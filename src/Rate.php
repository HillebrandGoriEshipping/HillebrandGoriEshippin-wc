<?php

namespace HGeS;

use HGeS\Admin\Products\ProductMeta;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\WooCommerce\Address;

class Rate
{
    /**
     * Temp : Map of the pickup services (id => name)
     */
    const SERVICES_NAMES = [
        '1' => 'DHL DOMESTIC EXPRESS',
        '16' => 'Chrono 18H',
        '44' => 'Chrono Classic (Pays CEE en voie routière)',
        '49' => 'Chrono Relais Europe',
        '86' => 'Chrono Relais 13H',
        '1S' => 'Chrono 13 Instance Agence'
    ];

    /**
     * Prepares URL parameters for the API request based on the provided package details.
     *
     * @param array $package The package details, including destination and contents.
     * 
     * @return array The prepared URL parameters
     *
     * @throws \Exception If an error occurs while fetching package sizes from the API.
     */
    public static function prepareUrlParams(array $package): array
    {
        $expAddress = Address::fromApi();

        $params = [
            'from' => [
                'addressType' => 'company',
                'zipCode' => $expAddress[0]['zipCode'],
                'city' => $expAddress[0]['city'],
                'country' => $expAddress[0]['country']['countryAlpha2'],

            ],
            'to' => [
                'addressType' => 'individual',
                'zipCode' => $package['destination']['postcode'],
                'city' => $package['destination']['city'],
                'country' => $package['destination']['country'],
            ],
        ];

        if (!empty($expAddress[0]['stateCode'])) {
            $params['from']['state'] = $expAddress[0]['stateCode'];
        }

        if (!empty($package['destination']['state'])) {
            $params['to']['state'] = $package['destination']['state'];
        }

        $standardQuantity = 0;
        $magnumQuantity = 0;
        $containsSparkling = false;

        foreach ($package['contents'] as $item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_id'];

            $itemQuantity = get_post_meta($productId, ProductMetaEnum::NUMBER_OF_BOTTLE, true);
            $bottleSize = get_post_meta($productId, ProductMetaEnum::SIZE_OF_BOTTLE, true);
            $productType = get_post_meta($productId, ProductMetaEnum::TYPE, true);

            if ($item['variation_id'] !== 0) {
                $itemQuantity = get_post_meta($variationId, '_variation_quantity', true);
            }

            if ($itemQuantity === '') {
                $itemQuantity = $item['quantity'];
            } else {
                $itemQuantity = (int) $itemQuantity;
            }

            $totalItemQuantity = $itemQuantity * $item['quantity'];

            if ($bottleSize === 'magnum') {
                $magnumQuantity += $totalItemQuantity;
            } else {
                $standardQuantity += $totalItemQuantity;
            }

            if ($productType === 'sparkling') {
                $containsSparkling = true;
            }
        }

        try {
            $packageList = ApiClient::get('/package/get-sizes', ['nbBottles' => $standardQuantity, 'nbMagnums' => $magnumQuantity]);
            $packageParam = [];

            if (empty($packageList['data']['packages'])) {
                return [];
            }

            foreach ($packageList['data']['packages'][0] as $packageData) {
                foreach ($packageData as $choice) {
                    $packageParam[] = [
                        'nbBottles' => $choice['nbBottles'] ?? 0,
                        'nbMagnums' => $choice['nbMagnums'] ?? 0,
                        'nb' => $choice['nbPackages'],
                        'width' => $choice['sizes']['width'],
                        'height' => $choice['sizes']['height'],
                        'length' => $choice['sizes']['length'],
                        'weight' => $containsSparkling ? $choice['sizes']['weightSparkling'] : $choice['sizes']['weightStill'],
                    ];
                }
                $params['packages'] = $packageParam;
            }
        } catch (\Exception $th) {
            \Sentry\captureException($th);
            throw new \Exception('Error fetching package sizes: ' . $th->getMessage());
        }

        $workingDays = get_option(OptionEnum::HGES_WORKING_DAYS, []);

        if (!$workingDays) {
            throw new \Exception('No working days set in the configuration.');
        }

        // Increment the pickup date to reach the prep time set in the user settings
        // We add a day to the pickup date until the number of working days is spent to reach the prep time
        $pickupDate = new \DateTime();
        $countedDays = 0;
        $countedPrepDays = 0;
        $prepTime = get_option(OptionEnum::HGES_PREP_TIME);

        while ($countedPrepDays <= $prepTime) {
            $pickupDate->modify('+' . $countedDays . ' days');
            if (in_array($pickupDate->format('N'), $workingDays)) {
                $countedPrepDays++;
            }
            $countedDays++;
        }
        $params['pickupDate'] = $pickupDate->format('Y-m-d');
        $params['minHour'] = get_option(OptionEnum::HGES_MINHOUR) . ':00';
        $params['cutoff'] = get_option(OptionEnum::HGES_CUTOFF) . ':00';
        $params['nbBottles'] = $magnumQuantity + $standardQuantity;

        $details = [];

        foreach ($package['contents'] as $item) {
            $productId = $item['product_id'];
            $variationId = $item['variation_id'];

            $product = wc_get_product($productId);
            $unitPriceExTax = wc_get_price_excluding_tax($product);

            $itemQuantity = get_post_meta($productId, ProductMetaEnum::NUMBER_OF_BOTTLE, true);
            if ($item['variation_id'] !== 0) {
                $itemQuantity = get_post_meta($variationId, '_variation_quantity', true);
            }

            if ($itemQuantity === '') {
                $itemQuantity = $item['quantity'];
            } else {
                $itemQuantity = (int) $itemQuantity;
            }

            $details[] = [
                'capacity' => get_post_meta($productId, ProductMetaEnum::CAPACITY, true),
                'alcoholDegree' => get_post_meta($productId, ProductMetaEnum::ALCOHOL_PERCENTAGE, true),
                'unitValue' => $unitPriceExTax,
                'hsCode' => get_post_meta($productId, ProductMetaEnum::HS_CODE, true),
                'quantity' => $itemQuantity * $item['quantity'],
            ];
        }

        $params['details'] = $details;

        $carrierList = get_option(OptionEnum::HGES_PREF_TRANSP, []);
        $params['preferredCarrier'] = $carrierList;

        return $params;
    }

    /**
     * Fetches shipping rates from the API based on the provided package details.
     *
     * @param array $package An associative array containing package details required to fetch shipping rates.
     *
     * @return array Returns an array of shipping rates if successful, or an array containing an error message in case of failure.
     *
     * @throws \Exception If an unexpected error occurs during the API call.
     */
    public static function getRatesFromApi(array $package): array
    {
        try {
            $urlParams = self::prepareUrlParams($package);
            if (!$urlParams) {
                return ['error' => 'No package sizes available for the given contents.'];
            }

            $response = ApiClient::get('/v2/rates', $urlParams);

            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            throw $e;
        }

        return [];
    }

    /**
     * Checks if the retrieval of shipping rates is allowed based business logic conditions
     * 
     * @param array $package An associative array containing package details required to check if rate retrieval is allowed.
     * @return bool Returns true if rate retrieval is allowed, false otherwise.
     */
    public static function isRateRetrievalAllowed(array $package): bool
    {
        $allowed = true;
        $debug = [];

        // do not attempt retrieving rates if current action is "add-to-cart"
        if (
            !empty($_POST['add-to-cart'])
            || (isset($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'add_to_cart')
        ) {
            $allowed = false;
            $debug[] = 'Rate retrieval not allowed for add-to-cart action.';
        }
        // do not attempt retrieving rates if destination address is not set
        if (
            empty($package['destination']['city'])
            || empty($package['destination']['postcode'])
        ) {
            $allowed = false;
            $debug[] = 'Rate retrieval not allowed: destination address is not set.';
        }

        // do not attempt retrieving rates if any product in the package does not have the mandatory meta
        $mandatoryFields = [
            ProductMetaEnum::HS_CODE,
        ];

        foreach ($package['contents'] as $item) {
            foreach ($mandatoryFields as $field) {
                if (empty(get_post_meta($item['product_id'], $field, true))) {
                    $allowed = false;
                    $debug[] = "Rate retrieval not allowed: product ID {$item['product_id']} is missing mandatory field '$field'.";
                }
            }
        }
        error_log('Rate retrieval debug info: ' . implode(', ', $debug));
        return $allowed;
    }

    /**
     * Retrieves and formats shipping rates for a given package.
     *
     * @param array $package An associative array containing package details required to fetch shipping rates (e.g., dimensions, weight, destination).
     *
     * @return array An array of formatted shipping rates
     * 
     */
    public static function getShippingRates(array $package): array
    {

        if (!self::isRateRetrievalAllowed($package)) {
            return [];
        }

        $shippingRates = self::getRatesFromApi($package);

        if (isset($shippingRates['error'])) {
            return $shippingRates;
        }
        // Add more shipping rates as needed
        $formattedShippingRates = [];

        // recup get option dans une variable
        $deliveryPref = get_option(OptionEnum::HGES_PREF_DEL);

        if ($deliveryPref === 'home') {
            $shippingRates = array_filter($shippingRates, function ($rate) {
                return $rate['deliveryMode'] === "door";
            });
        } elseif ($deliveryPref === 'pickup_point') {
            $shippingRates = array_filter($shippingRates, function ($rate) {
                return $rate['deliveryMode'] == "pickup";
            });
        }

        foreach ($shippingRates as $rate) {
            if (!empty($rate['prices'])) {
                $totalPrice = array_reduce($rate['prices'], function ($carry, $price) {
                    if (empty($price['amountAllIn'])) {
                        return $carry; // Skip if amountAllIn is not set
                    }
                    //TODO: Be sure to stick with the last API version
                    if (get_option(OptionEnum::HGES_INSURANCE) == "no" && $price['label'] === 'Insurance price') {
                        // Skip insurance price if insurance is not activated
                        return $carry;
                    }
                    return $carry + $price['amountAllIn'];
                }, 0);
            }

            $formattedShippingRates[] = [
                'id' => $rate['service'],
                'label' => $rate['service'],
                'cost' => $totalPrice,
                'pickupDate' => $rate['pickupDate'],
                'deliveryMode' => $rate['deliveryMode'],
                'meta_data' => [
                    'deliveryDate' => $rate['deliveryDate'],
                    'carrier' => $rate['carrier'],
                    'pickupDate' => $rate['pickupDate'],
                    'deliveryMode' => $rate['deliveryMode'],
                    'checksum' => $rate['checksum'],
                ],
            ];

            if (!$rate['deliveryMode']) {
                $formattedShippingRates['meta_data']['pickupServiceId'] = array_search($rate['service'], self::SERVICES_NAMES);
            }
        }

        return $formattedShippingRates;
    }

    /**
     * Retrieves a shipping rate by its checksum.
     */
    public static function getByChecksum(string $checksum): ?array
    {
        $shippingRate = ApiClient::get("/v2/rates/$checksum");

        if (isset($shippingRates['error'])) {
            return null;
        }

        return $shippingRate['data'];
    }

    /**
     * Checks if a shipping rate is still available by its checksum.
     * 
     * @param string $shippingRateChecksum The checksum of the shipping rate to check.
     * @return bool Returns true if the shipping rate is still available, false otherwise.
     */
    public static function isStillAvailable(?string $shippingRateChecksum = null): bool
    {
        if (empty($shippingRateChecksum)) {
            return false;
        }

        try {
            $shippingRate = self::getByChecksum($shippingRateChecksum);
            return !empty($shippingRate);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

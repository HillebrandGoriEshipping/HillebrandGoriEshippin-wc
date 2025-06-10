<?php

namespace HGeS;

use HGeS\Utils\ApiClient;
use HGeS\Utils\Address;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;

class Rate
{

    /**
     * Temp : Map of the pickup services (id => name)
     */
    const SERVICES_NAMES = [
        '1' => 'Chrono 13H',
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
    public static function prepareUrlParams($package)
    {
        $expAddress = Address::fromApi();

        $params = [
            'expAddress' => [
                'addressType' => 'company',
                'zipCode' => $expAddress[0]['zipCode'],
                'city' => $expAddress[0]['city'],
                'country' => $expAddress[0]['country']['countryAlpha2'],

            ],
            'destAddress' => [
                'addressType' => 'individual',
                'zipCode' => $package['destination']['postcode'],
                'city' => $package['destination']['city'],
                'country' => $package['destination']['country'],
            ],
        ];

        if (!empty($expAddress[0]['stateCode'])) {
            $params['expAddress']['state'] = $expAddress[0]['stateCode'];
        }

        if (!empty($package['destination']['state'])) {
            $params['destAddress']['state'] = $package['destination']['state'];
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
            $packageList = ApiClient::get('/package/get-sizes?nbBottles=' . $standardQuantity . '&nbMagnums=' . $magnumQuantity);
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
                'hsCode' => "1234.12.12", //TODO : retrieve real hs code dynamically
                'quantity' => $itemQuantity * $item['quantity'],
            ];
        }

        dump($details);

        $carrierList = get_option(OptionEnum::HGES_PREF_TRANSP, []);
        foreach ($carrierList as $carrier) {
            $params[$carrier] = true;
        }

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
    public static function getRatesFromApi($package)
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
     * Retrieves and formats shipping rates for a given package.
     *
     * @param array $package An associative array containing package details required to fetch shipping rates (e.g., dimensions, weight, destination).
     *
     * @return array An array of formatted shipping rates
     * 
     */
    public static function getShippingRates($package)
    {
        // do not attempt retrieving rates if current action is "add-to-cart"
        if (
            !empty($_POST['add-to-cart'])
            || (isset($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'add_to_cart')
        ) {
            trigger_error('HillebrandGori eShipping : Current action is "add-to-cart", returning empty rates array.', E_USER_NOTICE);
            return [];
        }
        // do not attempt retrieving rates if destination address is not set
        if (
            empty($package['destination']['city'])
            || empty($package['destination']['postcode'])
        ) {
            trigger_error('HillebrandGori eShipping : Destination address is not set, returning empty rates array.', E_USER_NOTICE);
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
                return $rate['doorDelivery'] === true;
            });
        } elseif ($deliveryPref === 'pickup_point') {
            $shippingRates = array_filter($shippingRates, function ($rate) {
                return $rate['doorDelivery'] == false;
            });
        }

        foreach ($shippingRates as $rate) {
            $formattedShippingRates[] = [
                'id' => $rate['service'],
                'label' => $rate['service'],
                'cost' => $rate['price'],
                'pickupDate' => $rate['pickupDate'],
                'doorDelivery' => $rate['doorDelivery'],
                'insurancePrice' => $rate['insurancePrice'],
                'meta_data' => [
                    'deliveryDate' => $rate['deliveryDate'],
                    'carrierName ' => $rate['name'],
                    'insurancePrice' => $rate['insurancePrice'],
                    'pickupDate' => $rate['pickupDate'],
                    'doorDelivery' => $rate['doorDelivery']
                ],
            ];

            if (!$rate['doorDelivery']) {
                $formattedShippingRates['meta_data']['pickupServiceId'] = array_search($rate['service'], self::SERVICES_NAMES);
            }
        }

        return $formattedShippingRates;
    }
}

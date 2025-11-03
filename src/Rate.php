<?php

namespace HGeS;

use HGeS\Dto\RateDto;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\Utils\Packaging;
use HGeS\WooCommerce\Address;
use HGeS\WooCommerce\Model\Order;
use HGeS\WooCommerce\ShippingAddressFields;

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
        $isCompanyCheckboxKey = ShippingAddressFields::WC_ORDER_META_PREFIX_SHIPPING . ShippingAddressFields::IS_COMPANY_CHECKBOX_OPTIONS['id'];
        $companyNameKey = ShippingAddressFields::WC_ORDER_META_PREFIX_SHIPPING . ShippingAddressFields::COMPANY_NAME_FIELD_OPTIONS['id'];

        $currentOrder = wc_get_order($package['order_id'] ?? (int) ($_GET['orderId'] ?? 0));

        if ($currentOrder) {
            $currentOrderShippingAddressCategory = $currentOrder->get_meta($isCompanyCheckboxKey) ? 'company' : 'individual';
            $currentCompanyName = $currentOrder->get_meta($companyNameKey) ?: '';
            $toAddress = [
                'category' => $currentOrderShippingAddressCategory,
                'firstname' => $currentOrder->get_shipping_first_name(),
                'lastname' => $currentOrder->get_shipping_last_name(),
                'company' => $currentCompanyName,
                'address' => $currentOrder->get_shipping_address_1(),
                'telephone' => $currentOrder->get_billing_phone(),
                'zipCode' => $currentOrder->get_shipping_postcode(),
                'city' => $currentOrder->get_shipping_city(),
                'country' => $currentOrder->get_shipping_country(),
                'email' => $currentOrder->get_billing_email(),
            ];
            if ($currentOrder->get_shipping_state()) {
                $toAddress['state'] = $currentOrder->get_shipping_state();
            }
        } else {
            $sessionMetaData = WC()->session->customer['meta_data'] ?? [];
            $isCompanyMeta = array_find($sessionMetaData, function ($meta) use ($isCompanyCheckboxKey) {
                return $meta['key'] === $isCompanyCheckboxKey;
            });
            $category = 'individual';
            if (!empty($isCompanyMeta) && $isCompanyMeta['value']) {
                $category = $isCompanyMeta['value'] ? 'company' : 'individual';
            }

            if ($category === 'company') {
                $companyNameMeta = array_find($sessionMetaData, function ($meta) use ($companyNameKey) {
                    return $meta['key'] === $companyNameKey;
                });
                $companyName = !empty($companyNameMeta) ? $companyNameMeta['value'] : '';
            }

            $toAddress = [
                'category' => $category,
                'zipCode' => $package['destination']['postcode'],
                'city' => $package['destination']['city'],
                'country' => $package['destination']['country'],
                'state' => !empty($package['destination']['state']) ? $package['destination']['state'] : null,
                'address' => $package['destination']['address'],
                'telephone' => $package['destination']['phone'] ?? '0123456789',
            ];
            if ($category === 'company') {
                $toAddress['company'] = $companyName ?? '';
            }
        }

        $expAddress = Address::getFavoriteAddress();

        $params = [
            'from' => ['addressId' => $expAddress['id']],
            'to' => $toAddress,
        ];

        if (empty($params['from']['state'])) {
            unset($params['from']['state']);
        }

        $containsSparkling = false;

        foreach ($package['contents'] as $item) {
            $productId = $item['product_id'];
            $productType = get_post_meta($productId, ProductMetaEnum::TYPE, true);

            if ($productType === 'sparkling') {
                $containsSparkling = true;
            }
        }

        try {
            if ($currentOrder) {
                $packageList = $currentOrder->get_meta(Order::PACKAGING_META_KEY, true);
            } else {

                $packageListByType = Packaging::calculatePackagingPossibilities($package['contents']);

                $packageList = [];
                foreach ($packageListByType as $packagingType => $packages) {
                    foreach ($packages as $pkg) {
                        $packageList[] = $pkg;
                    }
                }
            }

            foreach ($packageList as &$shippingPackage) {

                $shippingPackage['weight'] = $containsSparkling ? $shippingPackage['weightDefinition']['sparkling'] : $shippingPackage['weightDefinition']['still'];
                $shippingPackage['nb'] = 1;
            }
            $params['packages'] = $packageList;
        } catch (\Exception $th) {
            \Sentry\captureException($th);
            throw new \Exception('Error fetching package sizes: ' . esc_html($th->getMessage()));
        }

        $workingDays = get_option(OptionEnum::HGES_WORKING_DAYS, []);

        if (!$workingDays) {
            throw new \Exception('No working days set in the configuration.');
        }

        // Increment the pickup date to reach the prep time set in the user settings
        // We add a day to the pickup date until the number of working days is spent to reach the prep time
        $countedDays = 0;
        $countedPrepDays = 0;
        $prepTime = get_option(OptionEnum::HGES_PREP_TIME);

        while ($countedPrepDays < $prepTime) {
            $pickupDate = new \DateTime();
            $pickupDate->modify('+' . $countedDays . ' days');

            if (in_array($pickupDate->format('N'), $workingDays)) {
                $countedPrepDays++;
            }
            $countedDays++;
        }

        $params['pickupDate'] = $pickupDate->format('Y-m-d');
        $params['minHour'] = get_option(OptionEnum::HGES_MINHOUR) . ':00';
        $params['cutoff'] = get_option(OptionEnum::HGES_CUTOFF) . ':00';

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
                'designation' => 'Okay',
                'quantity' => $itemQuantity * $item['quantity'],
                'currency' => get_woocommerce_currency(),
            ];

            if (get_post_meta($productId, ProductMetaEnum::TYPE, true) === ProductMetaEnum::STILL || get_post_meta($productId, ProductMetaEnum::TYPE, true) === ProductMetaEnum::SPARKLING) {
                $details[count($details) - 1]['vintage'] = get_post_meta($productId, ProductMetaEnum::VINTAGE_YEAR, true);
                $details[count($details) - 1]['color'] = get_post_meta($productId, ProductMetaEnum::COLOR, true);
            }
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
            !empty($_GET['add-to-cart'])
            || (isset($_GET['wc-ajax']) && $_GET['wc-ajax'] === 'add_to_cart')
            || isset($_POST['add-to-cart'])
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

        if ($debug) {
            error_log('Rate retrieval debug info: ' . implode(', ', $debug));
        }

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
            $newRate = new RateDto();
            $newRate->setChecksum($rate['checksum']);
            $newRate->setServiceName($rate['serviceName']);
            $newRate->setPrices($rate['prices']);
            $newRate->setCarrier($rate['carrier']);
            $newRate->setServiceCode($rate['serviceCode']);
            $newRate->setPickupDate($rate['pickupDate']);
            $newRate->setDeliveryMode($rate['deliveryMode']);
            $newRate->setDeliveryDate($rate['deliveryDate']);
            $newRate->setRequiredAttachments($rate['requiredAttachments'] ?? []);
            $newRate->setCoast($rate['coast'] ?? null);
            $newRate->setFirstPickupDelivery($rate['firstPickupDelivery'] ?? null);
            $newRate->setPackages($rate['packages'] ?? []);
            $newRate->addMetaData('carrier', $rate['carrier']);
            $newRate->addMetaData('pickupDate', $rate['pickupDate']);
            $newRate->addMetaData('deliveryMode', $rate['deliveryMode']);
            $newRate->addMetaData('deliveryDate', $rate['deliveryDate']);
            $newRate->addMetaData('checksum', $rate['checksum']);

            $formattedShippingRates[] = $newRate->toArray();

            if (!$rate['deliveryMode']) {
                $formattedShippingRates['meta_data']['pickupServiceId'] = array_search($rate['service'], self::SERVICES_NAMES);
            }
        }

        return $formattedShippingRates;
    }

    /**
     * Retrieves a shipping rate by its checksum.
     */
    public static function getByChecksum(string $checksum): ?RateDto
    {
        try {
            $shippingRate = ApiClient::get("/v2/rates/$checksum");
        } catch (\Throwable $e) {
            error_log('Error retrieving shipping rate by checksum: ' . $checksum  . ' - ' . $e->getMessage());
            throw $e;
        }

        $rateArray = $shippingRate['data'] ?? null;
        $rateDto = new RateDto();
        if ($rateArray) {
            $rateDto->setChecksum($rateArray['checksum']);
            $rateDto->setServiceName($rateArray['serviceName']);
            $rateDto->setPrices($rateArray['prices']);
            $rateDto->setCarrier($rateArray['carrier']);
            $rateDto->setServiceCode($rateArray['serviceCode']);
            $rateDto->setPickupDate($rateArray['pickupDate']);
            $rateDto->setDeliveryMode($rateArray['deliveryMode']);
            $rateDto->setDeliveryDate($rateArray['deliveryDate']);
            $rateDto->setRequiredAttachments($rateArray['requiredAttachments'] ?? []);
            $rateDto->setCoast($rateArray['coast'] ?? null);
            $rateDto->setFirstPickupDelivery($rateArray['firstPickupDelivery'] ?? null);
            $rateDto->setPackages($rateArray['packages'] ?? []);
        }

        return $rateDto;
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

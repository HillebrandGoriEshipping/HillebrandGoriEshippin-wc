<?php

namespace HGeS;

use HGeS\Utils\ApiClient;
use HGeS\Utils\Address;

class Rate
{


    public static function prepareUrlParams($package)
    {

        $expAddress = Address::fromApi();

        $params = [
            'expAddress' => [
                'addressType' => 'company',
                'zipCode' => $expAddress[0]['postcode'],
                'city' => $expAddress[0]['city'],
                'country' => $expAddress[0]['country']['countryAlpha2'],

            ],
            'destAddress' => [
                'addressType' => 'individual',
                'zipCode' => $package['origin']['postcode'],
                'city' => $package['origin']['city'],
                'country' => $package['destination']['country'],
                // Add more address fields as needed
            ],
            // Add more package details as needed
        ];

        if (!empty($expAddress[0]['stateCode'])) {
            $params['expAddress']['state'] = $expAddress[0]['stateCode'];
        }

        if (!empty($package['destination']['state'])) {
            $params['destAddress']['state'] = $package['destination']['state'];
        }


        return $urlQuery;
    }

    public static function getRatesFromApi($package)
    {
        // packages[0][nb]: 1
        // packages[0][weight]: 4
        // packages[0][width]: 27
        // packages[0][height]: 14
        // packages[0][length]: 39
        // pickupDate: 2024-02-07
        // minHour: 09:00:00
        // cutoff: 19:00:00
        // nbBottles: 14
        // dhl: 1
        // ups: 1
        // fedex: 1
        // tnt: 1
        // chronopost: 1
        // destAddress[state]: TX
        // packages[1][nb]: 2
        // packages[1][weight]: 10
        // packages[1][width]: 27
        // packages[1][height]: 40
        // packages[1][length]: 39

        $urlParams = self::prepareUrlParams($package);

        try {
            $response = ApiClient::get('/shipment/get-rates');
            if (isset($response['data']) && is_array($response['data'])) {
                return $response['data'];
            }
        } catch (\Exception $e) {
            error_log('Error fetching rates: ' . $e->getMessage());
            return $error = '';
        }

        return [];
    }

    public static function getShippingRates($package)
    {
        $shippingRates = ApiClient::get('/shipment/get-rates');
        $shippingRates[] = [
            'id' => 'flat_rate',
            'label' => 'Flat Rate',
            'cost' => 25.00,
        ];
        $shippingRates[] = [
            'id' => 'free_shipping',
            'label' => 'Free Shipping',
            'cost' => 0.00,
        ];

        // Add more shipping rates as needed

        return $shippingRates;
    }
}

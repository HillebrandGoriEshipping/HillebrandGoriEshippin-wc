<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Utils\ApiClient;

class PickupPoint {

    public static function getPickupPoints(string $street, string $zipCode, string $city, string $country): ?array
    {
        $urlParams = [
            'street' => htmlspecialchars(wp_strip_all_tags($street)),
            'zipCode' => htmlspecialchars(wp_strip_all_tags($zipCode)),
            'city' => htmlspecialchars(wp_strip_all_tags($city)),
            'country' => htmlspecialchars(wp_strip_all_tags($country)),
        ];

        $pickupPointsRequest = ApiClient::get(
            '/relay/get-access-points',
            $urlParams,
        );

        if (isset($pickupPointsRequest['data']) && is_array($pickupPointsRequest['data'])) {
            return $pickupPointsRequest['data'];
        }

        return null;
    }
}

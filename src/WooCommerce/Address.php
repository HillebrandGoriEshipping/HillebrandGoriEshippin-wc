<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\ApiClient;

/**
 * Address Model
 */
class Address
{
    /**
     * Get the address from the API
     *
     * @return array
     * @throws \Exception
     */
    public static function fromApi(): array
    {
        try {
            $address = ApiClient::get('/address/get-addresses');

            if ($address['status'] === 200) {
                $address = $address['data'];
            } else {
                $address = [];
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return $address;
    }
}

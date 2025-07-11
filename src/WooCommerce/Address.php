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
    public static function allFromApi(): array
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

    /**
     * Get a single address by ID from the API
     * @param int $id
     * @return array
     * @throws \Throwable
     */
    public static function singleFromApi(int $id): array
    {
        try {
            $address = ApiClient::get('/v2/addresses/' . $id);

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

    /**
     * Get the favorite address from the API
     * 
     * @return array|null
     */
    public static function getFavoriteAddress(): ?array
    {
        $favoriteAddressId = get_option(\HGeS\Utils\Enums\OptionEnum::HGES_FAVORITE_ADDRESS_ID);
        if (!$favoriteAddressId) {
            return null;
        }

        try {
            return self::singleFromApi($favoriteAddressId);
        } catch (\Throwable $e) {
            return null;
        }
    }   
}

<?php

namespace HGeS\Utils;

class Address
{
    public static function fromApi()
    {
        try {
            $address = ApiClient::get('/address/get-addresses');

            if ($address['status'] === 200) {
                $address = $address['data'];
            } else {
                $address = [];
            }
        } catch (\Throwable $th) {
        }

        return $address;
    }
}

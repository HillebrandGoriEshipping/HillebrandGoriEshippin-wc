<?php

namespace HGeS\Validation;

class RateChecksumValidator
{
    public static function validate(string $checksum): string
    {
        if (!empty($checksum) && !preg_match('/^[a-f0-9]{32}$/', $checksum)) {     
           throw new \InvalidArgumentException("Invalid checksum format");
        }

        return $checksum;
    }
}

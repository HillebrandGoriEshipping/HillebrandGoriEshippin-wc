<?php 

namespace HGeS\Utils;

class HSCodeHelper {

    public static function isWine(string $hsCode): bool
    {
        return strpos($hsCode, '2204') === 0;
    }
}
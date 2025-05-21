<?php

namespace HGeS\Utils\Enums;

/**
 * Class GlobalEnum
 *
 * This class is used to define the global enums for the plugin.
 */
class GlobalEnum {

    const TRANSLATION_DOMAIN = 'HillebrandGorieShipping';

    public static function getList(): array
    {
        return [
            self::TRANSLATION_DOMAIN,
        ];
    }
}
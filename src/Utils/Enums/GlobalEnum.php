<?php

namespace HGeS\Utils\Enums;

/**
 * Class GlobalEnum
 *
 * This class is used to define the global enums for the plugin.
 */
class GlobalEnum implements EnumInterface {

    const TRANSLATION_DOMAIN = 'HillebrandGorieShipping';

    public static function getList(): array
    {
        return [
            self::TRANSLATION_DOMAIN,
        ];
    }

    public static function getConstraints(string $option): array | null
    {
        // No constraints defined for global enums
        return null;
    }
}
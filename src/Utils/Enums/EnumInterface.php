<?php

namespace HGeS\Utils\Enums;

interface EnumInterface
{
    /**
     * Get the list of options
     *
     * @return array
     */
    public static function getList(): array;

    /**
     * Return the constraints for a given option
     *
     * @param string $option
     * @return array
     */
    public static function getConstraints(string $option): ?array;

    /**
     * Return the sanitization type for a given option
     *
     * @param string $option
     * @return string|null
     */
    public static function getSanitizationType(string $option): ?string;
}
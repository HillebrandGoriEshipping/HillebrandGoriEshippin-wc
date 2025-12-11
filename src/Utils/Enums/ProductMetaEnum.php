<?php

namespace HGeS\Utils\Enums;

class ProductMetaEnum implements EnumInterface
{
    const NUMBER_OF_BOTTLE = "_number_of_bottle";
    const TYPE = "_type";
    const COLOR = "_color";
    const CAPACITY_TYPE = "_capacity_type";
    const CAPACITY = "_capacity";
    const ALCOHOL_PERCENTAGE = "_alcohol_percentage";
    const VINTAGE_YEAR = "_vintage";
    const PRODUCING_COUNTRY = "_producing_country";
    const DESIGNATION = "_designation";
    const HS_CODE = "_hs_code";

    const STILL = "still";
    const SPARKLING = "sparkling";

    const PRODUCT_META_GROUP = "HGeS_product_meta_group";

    /**
     * Get the list of product meta
     * 
     * @return array
     */
    public static function getList(): array
    {
        return [
            self::NUMBER_OF_BOTTLE,
            self::TYPE,
            self::COLOR,
            self::CAPACITY_TYPE,
            self::CAPACITY,
            self::ALCOHOL_PERCENTAGE,
            self::VINTAGE_YEAR,
            self::PRODUCING_COUNTRY,
            self::DESIGNATION,
            self::HS_CODE,
        ];
    }

    public static function getConstraints(string $option): ?array
    {
        return null;
    }

    public static function getSanitizationType(string $option): ?string
    {
        return null;
    }
}

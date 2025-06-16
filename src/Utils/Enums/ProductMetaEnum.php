<?php

namespace HGeS\Utils\Enums;

class ProductMetaEnum implements EnumInterface
{
    const NUMBER_OF_BOTTLE = "_number_of_bottle";
    const SIZE_OF_BOTTLE = "_size_of_bottle";
    const TYPE = "_type";
    const COLOR = "_color";
    const CAPACITY = "_capacity";
    const ALCOHOL_PERCENTAGE = "_alcohol_percentage";
    const VINTAGE_YEAR = "_vintage";
    const PRODUCING_COUNTRY = "_producing_country";
    const APPELLATION = "_appellation";
    const HS_CODE = "_hs_code";

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
            self::SIZE_OF_BOTTLE,
            self::TYPE,
            self::COLOR,
            self::CAPACITY,
            self::ALCOHOL_PERCENTAGE,
            self::VINTAGE_YEAR,
            self::PRODUCING_COUNTRY,
            self::APPELLATION,
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

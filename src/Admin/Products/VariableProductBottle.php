<?php

namespace HGeS\Admin\Products;

use HGeS\Utils\Enums\GlobalEnum;

class VariableProductBottle extends \WC_Product_Variable
{
    const PRODUCT_TYPE = 'bottle-variable';
    const PRODUCT_TYPE_LABEL = 'Variable Bottle Product';

    /**
     * Returns the product type.
     *
     * @return string The product type constant defined by PRODUCT_TYPE.
     */
    public function get_type(): string
    {
        return self::PRODUCT_TYPE;
    }

    /**
     * Adds the custom product type to the selection array.
     *
     * @param array $types The existing array of product types.
     * @return array The modified array including the custom product type.
     */
    public static function addToSelect(array $types): array
    {
        $types[self::PRODUCT_TYPE] = __(self::PRODUCT_TYPE_LABEL, GlobalEnum::TRANSLATION_DOMAIN);
        return $types;
    }
}

<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Enums\GlobalEnum;
use WC_Product_Attribute;
use WC_Product_Variation;

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

    public function get_children($visible_only = '')
    {
        $children = parent::get_children($visible_only);
        error_log(print_r($children, true)); // Ajoute ça
        return $children;
    }
}

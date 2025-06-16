<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Enums\GlobalEnum;

class VariableProductBottle extends \WC_Product_Variable
{
    const PRODUCT_TYPE = 'bottle-variable';
    const PRODUCT_TYPE_LABEL = 'Variable Bottle Product';

    /**
     * Initializes the admin hooks and filters for the custom product type.
     */
    public static function initAdmin(): void
    {
        add_filter('product_type_selector', [self::class, 'addToSelect']);
        add_filter('woocommerce_data_stores', [self::class, 'createDataStore'], 10, 1);
    }

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

    /**
     * Adds or overrides the data store class for variable product bottles in the WooCommerce data store registry.
     *
     * @param array $stores Existing array of WooCommerce product data stores.
     * @return array Modified array including the data store for variable product bottles.
     */
    public static function createDataStore($stores)
    {
        $stores['product-' . VariableProductBottle::PRODUCT_TYPE] = 'WC_Product_Variable_Data_Store_CPT';
        return $stores;
    }
}

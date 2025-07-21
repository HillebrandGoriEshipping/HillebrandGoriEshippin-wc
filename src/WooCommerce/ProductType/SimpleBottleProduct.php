<?php

namespace HGeS\WooCommerce\ProductType;

use HGeS\Utils\Enums\GlobalEnum;

class SimpleBottleProduct extends \WC_Product
{
    const PRODUCT_TYPE = 'bottle-simple';
    const PRODUCT_TYPE_LABEL = 'Simple Bottle Product';

    public static function init(): void
    {
        add_action('woocommerce_' . self::PRODUCT_TYPE . '_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
        add_filter('woocommerce_data_stores', [self::class, 'createDataStore'], 10, 1);
    }

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
     * Adds or overrides the data store class for simple product bottles in the WooCommerce data store registry.
     *
     * @param array $stores Existing array of WooCommerce product data stores.
     * @return array Modified array including the data store for simple product bottles.
     */
    public static function createDataStore($stores)
    {
        $stores['product-' . self::PRODUCT_TYPE] = 'WC_Product_Data_Store_CPT';
        return $stores;
    }
}

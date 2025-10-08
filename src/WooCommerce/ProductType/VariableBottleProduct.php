<?php

namespace HGeS\WooCommerce\ProductType;

use HGeS\Utils\Translator;

class VariableBottleProduct extends \WC_Product_Variable
{
    const PRODUCT_TYPE = 'bottle-variable';
    const PRODUCT_TYPE_LABEL = 'Variable Bottle Product';

    public static function init(): void
    {
        add_action('woocommerce_add_to_cart_handler_' . self::PRODUCT_TYPE, [self::class, 'add_to_cart_handler_variable'], 10, 0);
        add_action('woocommerce_' . self::PRODUCT_TYPE . '_add_to_cart', 'woocommerce_variable_add_to_cart', 30);
        add_filter('woocommerce_data_stores', [self::class, 'createDataStore'], 10, 1);
    }

    /**
     * Handles the add to cart action for variable bottle products.
     *
     * @param int $product_id The ID of the product being added to the cart.
     * @return bool True if the product was successfully added, false otherwise.
     */
    public static function add_to_cart_handler_variable()
    {
        if (! isset($_REQUEST['product_id'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }
        $product_id = $_REQUEST['product_id'];
        $variation_id = empty($_REQUEST['variation_id']) ? '' : absint(wp_unslash($_REQUEST['variation_id']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $quantity     = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount(wp_unslash($_REQUEST['quantity']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $variations   = array();

        $product = wc_get_product($product_id);

        foreach ($_REQUEST as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            if ('attribute_' !== substr($key, 0, 10)) {
                continue;
            }

            $variations[sanitize_title(wp_unslash($key))] = wp_unslash($value);
        }

        $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);

        if (! $passed_validation) {
            return false;
        }

        // Prevent parent variable product from being added to cart.
        if (empty($variation_id) && $product && $product->is_type(self::PRODUCT_TYPE)) {
            /* translators: 1: product link, 2: product name */
            wc_add_notice(sprintf(__('Please choose product options by visiting <a href="%1$s" title="%2$s">%2$s</a>.', 'woocommerce'), esc_url(get_permalink($product_id)), esc_html($product->get_name())), 'error');

            return false;
        }

        if (false !== WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations)) {
            wc_add_to_cart_message(array($product_id => $quantity), true);
            return true;
        }

        return false;
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
        $types[self::PRODUCT_TYPE] = Translator::translate(self::PRODUCT_TYPE_LABEL);
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
        $stores['product-' . self::PRODUCT_TYPE] = 'WC_Product_Variable_Data_Store_CPT';
        return $stores;
    }
}

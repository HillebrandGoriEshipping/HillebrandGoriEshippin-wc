<?php

namespace HGeS;

use HGeS\Admin\Products\ProductBottle;
use HGeS\Admin\Settings\Menu;
use HGeS\Admin\Settings\SettingsController;
use HGeS\Api\CustomEndpoints;
use HGeS\Admin\Products\ProductMeta;
use HGeS\Assets\Scripts;
use HGeS\Assets\Styles;
use HGeS\WooCommerce\ClassicUiRender;
use HGeS\WooCommerce\ShippingAddressFields;
use HGeS\WooCommerce\ShippingMethod;
use HGeS\WooCommerce\BottleShippingClass;
use HGeS\WooCommerce\PickupPointsRender;
use HGeS\Admin\Products\SimpleProductBottle;
use HGeS\Admin\Products\VariableProductBottle;


/**
 * Plugin entry class
 * It's role is to setup the plugin, no logic should be handled here.
 * Add the hooks and filters to the WordPress lifecycle and handle the custom requ
 */
class App
{
    /**
     * Run the plugin
     *
     * @return void
     */
    public static function run(): void
    {
        if (is_admin()) {
            self::runAdmin();
        }

        add_action('wp_enqueue_scripts', [Scripts::class, 'enqueue']);
        add_action('wp_enqueue_scripts', [Styles::class, 'enqueue']);
        add_action('rest_api_init', [CustomEndpoints::class, 'register']);
        add_action('woocommerce_blocks_loaded', [ShippingAddressFields::class, 'register']);
        add_action('woocommerce_checkout_create_order', [ShippingAddressFields::class, 'onOrderCreate'], 10, 2);

        add_filter('woocommerce_order_get_formatted_billing_address', [ShippingAddressFields::class, 'renderOrderConfirmation'], 10, 3);
        add_filter('woocommerce_order_get_formatted_shipping_address', [ShippingAddressFields::class, 'renderOrderConfirmation'], 9, 3);
        add_filter('woocommerce_order_get_formatted_shipping_address', [PickupPointsRender::class, 'renderOrderConfirmation'], 10, 3);
        add_filter('woocommerce_shipping_methods', [ShippingMethod::class, 'register']);
        add_filter('woocommerce_package_rates', [ClassicUiRender::class, 'sortShippingMethods'], 10, 2);
        add_filter('woocommerce_cart_shipping_method_full_label', [ClassicUiRender::class, 'renderLabel'], 10, 2);
        add_filter('woocommerce_cart_shipping_packages', [ClassicUiRender::class, 'invalidateRatesCache'], 100);
        add_filter('woocommerce_checkout_fields', [ShippingAddressFields::class, 'filterClassicUiFields'], 10, 1);
    }

    /**
     * Setup the hooks/filters for the admin area
     *
     * @return void
     */
    public static function runAdmin(): void
    {
        add_action('admin_enqueue_scripts', [Scripts::class, 'enqueueAdmin']);
        add_action('admin_enqueue_scripts', [Styles::class, 'enqueueAdmin']);
        add_action('admin_init', [self::class, 'router']);
        add_action('admin_menu', [Menu::class, 'addSettingsMenu']);
        add_filter('woocommerce_product_data_tabs', [ProductMeta::class, 'customTab']);
        add_action('woocommerce_product_data_panels', [ProductMeta::class, 'displayProductFields']);
        add_action('woocommerce_process_product_meta', [ProductMeta::class, 'saveProductFields']);
        add_action('woocommerce_product_after_variable_attributes', [ProductMeta::class, 'displayVariableProductField'], 10, 3);
        add_action('woocommerce_save_product_variation', [ProductMeta::class, 'saveVariableProductField'], 10, 2);
        add_action('init', [BottleShippingClass::class, 'create']);
        add_filter('product_type_selector', [SimpleProductBottle::class, 'addToSelect']);
        add_filter('product_type_selector', [VariableProductBottle::class, 'addToSelect']);
        add_filter('woocommerce_product_class', function ($classname, $product_type) {
            switch ($product_type) {
                case SimpleProductBottle::PRODUCT_TYPE:
                    return 'HGES\Admin\Products\SimpleProductBottle';
                case VariableProductBottle::PRODUCT_TYPE:
                    return 'HGES\Admin\Products\VariableProductBottle';
                default:
                    return $classname;
            }
        }, 10, 2);
        add_filter('woocommerce_product_data_tabs', function ($tabs) {
            foreach ($tabs as $key => &$tab) {
                if ($key === 'variations') {
                    $tab['class'][] = 'show_if_' . VariableProductBottle::PRODUCT_TYPE;
                }
                if (isset($tab['class']) && in_array('show_if_simple', $tab['class'])) {
                    $tab['class'][] = 'hide_if_simple';
                }
                if (isset($tab['target']) && $tab['target'] === 'general_product_data') {
                    if (!isset($tab['class'])) {
                        $tab['class'] = [];
                    }
                    $tab['class'][] = 'hide_if_simple';
                }
            }
            return $tabs;
        }, 1);
    }

    /**
     * Router for plugin specific actions
     *
     * @return void
     */
    public static function router(): void
    {
        if (
            isset($_GET['page'])
            && $_GET['page'] === 'hillebrand-gori-eshipping'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            SettingsController::saveSettings();
            exit;
        }
    }
}

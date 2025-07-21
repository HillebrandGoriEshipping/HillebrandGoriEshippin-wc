<?php

namespace HGeS;

use HGeS\Admin\Menu;
use HGeS\Admin\Order\ShippingMethodRow;
use HGeS\Admin\Products\ProductMeta;
use HGeS\Api\CustomEndpoints;
use HGeS\Assets\Scripts;
use HGeS\Assets\Styles;
use HGeS\WooCommerce\ShippingAddressFields;
use HGeS\WooCommerce\Model\Order;
use HGeS\WooCommerce\Model\Product;
use HGeS\WooCommerce\Model\ShippingMethod;
use HGeS\WooCommerce\ProductType\SimpleBottleProduct;
use HGeS\WooCommerce\ProductType\VariableBottleProduct;
use HGeS\WooCommerce\Render\ClassicUiRender;
use HGeS\WooCommerce\Render\PickupPointsRender;
use HGeS\WooCommerce\ShippingClass\BottleShippingClass;

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

        Scripts::init();
        Styles::init();

        ClassicUiRender::init();
        CustomEndpoints::init();
        PickupPointsRender::init();
        ShippingAddressFields::init();
        Order::init();
        ProductMeta::init();

        // some init() calls must wait for WC to be fully loaded
        add_action('init', function () {
            ShippingMethod::init();
            VariableBottleProduct::init();
        });
    }

    /**
     * Setup the hooks/filters for the admin area
     *
     * @return void
     */
    public static function runAdmin(): void
    {
        Scripts::initAdmin();
        Styles::initAdmin();

        BottleShippingClass::initAdmin();
        Menu::initAdmin();
        Product::initAdmin();
        ProductMeta::initAdmin();
        Router::initAdmin();
        ShippingAddressFields::initAdmin();
        ShippingMethodRow::initAdmin();

        // some initAdmin() calls must wait for WC to be fully loaded
        add_action('init', function () {
            SimpleBottleProduct::initAdmin();
            VariableBottleProduct::initAdmin();
        });
    }
}

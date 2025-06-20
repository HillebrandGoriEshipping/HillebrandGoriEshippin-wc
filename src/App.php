<?php

namespace HGeS;

use HGeS\Admin\Menu;
use HGeS\Admin\Products\ProductMeta;
use HGeS\Api\CustomEndpoints;
use HGeS\Assets\Scripts;
use HGeS\Assets\Styles;
use HGeS\WooCommerce\BottleShippingClass;
use HGeS\WooCommerce\ClassicUiRender;
use HGeS\WooCommerce\PickupPointsRender;
use HGeS\WooCommerce\ShippingAddressFields;
use HGeS\WooCommerce\SimpleProductBottle;
use HGeS\WooCommerce\VariableProductBottle;
use HGeS\WooCommerce\Model\Order;
use HGeS\WooCommerce\Model\ShippingMethod;

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
        
        // some init() calls must wait for WC to be fully loaded
        add_action('init', function() {
            ShippingMethod::init();
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
        ProductMeta::initAdmin();
        Router::initAdmin();
        ShippingAddressFields::initAdmin();

        // some initAdmin() calls must wait for WC to be fully loaded
        add_action('init', function() {
            SimpleProductBottle::initAdmin();
            VariableProductBottle::initAdmin();
        });
    }
}

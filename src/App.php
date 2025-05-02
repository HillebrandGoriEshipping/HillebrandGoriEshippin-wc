<?php

namespace HGeS;

use HGeS\Admin\Settings\Menu;
use HGeS\Admin\Settings\SettingsController;
use HGeS\Assets\Scripts;
use HGeS\Assets\Styles;
use HGeS\WooCommerce\ShippingMethod;

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
        
        add_filter('woocommerce_shipping_methods', [ShippingMethod::class, 'register']);

        add_action('wp_enqueue_scripts', [Scripts::class, 'enqueue']);
        add_action('wp_enqueue_scripts', [Styles::class, 'enqueue']);
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
            wp_redirect(admin_url('admin.php?page=hillebrand-gori-eshipping'));
            exit;
        }
    }
}

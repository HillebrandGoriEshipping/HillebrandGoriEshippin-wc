<?php

namespace HGeS;

use HGeS\Admin\Settings\Menu;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Admin\Settings\SettingsController;
use HGeS\WooCommerce\ShippingMethod;

/**
 * Plugin entry class
 *
 */
class App
{

    public static function run()
    {
        if (is_admin()) {
            self::runAdmin();
        }

        add_filter('woocommerce_shipping_methods', [ShippingMethod::class, 'register']);

        add_action('wp_enqueue_scripts', [self::class, 'enqueueAssets']);
    }

    public static function runAdmin()
    {
        add_action('admin_menu', [Menu::class, 'addSettingsMenu']);

        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);

        add_action('admin_init', [self::class, 'router']);
    }

    public static function router()
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

    public static function enqueueAssets()
    {
        wp_enqueue_script(
            'hges-shipping-rates-fill',
            HGeS_PLUGIN_URL . 'dist/shippingRatesFill.js',
            ['wp-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
            null,
            ['in_footer' => true]
        );
    }

    public static function enqueueAdminAssets()
    {
        // Enqueue your admin scripts and styles here
        if (!empty($_GET['page']) && $_GET['page'] === 'hillebrand-gori-eshipping') {
            wp_enqueue_style(
                'hges-admin-style',
                HGeS_PLUGIN_URL . 'assets/css/admin.css',
                [],
                null
            );

            wp_enqueue_script_module(
                'hges-settings-page-script',
                HGeS_PLUGIN_URL . 'assets/js/settingsPage.js'
            );
        }
    }
}

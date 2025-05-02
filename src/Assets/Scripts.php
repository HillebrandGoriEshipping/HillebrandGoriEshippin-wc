<?php

namespace HGeS\Assets;

class Scripts
{
    /**
     * Enqueue the scripts for the frontend
     *
     * @return void
     */
    public static function enqueue(): void
    {
        wp_enqueue_script(
            'hges-shipping-rates-fill',
            HGeS_PLUGIN_URL . 'dist/shippingRatesFill.js',
            ['wp-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script(
            'hges-order-recap-fill',
            HGeS_PLUGIN_URL . 'dist/orderRecapFill.js',
            ['wp-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
            null,
            ['in_footer' => true]
        );

       

        // Pass the assets URL to the JavaScript file
        wp_localize_script(
            'hges-shipping-rates-fill',
            'assetsPath',
            ['assetsUrl' => HGeS_PLUGIN_URL . 'assets/img/']
        );
    }

    /** 
     * Enqueue the scripts for the admin area
     */
    public static function enqueueAdmin(): void
    {
         // Enqueue your admin scripts and styles here
         if (!empty($_GET['page']) && $_GET['page'] === 'hillebrand-gori-eshipping') {
            wp_enqueue_script_module(
                'hges-settings-page-script',
                HGeS_PLUGIN_URL . 'assets/js/settingsPage.js'
            );
        }
    }
}

<?php

namespace HGeS\Assets;

use HGeS\Utils\Enums\OptionEnum;

class Scripts
{
    /**
     * The script key list for which we want to provide the global JS object
     */
    const GLOBAL_INJECT_TO = [
        'hges-shipping-rates-fill',
        'hges-order-recap-fill',
        'hges-global-object-injection',
    ];

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

        wp_enqueue_script_module(
            'hges-test',
            HGeS_PLUGIN_URL . 'assets/js/test.js',
            [],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script_module(
            'hges-api-client',
            HGeS_PLUGIN_URL . 'assets/js/apiClient.js',
            [],
            null,
            ['in_footer' => true]
        );

        self::globalObjectInjection();
    }

    /** 
     * Enqueue the scripts for the admin area
     * 
     * @return void
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
        wp_enqueue_script(
            'hges-global-object-injection',
            HGeS_PLUGIN_URL . 'assets/js/globalObjectInjection.js'
        );


        self::globalObjectInjection();
    }

    /**
     * Injects a global JavaScript object into specified frontend scripts.
     *
     * @return void
     */
    public static function globalObjectInjection(): void
    {
        $frontendJsGlobalObject = [
            'assetsUrl' => HGeS_PLUGIN_URL . 'assets/',
            'apiKey' => get_option(OptionEnum::HGES_ACCESS_KEY, '')
        ];

        $jsonObject = json_encode($frontendJsGlobalObject);
        $javascriptString =  "window.hges = $jsonObject;";

        foreach (self::GLOBAL_INJECT_TO as $script) {
            wp_add_inline_script(
                $script,
                $javascriptString,
                'before'
            );
        }
    }
}

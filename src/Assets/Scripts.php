<?php

namespace HGeS\Assets;

use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Messages;

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
        if (!empty($_GET['page']) && $_GET['page'] === 'hillebrand-gori-eshipping') {
            wp_enqueue_script_module(
                'hges-settings-page-script',
                HGeS_PLUGIN_URL . 'assets/js/settingsPage.js'
            );
        }

        wp_enqueue_script_module(
            'hges-product-metas',
            HGeS_PLUGIN_URL . 'assets/js/productMetas.js',
            ['wp-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
            false,
            ['in_footer' => true]
        );

        self::globalObjectInjection(true);
    }

    /**
     * Injects a global JavaScript object into specified frontend scripts.
     *
     * @param bool $admin Indicates if the injection is for admin scripts.
     * @return void
     */
    public static function globalObjectInjection(bool $admin = false): void
    {
        $frontendJsGlobalObject = [
            'assetsUrl' => HGeS_PLUGIN_URL . 'assets/',
            'messages' => Messages::getMessageList(),
        ];

        if ($admin) {
            $frontendJsGlobalObject['apiKey'] = get_option(OptionEnum::HGES_ACCESS_KEY, '');
        }

        $jsonObject = json_encode($frontendJsGlobalObject);
        $javascriptString =  "window.hges = $jsonObject;";

        wp_add_inline_script(
            'hges-global-object-injection',
            $javascriptString,
            'before'
        );
    }
}

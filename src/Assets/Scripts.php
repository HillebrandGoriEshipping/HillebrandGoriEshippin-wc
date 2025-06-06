<?php

namespace HGeS\Assets;

use HGeS\WooCommerce\SimpleProductBottle;
use HGeS\WooCommerce\VariableProductBottle;
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

        wp_enqueue_script(
            'hges-global-object-injection',
            HGeS_PLUGIN_URL . 'assets/js/globalObjectInjection.js'
        );

        wp_enqueue_script(
            'leaflet-map',
            HGeS_PLUGIN_URL . 'assets/js/classicLeafletMap.js',
            [],
            null,
            true
        );

        wp_enqueue_script_module(
            'hges-api-client-init',
            HGeS_PLUGIN_URL . 'assets/js/apiClientInit.js',
            ['hges-api-client'],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script(
            'dayjs-lib',
            'https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js',
            [],
            null,
            true
        );

        wp_enqueue_script_module(
            'hges-dayjs-init',
            HGeS_PLUGIN_URL . '/js/dayJsInit.js',
            ['dayjs-lib'],
            null,
            ['strategy' => 'defer', 'type' => 'module']
        );

        wp_enqueue_script_module(
            'hges-classic-pickup-map-handler',
            HGeS_PLUGIN_URL . 'assets/js/classicPickupMap.js',
            ['leaflet-map'],
            null,
            true
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

            wp_enqueue_script_module(
                'hges-validator',
                HGeS_PLUGIN_URL . 'assets/js/validator.js',
                ['hges-settings-page-script'],
            );
        }

        $screen = get_current_screen();

        if ($screen && $screen->post_type === 'product' && $screen->base === 'post') {
            wp_enqueue_script_module(
                'hges-product-metas',
                HGeS_PLUGIN_URL . 'assets/js/productMetas.js',
                ['wp-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
                false,
                ['in_footer' => true]
            );
        }

        wp_enqueue_script(
            'hges-global-object-injection',
            HGeS_PLUGIN_URL . 'assets/js/globalObjectInjection.js'
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
            'variableProductTypes' => [
                VariableProductBottle::PRODUCT_TYPE,
            ],
            'pricableProductTypes' => [
                SimpleProductBottle::PRODUCT_TYPE,
            ],
        ];

        if ($admin) {
            $frontendJsGlobalObject['apiKey'] = get_option(OptionEnum::HGES_ACCESS_KEY, '');
            $frontendJsGlobalObject['validatorConstraints'] = FrontendValidator::getAll();
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

<?php

namespace HGeS\Assets;

use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\GlobalEnum;
use HGeS\WooCommerce\ProductType\SimpleBottleProduct;
use HGeS\WooCommerce\ProductType\VariableBottleProduct;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Messages;
use HGeS\Utils\Translator;

class Scripts
{
    /**
     * Initializes the scripts for the frontend
     */
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Initializes the scripts for the admin area
     */
    public static function initAdmin(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
    }

    /**
     * Enqueue the scripts for the frontend
     *
     * @return void
     */
    public static function enqueue(): void
    {
        wp_enqueue_script(
            'hges-shipping-rates-fill',
            HGES_PLUGIN_URL . 'dist/shippingRatesFill.js',
            ['hges-i18n', 'wp-plugins', 'wp-element', 'wp-components', 'wp-hooks', 'wc-blocks-checkout'],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script(
            'hges-order-recap-fill',
            HGES_PLUGIN_URL . 'dist/orderRecapFill.js',
            ['hges-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wp-components', 'wc-blocks-checkout'],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script_module(
            'hges-api-client',
            HGES_PLUGIN_URL . 'assets/js/apiClient.js',
            [],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script(
            'hges-leaflet-map',
            HGES_PLUGIN_URL . 'assets/js/classicLeafletMap.js',
            [],
            null,
            true
        );

        wp_enqueue_script_module(
            'hges-api-client-init',
            HGES_PLUGIN_URL . 'assets/js/apiClientInit.js',
            ['hges-api-client'],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script_module(
            'hges-classic-pickup-map-handler',
            HGES_PLUGIN_URL . 'assets/js/classicPickupMap.js',
            ['hges-leaflet-map'],
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

        wp_enqueue_script(
            'hges-modal',
            HGES_PLUGIN_URL . 'assets/js/modal.js',
            [],
            null,
            ['in_footer' => true]
        );

        wp_enqueue_script(
            'hges-react-spawn-component',
            HGES_PLUGIN_URL . 'assets/js/react/spawnComponent.js',
            ['wp-element', 'hges-i18n'],
            null,
            ['in_footer' => true]
        );

        if (!empty($_GET['page']) && $_GET['page'] === 'hillebrand-gori-eshipping') {
            wp_enqueue_script_module(
                'hges-settings-page-script',
                HGES_PLUGIN_URL . 'assets/js/settingsPage.js'
            );

            wp_enqueue_script_module(
                'hges-validator',
                HGES_PLUGIN_URL . 'assets/js/validator.js',
                ['hges-settings-page-script'],
            );
        }


        $screen = get_current_screen();

        if ($screen && $screen->post_type === 'product' && $screen->base === 'post') {
            wp_enqueue_script_module(
                'hges-product-metas',
                HGES_PLUGIN_URL . 'assets/js/productMetas.js',
                ['hges-i18n', 'wp-plugins', 'wp-element', 'wp-hooks', 'wc-blocks-checkout'],
                false,
                ['in_footer' => true]
            );
        }

        wp_enqueue_script(
            'hges-components',
            HGES_PLUGIN_URL . 'dist/components.js',
            ['wp-element',  'wc-blocks-checkout', 'hges-i18n'],
            null,
            true
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
        wp_enqueue_script(
            'hges-global-object-injection',
            HGES_PLUGIN_URL . 'assets/js/globalObjectInjection.js'
        );

        wp_enqueue_script(
            'hges-i18n',
            HGES_PLUGIN_URL . 'assets/js/i18n.js',
            ['hges-global-object-injection']
        );

        $frontendJsGlobalObject = [
            'assetsUrl' => HGES_PLUGIN_URL . 'assets/',
            'messages' => Messages::getMessageList(),
            'variableProductTypes' => [
                VariableBottleProduct::PRODUCT_TYPE,
            ],
            'pricableProductTypes' => [
                SimpleBottleProduct::PRODUCT_TYPE,
            ],
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'apiUrl' => ApiClient::getApiUrl(),
            'i18n' => [
                'messages' => Translator::getTranslations()
            ],
            'nonce' => wp_create_nonce(GlobalEnum::NONCE_ACTION),
        ];

        if ($admin) {
            $frontendJsGlobalObject['apiKey'] = get_option(OptionEnum::HGES_ACCESS_KEY, '');
            $frontendJsGlobalObject['validatorConstraints'] = FrontendValidator::getAll();
        }

        $jsonObject = wp_json_encode($frontendJsGlobalObject);
        $javascriptString =  "window.hges = $jsonObject;";

        wp_add_inline_script(
            'hges-global-object-injection',
            $javascriptString,
            'before'
        );
    }
}

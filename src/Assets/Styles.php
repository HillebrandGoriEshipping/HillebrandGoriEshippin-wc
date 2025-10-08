<?php

namespace HGeS\Assets;

class Styles
{
    /**
     * Initializes the styles for the frontend
     */
    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
    }

    /**
     * Initializes the styles for the admin area
     */
    public static function initAdmin(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdmin']);
    }

    /**
     * Enqueue the styles for the frontend
     *
     * @return void
     */
    public static function enqueue(): void
    {
        wp_enqueue_style(
            'hges-checkout-style',
            HGES_PLUGIN_URL . 'assets/css/checkout.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-shipping-rates-classic-style',
            HGES_PLUGIN_URL . 'assets/css/shipping-rates-classic.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-modal-style',
            HGES_PLUGIN_URL . 'assets/css/modal.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-leaftlet-style',
            HGES_PLUGIN_URL . 'dist/shippingRatesFill.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-map-style',
            HGES_PLUGIN_URL . 'assets/css/map.css',
            [],
            null
        );
    }

    /** 
     * Enqueue the styles for the admin area
     */
    public static function enqueueAdmin(): void
    {

        wp_enqueue_style(
            'hges-fonts',
            HGES_PLUGIN_URL . 'assets/css/fonts.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-admin-style',
            HGES_PLUGIN_URL . 'assets/css/admin.css',
            [],
            null
        );
        wp_enqueue_style(
            'hges-modal-style',
            HGES_PLUGIN_URL . 'assets/css/modal.css',
            [],
            null
        );

        if (
            !empty($_GET['page']) && $_GET['page'] === 'wc-orders'
            && !empty($_GET['action']) && $_GET['action'] === 'edit'
        ) {
            wp_enqueue_style(
                'hges-filepond-style',
                HGES_PLUGIN_URL . 'node_modules/filepond/dist/filepond.css',
                [],
                null
            );
            wp_enqueue_style(
                'hges-leaflet-style',
                HGES_PLUGIN_URL . 'node_modules/leaflet/dist/leaflet.css',
                [],
                null
            );
            wp_enqueue_style(
                'hges-shipping-rates-classic-style',
                HGES_PLUGIN_URL . 'assets/css/shipping-rates-classic.css',
                [],
                null
            );
            wp_enqueue_style(
                'hges-order-page-style',
                HGES_PLUGIN_URL . 'assets/css/order-page.css',
                ['hges-shipping-rates-classic-style'],
                null
            );

            wp_enqueue_style(
                'woocommerce-packages',
                HGES_PLUGIN_URL . '../woocommerce/assets/client/blocks/packages-style.css',
                [],
                null
            );

            wp_enqueue_style(
                'hges-map-style',
                HGES_PLUGIN_URL . 'assets/css/map.css',
                [],
                null
            );
        }
    }
}

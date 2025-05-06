<?php

namespace HGeS\Assets;

class Styles
{
    /**
     * Enqueue the styles for the frontend
     *
     * @return void
     */
    public static function enqueue(): void
    {
        wp_enqueue_style(
            'hges-checkout-style',
            HGeS_PLUGIN_URL . 'assets/css/checkout.css',
            [],
            null
        );

        wp_enqueue_style(
            'hges-shipping-rates-classic-style',
            HGeS_PLUGIN_URL . 'assets/css/shipping-rates-classic.css',
            [],
            null
        );
        
        wp_enqueue_style(
            'hges-modal-style',
            HGeS_PLUGIN_URL . 'assets/css/modal.css',
            [],
            null
        );
    }

    /** 
     * Enqueue the styles for the admin area
     */
    public static function enqueueAdmin(): void
    {
        if (!empty($_GET['page']) && $_GET['page'] === 'hillebrand-gori-eshipping') {
            wp_enqueue_style(
                'hges-admin-style',
                HGeS_PLUGIN_URL . 'assets/css/admin.css',
                [],
                null
            );
        }
    }
}

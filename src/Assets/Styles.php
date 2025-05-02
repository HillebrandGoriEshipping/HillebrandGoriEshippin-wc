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
    }

    /** 
     * Enqueue the styles for the admin area
     */
    public function enqueueAdmin(): void
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

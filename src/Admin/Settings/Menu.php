<?php

namespace HGeS\Admin\Settings;


class Menu
{
    /**
     * Initialize the settings menu for the admin area
     */
    public static function initAdmin(): void
    {
        add_action('admin_menu', [self::class, 'addSettingsMenu']);
    }

    /** 
     * Add the settings menu to the WooCommerce menu
     * 
     * @return void
     */
    public static function addSettingsMenu(): void
    {
        add_submenu_page(
            'woocommerce',
            'Hillebrand Gori eShipping',
            'Hillebrand Gori eShipping',
            'manage_options',
            'hillebrand-gori-eshipping',
            [SettingsController::class, 'renderSettingsPage'],
            'dashicons-admin-generic'
        );
    }
}

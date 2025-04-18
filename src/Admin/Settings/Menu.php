<?php

namespace HGeS\Admin\Settings;


class Menu
{
    public static function addSettingsMenu()
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

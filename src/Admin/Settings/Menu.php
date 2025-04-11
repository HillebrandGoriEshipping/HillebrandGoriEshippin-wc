<?php

namespace HGeS\Admin\Settings;

class Menu
{
    public static function addSettingsMenu()
    {

        add_menu_page(
            'Hillebrand Gori eShipping',
            'Hillebrand Gori eShipping',
            'manage_options',
            'hillebrand-gori-eshipping',
            [__CLASS__, 'renderSettingsPage'],
            'dashicons-admin-generic'
        );
    }

    public static function renderSettingsPage()
    {
        // Render the settings page content here
        echo '<h1>Hillebrand Gori eShipping Settings</h1>';
        echo '<p>Settings content goes here.</p>';
    }
}

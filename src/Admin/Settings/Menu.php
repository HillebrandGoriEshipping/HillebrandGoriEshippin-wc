<?php

namespace HGeS\Admin\Settings;

use HGeS\Utils\Twig;

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
            [__CLASS__, 'renderSettingsPage'],
            'dashicons-admin-generic'
        );
    }

    public static function renderSettingsPage()
    {
        // Utiliser Twig pour rendre la page
        $twig = Twig::getTwig();
        echo $twig->render('settings-page.twig', [
            'title' => 'Hillebrand Gori eShipping Settings',
            'description' => 'Settings content goes here.',
        ]);
    }
}

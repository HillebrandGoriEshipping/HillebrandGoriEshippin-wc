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
        $options = [
            'VINW_ACCESS_KEY' => get_option('VINW_ACCESS_KEY'),
            'acces_key_validate' => get_option('acces-key-validate', '0'),
            'VINW_MAPBOX_ACCESS_KEY' => get_option('VINW_MAPBOX_ACCESS_KEY'),
            'mapbox_api_key_validate' => get_option('mapbox-api-key-validate', '0'),
            'VINW_PREF_TRANSP' => get_option('VINW_PREF_TRANSP', []),
            'VINW_PREF_STAT' => get_option('VINW_PREF_STAT'),
            'VINW_TAX_RIGHTS' => get_option('VINW_TAX_RIGHTS'),
            'VINW_VAT_CHOICE' => get_option('VINW_VAT_CHOICE'),
            'VINW_VAT_NUMBER' => get_option('VINW_VAT_NUMBER'),
            'VINW_VAT_OSS' => get_option('VINW_VAT_OSS'),
            'VINW_EORI_NUMBER' => get_option('VINW_EORI_NUMBER'),
            'VINW_FDA_NUMBER' => get_option('VINW_FDA_NUMBER'),
            'VINW_ASSURANCE' => get_option('VINW_ASSURANCE'),
            'VINW_NBR_MIN' => get_option('VINW_NBR_MIN'),
            'VINW_EXP_DAYS_MIN' => get_option('VINW_EXP_DAYS_MIN'),
        ];

        //TODO : GET ADDRESS FROM API
        $address = [
            'addressName' => 'Main Office',
            'company' => 'Hillebrand Gori',
            'contact' => 'Mr Smith',
            'phone' => '+33 1 23 45 67 89',
            'address' => 'Rue de la Gare',
            'addressComplement' => 'Bâtiment A',
            'addressComplement2' => '',
            'city' => 'Paris',

        ];

        // Utiliser Twig pour rendre la page
        $twig = Twig::getTwig();
        echo $twig->render('settings-page.twig', [
            'title' => 'Hillebrand Gori eShipping Settings',
            'options' => $options,
            'address' => $address,
        ]);
    }
}

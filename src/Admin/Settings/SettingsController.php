<?php

namespace HGeS\Admin\Settings;

use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Twig;

class SettingsController
{
    public static function renderSettingsPage()
    {
        $options = [];

        foreach (OptionEnum::getList() as $option) {
            $options[$option] = get_option($option);
        }

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

    public static function saveSettings()
    {
        foreach (OptionEnum::getList() as $optionName) {
            if (!isset($_POST[$optionName])) {
                continue;
            }
            update_option($optionName, $_POST[$optionName]);
            if ($optionName === "HGES_ACCESS_KEY") {
                try {
                    $result = ApiClient::get('/package/get-sizes?nbBottles=1');
                    if ($result['status'] === 200) {
                        update_option(OptionEnum::access_key_validate, 1);
                    } else {
                        update_option(OptionEnum::access_key_validate, 0);
                    }
                } catch (\Throwable $th) {
                    update_option(OptionEnum::access_key_validate, 0);
                }
            }
        }
    }
}

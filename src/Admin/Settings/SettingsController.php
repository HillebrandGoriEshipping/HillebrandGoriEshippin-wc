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

        try {
            $address = ApiClient::get('/address/get-addresses');

            if ($address['status'] === 200) {
                $address = $address['data'];
            } else {
                $address = [];
            }
        } catch (\Throwable $th) {
        }

        // Utiliser Twig pour rendre la page
        $twig = Twig::getTwig();
        echo $twig->render('settings-page.twig', [
            'title' => 'Hillebrand Gori eShipping Settings',
            'options' => $options,
            'address' => $address[0],
        ]);
    }

    public static function saveSettings()
    {
        if (wp_verify_nonce($_POST['settings_nonce'], 'save_settings') !== 1) {
            throw new \Exception('Nonce verification failed');
        }
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

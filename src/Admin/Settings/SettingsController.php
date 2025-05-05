<?php

namespace HGeS\Admin\Settings;

use HGeS\Utils\Address;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Twig;

class SettingsController
{
    const SETTING_PAGE_TITLE = 'Hillebrand Gori eShipping Settings';

    /**
     * Render the settings page
     * 
     * @return void
     */
    public static function renderSettingsPage(): void
    {
        $options = [];
        foreach (OptionEnum::getList() as $option) {
            $options[$option] = get_option($option);
        }

        $address = Address::fromApi();

        // Utiliser Twig pour rendre la page
        $twig = Twig::getTwig();
        echo $twig->render('settings-page.twig', [
            'title' => __(self::SETTING_PAGE_TITLE),
            'options' => $options,
            'address' => $address[0],
        ]);
    }

    /**
     * Handle the settings form submission
     * 
     * @throws \Exception
     * @return void
     */
    public static function saveSettings(): void
    {
        if (wp_verify_nonce($_POST['settings_nonce'], 'save_settings') !== 1) {
            throw new \Exception('Nonce verification failed');
        }

        $settingsFormData = new SettingsFormData($_POST);
        $errors = $settingsFormData->validate();

        if ($errors) {
            
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
                        update_option(OptionEnum::ACCESS_KEY_VALIDATE, 1);
                    } else {
                        update_option(OptionEnum::ACCESS_KEY_VALIDATE, 0);
                    }
                } catch (\Throwable $th) {
                    update_option(OptionEnum::ACCESS_KEY_VALIDATE, 0);
                }
            }
        }
    }
}

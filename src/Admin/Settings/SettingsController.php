<?php

namespace HGeS\Admin\Settings;

use HGeS\Utils\Address;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\FormSessionMessages;
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

        try {
            $address = Address::fromApi();
        } catch (\Throwable $e) {
            $address = [null];
        }

        // Utiliser Twig pour rendre la page
        $twig = Twig::getTwig();

        echo $twig->render('settings-page.twig', [
            'title' => __(self::SETTING_PAGE_TITLE),
            'options' => $options,
            'address' => $address[0],
            'errors' => FormSessionMessages::getMessages('error'),
        ]);
    }

    public static function saveApiKey(): void
    {
        if (wp_verify_nonce($_POST['settings_nonce'], 'save_hges_api_key') !== 1) {
            throw new \Exception('Nonce verification failed');
        }

        $accessKey = sanitize_text_field($_POST['HGES_ACCESS_KEY']);

        try {
            $result = ApiClient::get(
                '/package/get-sizes',
                ['nbBottles' => 1],
                [
                    'X-AUTH-TOKEN' => $accessKey,
                ],
                false
            );
            if ($result['status'] === 200) {
                update_option(OptionEnum::ACCESS_KEY_VALIDATE, 1);
                update_option(OptionEnum::HGES_ACCESS_KEY, $accessKey);
            } else {
                update_option(OptionEnum::ACCESS_KEY_VALIDATE, 0);
            }
        } catch (\Throwable $th) {
            update_option(OptionEnum::ACCESS_KEY_VALIDATE, 0);
            FormSessionMessages::setMessages('error', ["HGES_ACCESS_KEY" => "Invalid API key. Please check your access key and try again."]);
        }
        wp_redirect(admin_url('admin.php?page=hillebrand-gori-eshipping'));
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
        $settingsFormData->sanitize(OptionEnum::class);
        $errors = $settingsFormData->validate();

        if ($errors) {
            FormSessionMessages::setMessages('error', $errors);
            wp_redirect(admin_url('admin.php?page=hillebrand-gori-eshipping'));
            return;
        }

        foreach (OptionEnum::getList() as $optionName) {
            if ($optionName === "HGES_ACCESS_KEY" || !$settingsFormData->$optionName) {
                continue;
            }
            update_option($optionName, $settingsFormData->$optionName);
        }

        wp_redirect(admin_url('admin.php?page=hillebrand-gori-eshipping'));
    }
}

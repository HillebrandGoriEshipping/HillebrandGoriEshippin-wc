<?php

namespace HGeS\Admin\Settings;

use HGeS\Form\SettingsFormData;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\FormSessionMessages;
use HGeS\Utils\Translator;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Address;

class SettingsController
{
    const SETTING_PAGE_TITLE = 'Hillebrand Gori eShipping Settings';
    const SETTING_PAGE_URL = 'admin.php?page=hillebrand-gori-eshipping';

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

        $favoriteAddress = Address::getFavoriteAddress();
        if (empty($favoriteAddress)) {
            $favoriteAddress = null;
        }

        try {
            $allAddresses = Address::allFromApi();
        } catch (\Throwable $e) {
            $allAddresses = [];
        }

        try {
            $packagingOptions = ApiClient::get('/v2/packages')['data'];
        } catch (\Throwable $e) {
            $packagingOptions = [];
        }

        $existingPackagingOptions = [];
        foreach ($packagingOptions as $packagingOption) {
            $existingPackagingOptions[$packagingOption['containerType']][] = $packagingOption;
        }

        echo Twig::getTwig()->render('admin/settings-page.twig', [
            'title' => Translator::translate(esc_html(self::SETTING_PAGE_TITLE)),
            'options' => $options,
            'favoriteAddress' => $favoriteAddress,
            'allAddresses' => $allAddresses,
            'errors' => FormSessionMessages::getMessages('error'),
            'existingPackagingOptions' => $existingPackagingOptions,
        ]);
    }

    public static function saveApiKey(): void
    {
        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['settings_nonce'])), 'save_hges_api_key') !== 1) {
            throw new \Exception('Nonce verification failed');
        }

        $accessKey = sanitize_text_field(wp_unslash($_POST[OptionEnum::HGES_ACCESS_KEY]) ?? '');

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
            FormSessionMessages::setMessages('error', [OptionEnum::HGES_ACCESS_KEY => "Invalid API key. Please check your access key and try again."]);
        }
        wp_redirect(admin_url(self::SETTING_PAGE_URL));
    }

    /**
     * Handle the favorite address form submission
     * 
     * @throws \Exception
     * @return void
     */
    public static function saveFavoriteAddress(): void
    {
        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['settings_nonce'])), 'save_favorite_address') !== 1) {
            throw new \Exception('Nonce verification failed');
        }

        $favoriteAddressId = sanitize_text_field(wp_unslash($_POST[OptionEnum::HGES_FAVORITE_ADDRESS_ID]));
        $favoriteAddress = Address::singleFromApi($favoriteAddressId);
        $accessKey = get_option(OptionEnum::HGES_ACCESS_KEY);

        if (empty($accessKey)) {
            FormSessionMessages::setMessages('error', [
                OptionEnum::HGES_FAVORITE_ADDRESS_ID => "Please connect to the API first (missing access key)."
            ]);
            wp_redirect(admin_url(self::SETTING_PAGE_URL));
            return;
        }

        if (empty($favoriteAddress)) {
            FormSessionMessages::setMessages('error', [OptionEnum::HGES_FAVORITE_ADDRESS_ID => "Invalid address ID. Please select a valid address."]);
            wp_redirect(admin_url(self::SETTING_PAGE_URL));
            return;
        }

        update_option(OptionEnum::HGES_FAVORITE_ADDRESS_ID, $favoriteAddressId);
        FormSessionMessages::setMessages('success', ["Favorite address updated successfully."]);
        wp_redirect(admin_url(self::SETTING_PAGE_URL));
    }

    /**
     * Handle the settings form submission
     * 
     * @throws \Exception
     * @return void
     */
    public static function saveSettings(): void
    {
        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['settings_nonce'])), 'save_settings') !== 1) {
            throw new \Exception('Nonce verification failed');
        }

        $settingsFormData = new SettingsFormData($_POST);
        $settingsFormData->sanitize(OptionEnum::class);
        $errors = $settingsFormData->validate();

        if ($errors) {
            FormSessionMessages::setMessages('error', $errors);
            wp_redirect(admin_url(self::SETTING_PAGE_URL));
            return;
        }

        foreach (OptionEnum::getList() as $optionName) {
            if ($optionName === OptionEnum::HGES_ACCESS_KEY || !$settingsFormData->$optionName) {
                continue;
            }
            update_option($optionName, $settingsFormData->$optionName);
        }

        wp_redirect(admin_url(self::SETTING_PAGE_URL));
    }
}

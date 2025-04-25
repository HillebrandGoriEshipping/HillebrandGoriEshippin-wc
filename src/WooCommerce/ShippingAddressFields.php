<?php

namespace HGeS\WooCommerce;

class ShippingAddressFields {

    /**
     * Lists the available locations to be used in "location" option field
     * https://developer.woocommerce.com/docs/cart-and-checkout-additional-checkout-fields/#options
     */
    const WC_CHECKOUT_FIELDS_LOCATIONS = [
        'CONTACT' => 'contact',
        'ADDRESS' => 'address',
        'ORDER' => 'order',
    ];

    /**
     * Lists the available field types to be used in "type" option field
     * https://developer.woocommerce.com/docs/cart-and-checkout-additional-checkout-fields/#options
     */
    const WC_CHECKOUT_FIELDS_TYPES = [
        'TEXT' => 'text',
        'SELECT' => 'select',
        'CHECKBOX' => 'checkbox',
    ];

    /**
     * See the woocommerce documentation to get the list of available options
     * https://developer.woocommerce.com/docs/cart-and-checkout-additional-checkout-fields/#options
     */
    const IS_COMPANY_CHECKBOX_OPTIONS = [
        'id' => 'hges/is-company-address',
        'label' => 'Company address',
        'optionalLabel' => 'I am a company',
        'location' => self::WC_CHECKOUT_FIELDS_LOCATIONS['ADDRESS'],
        'type' => self::WC_CHECKOUT_FIELDS_TYPES['CHECKBOX'],
        'attributes' => [],
        'required' => false,
        'hidden' => false,
        'validation' => [],
    ];

    /**
     * List of translatable option fields
     */
    const TRANSLATABLE_FIELDS = [
        'optionLabel', 'label'
    ];

    /**
     * Controls the custom field registration
     */
    public static function register(): void
    {
        self::isCompanyField();
    }

    /**
     * Registers the "address type (company/individual)" checkbox
     */
    public static function isCompanyField(): void
    {
        $options = self::applyI18n(self::IS_COMPANY_CHECKBOX_OPTIONS);
        \woocommerce_register_additional_checkout_field($options);
    }

    /**
     * Replaces the translatable strings from a given options array
     */
    public static function applyI18n(array $options): array
    {
        $translated = $options;
        foreach ($options as $optionKey => $optionValue) {
            if (in_array($optionKey, self::TRANSLATABLE_FIELDS)) {
                $translated[$optionKey] = \__($optionValue, 'HillebrandGorieShipping');
            }
        }
        return $translated;
    }
}

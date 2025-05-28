<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Enums\GlobalEnum;
use HGeS\Utils\Twig;
use Automattic\WooCommerce\Admin\Overrides\Order;

/**
 * Class ShippingAddressFields
 *
 * This class is responsible for registering the custom fields in the checkout page. 
 * The methods are mostly designed to be used in blocks UI mode, but the class also
 * handles the classic UI mode `woocommerce_checkout_fields` filter callback.
 *
 * @see https://developer.woocommerce.com/docs/cart-and-checkout-additional-checkout-fields/
 */
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

    const WC_ORDER_META_PREFIX_SHIPPING = 'wc_shipping/';
    const WC_ORDER_META_PREFIX_BILLING = 'wc_billing/';

    /**
     * See the woocommerce documentation to get the list of available options
     * https://developer.woocommerce.com/docs/cart-and-checkout-additional-checkout-fields/#options
     */
    const IS_COMPANY_CHECKBOX_OPTIONS = [
        'id' => 'hges/is-company-address',
        'label' => 'Business order',
        'optionalLabel' => 'Business order',
        'location' => self::WC_CHECKOUT_FIELDS_LOCATIONS['ADDRESS'],
        'type' => self::WC_CHECKOUT_FIELDS_TYPES['CHECKBOX'],
        'attributes' => [],
        'required' => false,
        'hidden' => false,
        'validation' => [],
    ];
    const COMPANY_NAME_FIELD_OPTIONS = [
        'id' => 'hges/company-name',
        'label' => 'Company name',
        'optionalLabel' => 'Company name',
        'location' => self::WC_CHECKOUT_FIELDS_LOCATIONS['ADDRESS'],
        'type' => self::WC_CHECKOUT_FIELDS_TYPES['TEXT'],
        'attributes' => [],
        'required' => false,
        'hidden' => false,
        'validation' => [],
    ];
    const EXCISE_NUMBER_FIELD_OPTIONS = [
        'id' => 'hges/excise-number',
        'label' => 'Excise number',
        'optionalLabel' => 'Excise number',
        'location' => self::WC_CHECKOUT_FIELDS_LOCATIONS['ADDRESS'],
        'type' => self::WC_CHECKOUT_FIELDS_TYPES['TEXT'],
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

    const SHIPPING_IS_COMPANY_METANAME = '_' . self::WC_ORDER_META_PREFIX_SHIPPING . self::IS_COMPANY_CHECKBOX_OPTIONS['id'];
    const SHIPPING_COMPANY_NAME_METANAME = '_' . self::WC_ORDER_META_PREFIX_SHIPPING . self::COMPANY_NAME_FIELD_OPTIONS['id'];
    const SHIPPING_EXCISE_NUMBER_METANAME = '_' . self::WC_ORDER_META_PREFIX_SHIPPING . self::EXCISE_NUMBER_FIELD_OPTIONS['id'];


    /**
     * Controls the custom field registration
     */
    public static function register(): void
    {
        self::isCompanyField();
        self::companyNameField();
        self::exciseNumberField();
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
     * Registers the "company name" text field
     */
    public static function companyNameField(): void
    {
        $options = self::applyI18n(self::COMPANY_NAME_FIELD_OPTIONS);
        \woocommerce_register_additional_checkout_field($options);
    }

    /**
     * Registers the "excise number" text field
     */
    public static function exciseNumberField(): void
    {
        $options = self::applyI18n(self::EXCISE_NUMBER_FIELD_OPTIONS);
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
                $translated[$optionKey] = __($optionValue, GlobalEnum::TRANSLATION_DOMAIN);
            }
        }
        return $translated;
    }

    /**
     * Add the fields to the checkout fields array in classic UI mode
     * 
     * @param array $fields
     * @return array
     */
    public static function filterClassicUiFields(array $fields): array
    {
        $isCompanyCheckbox = [
            'type' => 'checkbox',
            'label' => __(self::IS_COMPANY_CHECKBOX_OPTIONS['label'], GlobalEnum::TRANSLATION_DOMAIN),
            'class' => ['form-row-wide'],
            'required' => false,
        ];
        $companyNameField = [
            'type' => 'text',
            'label' => __(self::COMPANY_NAME_FIELD_OPTIONS['label'], GlobalEnum::TRANSLATION_DOMAIN),
            'class' => ['form-row-wide'],
            'required' => false,
        ];
        $exciseNumberField = [
            'type' => 'text',
            'label' => __(self::EXCISE_NUMBER_FIELD_OPTIONS['label'], GlobalEnum::TRANSLATION_DOMAIN),
            'class' => ['form-row-wide'],
            'required' => false,
        ];
        // generate the field id to match the behavior of the blocks UI mode 
        $fields['billing'][self::WC_ORDER_META_PREFIX_BILLING . self::IS_COMPANY_CHECKBOX_OPTIONS['id']] = $isCompanyCheckbox;
        $fields['shipping'][self::WC_ORDER_META_PREFIX_SHIPPING . self::IS_COMPANY_CHECKBOX_OPTIONS['id']] = $isCompanyCheckbox;
        $fields['billing'][self::WC_ORDER_META_PREFIX_BILLING . self::COMPANY_NAME_FIELD_OPTIONS['id']] = $companyNameField;
        $fields['shipping'][self::WC_ORDER_META_PREFIX_SHIPPING . self::COMPANY_NAME_FIELD_OPTIONS['id']] = $companyNameField;
        $fields['billing'][self::WC_ORDER_META_PREFIX_BILLING . self::EXCISE_NUMBER_FIELD_OPTIONS['id']] = $exciseNumberField;
        $fields['shipping'][self::WC_ORDER_META_PREFIX_SHIPPING . self::EXCISE_NUMBER_FIELD_OPTIONS['id']] = $exciseNumberField;

        return $fields;
    }

    /**
     * Handles the order creation process to save the custom fields in classic UI mode
     * 
     * @param \WC_Order $order
     */
    public static function onOrderCreate($order, $data): void
    {
        if (empty($data[self::WC_ORDER_META_PREFIX_BILLING . self::IS_COMPANY_CHECKBOX_OPTIONS['id']])
            && empty($data[self::WC_ORDER_META_PREFIX_BILLING . self::COMPANY_NAME_FIELD_OPTIONS['id']])
        ) {
            return;
        }

        $customPostFields = [
            self::WC_ORDER_META_PREFIX_BILLING . self::IS_COMPANY_CHECKBOX_OPTIONS['id'],
            self::WC_ORDER_META_PREFIX_SHIPPING . self::IS_COMPANY_CHECKBOX_OPTIONS['id'],
            self::WC_ORDER_META_PREFIX_BILLING . self::COMPANY_NAME_FIELD_OPTIONS['id'],
            self::WC_ORDER_META_PREFIX_SHIPPING . self::COMPANY_NAME_FIELD_OPTIONS['id'],
            self::WC_ORDER_META_PREFIX_BILLING . self::EXCISE_NUMBER_FIELD_OPTIONS['id'],
            self::WC_ORDER_META_PREFIX_SHIPPING . self::EXCISE_NUMBER_FIELD_OPTIONS['id'],
        ];

        foreach ($customPostFields as $field) {
            if (isset($data[$field])) {
                $order->update_meta_data('_'.$field, $data[$field]);
            }
        }

        if (!$data['ship_to_different_address']) {
            $billingIsCompanyValue = $data[self::WC_ORDER_META_PREFIX_BILLING . self::IS_COMPANY_CHECKBOX_OPTIONS['id']];
            $billingCompanyNameValue = $data[self::WC_ORDER_META_PREFIX_BILLING . self::COMPANY_NAME_FIELD_OPTIONS['id']];
            $billingExciseNumberValue = $data[self::WC_ORDER_META_PREFIX_BILLING . self::EXCISE_NUMBER_FIELD_OPTIONS['id']];
            
            $order->update_meta_data(self::SHIPPING_IS_COMPANY_METANAME, $billingIsCompanyValue);
            $order->update_meta_data(self::SHIPPING_COMPANY_NAME_METANAME, $billingCompanyNameValue);
            $order->update_meta_data(self::SHIPPING_EXCISE_NUMBER_METANAME, $billingExciseNumberValue);
        }
    }

    /**
     * Displays the custom fields in the order confirmation page in classic UI mode
     * 
     * @param string $address
     * @param string $rawAddress
     * @param \WC_Order $order
     * @return string
     */
    public static function renderOrderConfirmationAddress(string $address, array $rawAddress = null, Order | null $order): string
    {
        if ('store-api' === $order->get_created_via() || is_admin()) {
            return $address;
        }

        $address = self::getRenderedOrderConfirmationAddress($address);
        echo $address;
    }

    public static function getRenderedOrderConfirmationAddress(string $address): string
    {
        $companyBlock = self::getRenderedCompanyBlock($order);
        $address .= $companyBlock;
        return $address;
    }

    public static function renderCompanyBlock($order)
    {
        $companyBlock = self::getRenderedCompanyBlock($order);
        echo $companyBlock;
    }

    public static function getRenderedCompanyBlock(Order $order): string
    {
        $data = [
            'isCompany' => $order->get_meta(self::SHIPPING_IS_COMPANY_METANAME, true) ? __('Yes') : __('No'),
            'companyName' => $order->get_meta(self::SHIPPING_COMPANY_NAME_METANAME, true),
            'exciseNumber' => $order->get_meta(self::SHIPPING_EXCISE_NUMBER_METANAME, true),
        ];

        $companyBlock = Twig::getTwig()->render(
            'checkout/confirm-shipping-address-custom-fields.twig',
            $data,
        );

        return $companyBlock;
    }
}

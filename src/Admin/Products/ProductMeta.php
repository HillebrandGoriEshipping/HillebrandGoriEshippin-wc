<?php

namespace HGeS\Admin\Products;

use HGeS\Utils\Twig;
use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\Utils\Enums\GlobalEnum;
use HGeS\Utils\HSCodeHelper;
use HGeS\WooCommerce\ProductType\SimpleBottleProduct;
use HGeS\WooCommerce\ProductType\VariableBottleProduct;

class ProductMeta
{

    /**
     * Initializes the admin hooks and filters for product meta management.
     */
    public static function init(): void
    {
        add_filter('woocommerce_product_class', [self::class, 'getClassNameByProductType'], 10, 2);
    }


    /**
     * Initializes the admin hooks and filters for product meta management.
     */
    public static function initAdmin(): void
    {
        add_filter('woocommerce_product_data_tabs', [self::class, 'customTab']);
        add_action('woocommerce_product_data_panels', [self::class, 'displayProductFields']);
        add_action('woocommerce_process_product_meta', [self::class, 'saveProductFields']);
        add_action('woocommerce_product_after_variable_attributes', [self::class, 'displayVariableProductField'], 10, 3);
        add_action('woocommerce_save_product_variation', [self::class, 'saveVariableProductField'], 10, 2);
        add_filter('woocommerce_product_class', [self::class, 'getClassNameByProductType'], 10, 2);
        add_filter('woocommerce_product_data_tabs', [self::class, 'getGeneralTabInCustomTypes']);
        add_action('woocommerce_product_options_shipping', [self::class, 'addShippingFields']);
    }

    /**
     * Adds a custom tab to the WooCommerce product data tabs.
     *
     * @param array $tabs An array of existing product data tabs.
     * 
     * @return array Modified array of product data tabs with the custom tab added.
     * 
     */
    public static function customTab(array $tabs): array
    {
        $tabs['HGeS_product_tab'] = [
            'label'    => __('Bottle Settings', GlobalEnum::TRANSLATION_DOMAIN),
            'target'   => 'HGeS_product_tab_options',
            'priority' => 21,
            'class'    => ['show_if_bottle-simple', 'show_if_bottle-variable'],
        ];

        return $tabs;
    }

    /**
     * Displays custom product fields in the admin interface.
     *
     * @return void Outputs the rendered HTML for the product fields.
     */
    public static function displayProductFields(): void
    {
        $twig = Twig::getTwig();

        $productMeta = [];
        foreach (ProductMetaEnum::getList() as $meta) {
            $productMeta[$meta] = get_post_meta(get_the_ID(), $meta, true);
        }

        $producingCountries = [
            'FR' => 'France',
            'GB' => 'Great Britain',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'PT' => 'Portugal',
        ];

        $html = $twig->render('product-metas.twig', [
            'productMeta' => $productMeta,
            'producingCountries' => $producingCountries,
        ]);

        echo $html;
    }

    /**
     * Display a custom field for the variable product.
     *
     * @param int $loop_index The index of the variation in the loop.
     * @param array $variation_data The variation data.
     * @param object $variation The variation object.
     *
     * @return void
     */
    public static function displayVariableProductField(int $loop_index, array $variation_data, object $variation): void
    {
        $parent_product = wc_get_product($variation->post_parent);

        if ($parent_product && $parent_product->is_type(VariableBottleProduct::PRODUCT_TYPE)) {
            $twig = Twig::getTwig();
            $value = get_post_meta($variation->ID, '_variation_quantity', true);

            echo $twig->render('variable-product-meta.twig', [
                'value' => $value,
                'index' => $loop_index,
            ]);
        }
    }

    /**
     * Save custom product fields to the database.
     *
     * @param int $post_id The ID of the product post being saved.
     *
     * @return void
     */
    public static function saveProductFields(int $post_id): void
    {
        foreach (ProductMetaEnum::getList() as $meta) {
            if (isset($_POST[$meta])) {
                update_post_meta($post_id, $meta, $_POST[$meta]);
            }
        }
    }

    /**
     * Save custom product fields for variable products.
     *
     * @param int $post_id The ID of the product post being saved.
     * @param int $i The index of the variation being saved.
     *
     * @return void
     */
    public static function saveVariableProductField(int $variation_id, int $i): void
    {
        if (isset($_POST['_variation_quantity'][$i])) {
            update_post_meta(
                $variation_id,
                '_variation_quantity',
                $_POST['_variation_quantity'][$i]
            );
        }
    }

    /**
     * Returns the fully qualified class name for a given product type.
     *
     * @param string $classname     The default class name to return if no match is found.
     * @param string $product_type  The product type identifier to check.
     * @return string               The fully qualified class name for the product type, or the default class name.
     */
    public static function getClassNameByProductType(string $classname, string $product_type): string
    {
        switch ($product_type) {
            case SimpleBottleProduct::PRODUCT_TYPE:
                return SimpleBottleProduct::class;
            case VariableBottleProduct::PRODUCT_TYPE:
                return VariableBottleProduct::class;
            default:
                return $classname;
        }
    }

    /**
     * Modifies the classes of product data tabs for custom product types.
     *
     * @param array $tabs The array of product data tabs, each containing tab properties including 'class'.
     * @return array The modified array of tabs with updated classes for custom product types.
     */
    public static function getGeneralTabInCustomTypes(array $tabs): array
    {
        foreach ($tabs as $key => &$tab) {
            if ($key === 'variations') {
                $tab['class'][] = 'show_if_' . VariableBottleProduct::PRODUCT_TYPE;
            }
            if (isset($tab['class']) && in_array('show_if_simple', $tab['class'])) {
                $tab['class'][] = 'show_if_bottle-simple';
            }
        }
        return $tabs;
    }

    /**
     * Adds custom shipping fields to the product options in WooCommerce.
     *
     * @return void
     */
    public static function addShippingFields(): void
    {
        $twig = Twig::getTwig();
        $data = [
            'productHsCode' => get_post_meta(get_the_ID(), ProductMetaEnum::HS_CODE, true),
            'productAppellation' => get_post_meta(get_the_ID(), ProductMetaEnum::APPELLATION, true),
        ];
        $isWine = HSCodeHelper::isWine($data['productHsCode']);
        $data['isWine'] = $isWine;

        echo $twig->render('admin/product/product-meta-shipping.twig', $data);
    }
}
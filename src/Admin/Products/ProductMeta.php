<?php

namespace HGeS\Admin\Products;

use HGeS\Utils\Twig;
use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\Utils\ApiClient;

class ProductMeta
{

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
            'label'    => __('Bottle Settings', 'woocommerce'),
            'target'   => 'HGeS_product_tab_options',
            'class'    => ['show_if_simpleBottleProduct', 'show_if_variableBottleProduct'],
            'priority' => 21,
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
    public static function displayVariableProductField($loop_index, $variation_data, $variation): void
    {
        $product = wc_get_product($variation->ID);
        $twig = Twig::getTwig();
        $value = get_post_meta($variation->ID, '_variation_quantity', true);

        if ($product->is_type('variation')) {
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
    public static function  saveProductFields(int $post_id): void
    {
        // TODO: add a sanitizer
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
    public static function saveVariableProductField($variation_id, $i): void
    {
        if (isset($_POST['_variation_quantity'][$i])) {
            update_post_meta(
                $variation_id,
                '_variation_quantity',
                $_POST['_variation_quantity'][$i]
            );
        }
    }
}

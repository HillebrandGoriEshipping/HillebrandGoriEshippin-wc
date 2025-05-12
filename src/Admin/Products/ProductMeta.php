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
            'label'    => __('Hillebrand Gori eSHipping', 'woocommerce'),
            'target'   => 'HGeS_product_tab_options',
            'class'    => [],
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

        $producingCounties = [
            'FR' => 'France',
            'GB' => 'Great Britain',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'PT' => 'Portugal',
        ];

        $html = $twig->render('product-metas.twig', [
            'productMeta' => $productMeta,
            'producingCountries' => $producingCounties,
        ]);

        echo $html;
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
}

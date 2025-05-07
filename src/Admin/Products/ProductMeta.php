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
            // 'data' => $data,
            'productMeta' => $productMeta,
            'appellationList' => self::getAppellationsFromApi($productMeta[ProductMetaEnum::PRODUCING_COUNTRY]),
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

    /**
     * Retrieves a list of appellations from the API based on the producing country.
     *
     * @param string $producingCountry The name of the producing country to filter appellations.
     * 
     * @return array An array of appellations retrieved from the API.
     * 
     * @throws \Throwable If an error occurs during the API request or processing.
     */
    public static function getAppellationsFromApi(string $producingCountry): array
    {
        $appellationList = [];
        try {
            $result = ApiClient::get('/get-appellations?producingCountry=' . $producingCountry);
            if ($result['status'] === 200) {
                $appellationList = $result['data'];
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return $appellationList;
    }

    /**
     * Retrieves appellations by country and sends a JSON response.
     *
     * @return void
     *
     * @throws \Throwable If an error occurs during the API call.
     *
     */
    public static function getAppellationsByCountry(): void
    {
        if (!isset($_GET['country'])) {
            wp_send_json_error('Missing country param', 400);
        }

        $country = sanitize_text_field($_GET['country']);

        try {
            $appellations = self::getAppellationsFromApi($country);
            wp_send_json_success($appellations);
        } catch (\Throwable $e) {
            wp_send_json_error($e->getMessage(), 500);
        }
    }
}

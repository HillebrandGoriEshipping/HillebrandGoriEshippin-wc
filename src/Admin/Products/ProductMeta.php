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

        $bottleNumber = get_post_meta(get_the_ID(), ProductMetaEnum::NUMBER_OF_BOTTLE, true);
        $bottleSize = get_post_meta(get_the_ID(), ProductMetaEnum::NUMBER_OF_BOTTLE, true);
        $type = get_post_meta(get_the_ID(), ProductMetaEnum::TYPE, true);
        $color = get_post_meta(get_the_ID(), ProductMetaEnum::COLOR, true);
        $capacity = get_post_meta(get_the_ID(), ProductMetaEnum::CAPACITY, true);
        $alcoholPercentage = get_post_meta(get_the_ID(), ProductMetaEnum::ALCOHOL_PERCENTAGE, true);
        $vintageYear = get_post_meta(get_the_ID(), ProductMetaEnum::VINTAGE_YEAR, true);
        $producingCountry = get_post_meta(get_the_ID(), ProductMetaEnum::PRODUCING_COUNTRY, true);
        $appellation = get_post_meta(get_the_ID(), ProductMetaEnum::APPELLATION, true);

        $data = [
            'bottleNumber' => $bottleNumber,
            'bottleSize' => $bottleSize,
            'type' => $type,
            'color' => $color,
            'capacity' => $capacity,
            'alcoholPercentage' => $alcoholPercentage,
            'vintageYear' => $vintageYear,
            'producingCountry' => $producingCountry,
            'appellation' => $appellation,
        ];


        $html = $twig->render('product-metas.twig', [
            'data' => $data,
            'appellations' => self::getAppellationsFromApi(),
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

    public static function getAppellationsFromApi(string $producingCountry = "FR"): array
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

    public static function getAppellationsByCountry()
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

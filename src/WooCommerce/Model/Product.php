<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\Utils\Messages;
use HGeS\Utils\Translator;

class Product
{
    public static function initAdmin(): void
    {
        add_filter('wp_insert_post_data', [self::class, 'validateBeforeSave'], 10, 2);
        add_action('admin_notices', [self::class, 'maybeDisplayAdminError']);
    }

    /**
     * Validates product data before saving to ensure required custom meta fields are present for specific product types.
     *
     * @param array $data    The product data to be saved.
     * @param array $postarr The original post data array.
     * @return array         The potentially modified product data.
     */
    public static function validateBeforeSave(array $data, array $postarr): array
    {
        if ($data['post_type'] !== 'product') {
            return $data;
        }

        $productType = wp_unslash($_POST['product-type']) ?? '';

        if (!in_array($productType, ['bottle-simple', 'bottle-variable'], true)) {
            return $data;
        }

        $requiredFields = [
            ProductMetaEnum::HS_CODE => Translator::translate('Valid appellation'),
            ProductMetaEnum::CAPACITY => Translator::translate('Capacity'),
            ProductMetaEnum::ALCOHOL_PERCENTAGE => Translator::translate('Alcohol Percentage'),
            ProductMetaEnum::COLOR => Translator::translate('Color'),
        ];

        $missing = [];

        foreach ($requiredFields as $key => $label) {
            if (empty($_POST[$key])) {
                $missing[] = $label;
            }
        }

        if (!empty($missing)) {
            $data['post_status'] = 'draft';

            add_filter('redirect_post_location', function ($location) use ($missing) {
                return add_query_arg([
                    'hges_error' => 'missing_meta',
                    'hges_missing_fields' => implode(',', $missing),
                ], $location);
            });
        }

        return $data;
    }

    /**
     * Displays an admin error notice in the WordPress dashboard if required product meta fields are missing.
     *
     * @return void
     */
    public static function maybeDisplayAdminError(): void
    {
        if (isset($_GET['hges_error']) && $_GET['hges_error'] === 'missing_meta') {

            if (isset($_GET['message'])) {
                unset($_GET['message']);
            }

            $fields = isset($_GET['hges_missing_fields'])
                ? explode(',', sanitize_text_field(wp_unslash($_GET['hges_missing_fields'])))
                : [];

            $fieldsList = !empty($fields) ? implode(', ', $fields) : 'certains champs requis';

            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . Messages::getMessage('productMeta.missingMetaFields') . ': <strong>' . esc_html($fieldsList) . '</strong>.</p>';
            echo '</div>';
        }
    }
}

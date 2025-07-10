<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Utils\Enums\ProductMetaEnum;

class Product
{
    public static function initAdmin(): void
    {
        add_action('save_post_product', [self::class, 'validateProductMeta'], 10, 3);
        add_filter('redirect_post_location', [self::class, 'addErrorQueryArg'], 99, 2);
        add_action('admin_notices', [self::class, 'showAdminError']);
    }

    protected static bool $isMetaInvalid = false;

    public static function validateProductMeta(int $postId, \WP_Post $post, bool $update): void
    {
        static $running = false;

        if ($running) {
            return;
        }

        $running = true;

        if (wp_is_post_revision($postId)) {
            $running = false;
            return;
        }

        $productType = $_POST['product-type'] ?? '';
        if (!in_array($productType, ['bottle-simple', 'bottle-variable'], true)) {
            return;
        }

        // Check if metas are already set
        $hsCode = sanitize_text_field($_POST[ProductMetaEnum::HS_CODE] ?? '');
        $capacity = sanitize_text_field($_POST[ProductMetaEnum::CAPACITY] ?? '');
        $alcohol = sanitize_text_field($_POST[ProductMetaEnum::ALCOHOL_PERCENTAGE] ?? '');
        $color = sanitize_text_field($_POST[ProductMetaEnum::COLOR] ?? '');

        if (empty($hsCode) || empty($capacity) || empty($alcohol) || empty($color)) {
            self::$isMetaInvalid = true;

            wp_update_post([
                'ID' => $postId,
                'post_status' => 'draft',
            ]);

            delete_post_meta($postId, '_hs_code');
        }

        $running = false;
    }

    public static function addErrorQueryArg(string $location, int $postId): string
    {
        if (self::$isMetaInvalid) {
            return add_query_arg('hges_error', 'missing_meta', $location);
        }

        return $location;
    }

    public static function showAdminError(): void
    {
        if (!isset($_GET['hges_error']) || $_GET['hges_error'] !== 'missing_meta') {
            return;
        }

        echo '<div class="notice notice-error is-dismissible"><p>';
        echo esc_html__('You must complete all bottle settings before publishing the product.', 'hges');
        echo '</p></div>';
    }
}

<?php

namespace HGeS\WooCommerce;

/**
 * Class BottleShippingClass
 *
 * Handles the creation of a custom WooCommerce product shipping class for bottled products.
 *
 */
class BottleShippingClass
{

    /**
     * Creates a custom WooCommerce product shipping class named "Bottle" if it does not already exist.
     *
     * @return void
     */
    public static function create(): void
    {
        $class_name = 'Bottle';
        $slug       = sanitize_title($class_name);

        $existing = get_terms('product_shipping_class', [
            'slug'   => $slug,
            'fields' => 'ids',
        ]);

        if (empty($existing)) {
            wp_insert_term($class_name, 'product_shipping_class', [
                'slug' => $slug,
                'description' => __('Hillebrand Gori eShipping\'s shipping class for bottled products.', 'hges'),
            ]);
        }
    }
}

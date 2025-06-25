<?php

namespace HGeS\Admin\Order;

use HGeS\WooCommerce\Model\ShippingMethod;

class ShippingMethodRow {
    
    /**
     * Initialize the admin hooks and filters for the shipping method row
     */
    public static function initAdmin() {
        add_action('woocommerce_before_order_itemmeta', [self::class, 'beforeOrderItemMeta'], 10, 3);
        add_action('woocommerce_after_order_itemmeta', [self::class, 'afterOrderItemMeta'], 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', [self::class, 'hiddenOrderItemMeta'], 10, 1);
    }

    /**
     * Displays the shipping method edition button
     * triggered by the 'woocommerce_before_order_itemmeta' action
     */
    public static function beforeOrderItemMeta($item_id, $item, $order) {
        if (
            get_class($item) !== 'WC_Order_Item_Shipping' 
            || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID
        ) {
            return;
        }
        echo '<div class="error-message">THIS SHIPPING METHOD IS NOT AVAILABLE ANYMORE.</div>';
        echo '<button type="button">Edit shipping options</button>';
        echo '<div style="display: none">';
    }

    /**
     * Closes the div opened in beforeOrderItemMeta
     * triggered by the 'woocommerce_after_order_itemmeta' action
     */
    public static function afterOrderItemMeta($item_id, $item, $order) {
        echo '</div>';
    }

    /**
     * Hide all the meta data related to the shipping method so they can't be edited one by one
     * triggered by the 'woocommerce_hidden_order_itemmeta' filter
     */
    public static function hiddenOrderItemMeta($hidden) {
        $hidden[] = 'deliveryDate';
        $hidden[] = 'pickupDate';
        $hidden[] = 'insurancePrice';
        $hidden[] = 'carrier';
        $hidden[] = 'deliveryMode';
        $hidden[] = 'firstPickupDelivery';
        return $hidden;
    }
}
<?php

namespace HGeS\WooCommerce;

/**
 * This class exposes methods to interact with the WooCommerce orders
 */
class Order {

    /**
     * The pickup point meta key used in database
     */
    const PICKUP_POINT_META_KEY = 'hges_pickup_point';

    /**
     * Update the selected pickup point for the given order
     * 
     * @param int $orderId the ID for the woocommerce order to update
     * @param array $pickupPoint the associative array describing the pickup point
     * 
     * @return void
     */
    public static function setPickupPoint(int $orderId,array $pickupPoint): void
    {
        $order = wc_get_order($orderId);
        $order->update_meta_data(self::PICKUP_POINT_META_KEY, $pickupPoint);
        $order->save_meta_data();
    }
}

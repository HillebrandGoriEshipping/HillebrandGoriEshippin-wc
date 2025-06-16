<?php

namespace HGeS\WooCommerce;

/**
 * This class exposes methods to interact with the WooCommerce orders
 */
class Order
{

    /**
     * The pickup point meta key used in database
     */
    const PICKUP_POINT_META_KEY = 'hges_pickup_point';

    /**
     * Initialize the order hooks and filters
     */
    public static function init(): void
    {
        add_action('woocommerce_checkout_create_order', [self::class, 'setOrderPickupMeta'], 10, 2);
    }

    /**
     * Update the selected pickup point for the given order
     * 
     * @param int $orderId the ID for the woocommerce order to update
     * @param array $pickupPoint the associative array describing the pickup point
     * 
     * @return void
     */
    public static function setPickupPoint(int $orderId, array $pickupPoint): void
    {
        $order = wc_get_order($orderId);
        $order->update_meta_data(self::PICKUP_POINT_META_KEY, $pickupPoint);
        $order->save_meta_data();
    }

    /**
     * Sets pickup point metadata for a WooCommerce order based on POST data.
     *
     * @param mixed $order The WooCommerce order object to update.
     *
     * @return void
     */
    public static function setOrderPickupMeta(mixed $order): void
    {
        if (
            !isset($_POST['hges_pickup_point_data']) ||
            !is_string($_POST['hges_pickup_point_data'])
        ) {
            return;
        }

        $pickupPointJson = stripslashes($_POST['hges_pickup_point_data']);
        $pickupPoint = json_decode($pickupPointJson, true);

        if (
            !is_array($pickupPoint) ||
            !isset(
                $pickupPoint['id'],
                $pickupPoint['name'],
                $pickupPoint['addLine1'],
                $pickupPoint['city'],
                $pickupPoint['zipCode'],
                $pickupPoint['country'],
                $pickupPoint['latitude'],
                $pickupPoint['longitude'],
                $pickupPoint['distance'],
                $pickupPoint['distanceUnitOfMeasurement']
            )
        ) {
            return;
        }

        $pickupPoint = array_map(
            function ($value) {
                return is_string($value) ? sanitize_text_field($value) : $value;
            },
            $pickupPoint
        );

        $string_keys = ['id', 'name', 'addLine1', 'city', 'zipCode', 'country', 'distanceUnitOfMeasurement'];
        foreach ($string_keys as $key) {
            if (!is_string($pickupPoint[$key])) {
                return;
            }
        }

        if (
            !is_numeric($pickupPoint['latitude']) ||
            !is_numeric($pickupPoint['longitude']) ||
            !is_numeric($pickupPoint['distance'])
        ) {
            return;
        }

        foreach ($pickupPoint as $key => $value) {
            $order->update_meta_data('_hges_pickup_point_' . sanitize_key($key), $value);
        }
    }
}

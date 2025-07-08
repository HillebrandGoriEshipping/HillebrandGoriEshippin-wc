<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Rate;
use HGeS\Utils\Messages;

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
        add_action('woocommerce_order_edit_status', [self::class, 'checkShippingBeforeStatusUpdate'], 10, 2);
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

    public static function updateSelectedShippingRate(int $orderId,int $orderShippingItemId, string $shippingRateChecksum): ?array
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return null;
        }

        $item = $order->get_item($orderShippingItemId);
        $rate = Rate::getByChecksum($shippingRateChecksum);
        if (!$item || !$rate) {
            throw new \Exception("Order item or shipping rate not found.");
        }
        $item->set_props([
            "name" => $rate['service'],
            "method_title" => $rate['service'],
            "method_id" => "hges_shipping",
            "instance_id" => "8",
            "total" => $rate['shippingPrice']['amount'],
            "total_tax" => "0",
            "taxes" => ["total" => []],
            "tax_status" => "taxable",
        ]);

        $item->get_data_store()->update($item);
        $item->save();

        $order->calculate_totals();

        return $rate;
    }

    public static function checkShippingBeforeStatusUpdate(int $orderId, string $newStatus): bool
    {
        if ($newStatus !== 'processing' && $newStatus !== 'completed') {
            return true;
        }   
        $shippingRateChecksum = self::getShippingRateChecksum($orderId);
        $shippingMethodStillAvailable = Rate::isStillAvailable($shippingRateChecksum);
        if (!$shippingMethodStillAvailable) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin')['shippingRateNotAvailable']);
            $url = add_query_arg( 'error', Messages::getMessage('orderAdmin')['shippingRateNotAvailable'], wp_get_referer());
            wp_redirect($url);
            exit;
        }
        return $shippingMethodStillAvailable;
    } 

    /**
     * Get the shipping rate checksum for a specific order and shipping item.
     * 
     * @param int $orderId The ID of the order.
     * @return string|null The shipping rate checksum if found, otherwise null.
     */
    public static function getShippingRateChecksum(int $orderId): ?string
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return null;
        }
        $item = array_pop($order->get_items('shipping'));
        if (!$item || get_class($item) !== 'WC_Order_Item_Shipping' || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID) {
            return null;
        }
        $shippingRateChecksumMeta = array_find($item->get_meta_data(), function (\WC_Meta_Data $meta) {
            return $meta->key === 'checksum';
        });

        return $shippingRateChecksumMeta ? $shippingRateChecksumMeta->value : null;
    }

    /**
     * Update the documents for a specific order.
     */
    public static function updateDocuments()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($data['orderId']) ? intval($data['orderId']) : 0;
        $documents = isset($data['documents']) ? $data['documents'] : null;
        $order = wc_get_order($orderId);
        if (!$order || !$documents) {
            wp_send_json_error(['message' => 'Invalid order ID or documents data.'], 400);
            return;
        }

        if (!empty($documents)) {
            $order->update_meta_data('hges_documents', $documents);
            $order->save_meta_data();
            $response = [
                'success' => true,
                'message' => 'Documents updated successfully.',
                'documents' => $documents,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No documents provided or documents are empty.',
            ];
        }

        echo json_encode($response);
        exit;
    }
}

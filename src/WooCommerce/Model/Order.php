<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Dto\RateDto;
use HGeS\Rate;
use HGeS\Utils\Messages;
use HGeS\Utils\RateHelper;

/**
 * This class exposes methods to interact with the WooCommerce orders
 */
class Order
{
    /**
     * The pickup point meta key used in database
     */
    public const PICKUP_POINT_META_KEY = 'hges_pickup_point';

    /**
     * The attachments meta key used in database
     */
    public const ATTACHMENTS_META_KEY = 'hges_attachments';

    /**
     * Initialize the order hooks and filters
     */
    public static function init(): void
    {
        add_action('woocommerce_checkout_create_order', [self::class, 'setOrderPickupMeta'], 10, 2);
        add_action('woocommerce_order_edit_status', [self::class, 'checkShippingBeforeStatusUpdate'], 10, 2);
        add_action('woocommerce_order_edit_status', [self::class, 'checkAttachmentsBeforeStatusUpdate'], 10, 2);
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

    public static function updateSelectedShippingRate(
        int $orderId,
        int $orderShippingItemId,
        string $newShippingRateChecksum
    ): ?RateDto {
        if (!$orderId || !$orderShippingItemId || !$newShippingRateChecksum) {
            throw new \Exception("Invalid order ID, shipping item ID, or shipping rate checksum.");
        }

        $order = wc_get_order($orderId);
        if (!$order) {
            return null;
        }

        $item = $order->get_item($orderShippingItemId);

        $formerShippingRateChecksum = self::getShippingRateChecksum($orderId);
        try {
            $formerShippingRate = Rate::getByChecksum($formerShippingRateChecksum);
        } catch (\Exception $e) {
            $formerShippingRate = null;
        }

        $rate = Rate::getByChecksum($newShippingRateChecksum);
        if (!$rate) {
            throw new \Exception("Order item or shipping rate not found.");
        }
        $item->set_props([
            "name" => $rate->getServiceName(),
            "total" => RateHelper::calculateTotal($rate),
            "total_tax" => "0",
            "taxes" => ["total" => []],
            "tax_status" => "taxable",
        ]);


        $metaData = [
            "checksum" => $newShippingRateChecksum,
            "method_title" => $rate->getServiceName(),
            "method_id" => ShippingMethod::METHOD_ID,
        ];

        $customerSelectedRateExists = $item->meta_exists('customer_selected_rate');
        if (!$customerSelectedRateExists) {
            $metaData['customer_selected_rate'] = $formerShippingRate;
        }

        foreach ($metaData as $key => $value) {
            $item->update_meta_data($key, $value);
        }
        $item->get_data_store()->update($item);
        $item->apply_changes($item);
        $item->save();

        $order->calculate_totals();
        return $rate;
    }

    /**
     * Check if the shipping method is still available before updating the order status.
     * triggered by the 'woocommerce_order_edit_status' action.
     *
     * @param int $orderId The ID of the order.
     * @param string $newStatus The new status to which the order is being updated.
     * @return bool True if the shipping method is still available, false otherwise.
     */
    public static function checkShippingBeforeStatusUpdate(int $orderId, string $newStatus): bool
    {
        if ($newStatus !== 'processing' && $newStatus !== 'completed') {
            return true;
        }
        $shippingRateChecksum = self::getShippingRateChecksum($orderId);
        $shippingMethodStillAvailable = Rate::isStillAvailable($shippingRateChecksum);
        if (!$shippingMethodStillAvailable) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin')['shippingRateNotAvailable']);
            $url = add_query_arg('error', Messages::getMessage('orderAdmin')['shippingRateNotAvailable'], wp_get_referer());
            wp_redirect($url);
            exit;
        }
        return $shippingMethodStillAvailable;
    }

    /**
     * Check if the required attachments are present before updating the order status.
     * triggered by the 'woocommerce_order_edit_status' action.
     *
     * @param int $orderId The ID of the order.
     * @param string $newStatus The new status to which the order is being updated.
     * @return bool True if all required attachments are present, false otherwise.
     */
    public static function checkAttachmentsBeforeStatusUpdate(int $orderId, string $newStatus): bool
    {
        if ($newStatus !== 'processing' && $newStatus !== 'completed') {
            return true;
        }

        $currentShippingRate = Rate::getByChecksum(self::getShippingRateChecksum($orderId));

        if (!$currentShippingRate) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin')['shippingRateNotAvailable']);
            $url = add_query_arg('error', Messages::getMessage('orderAdmin')['shippingRateNotAvailable'], wp_get_referer());
            wp_redirect($url);
            exit;
        }

        $attachmentsRequired = $currentShippingRate['requiredAttachments'] ?? false;
        if (empty($attachmentsRequired)) {
            return true;
        }

        $attachments = self::getAttachmentList($orderId);
        $missingAttachments = array_filter($currentShippingRate['requiredAttachments'] ?? [], function ($requiredAttachment) use ($attachments) {
            $requiredAttachmentType = $requiredAttachment['type'] ?? '';
            return !in_array($requiredAttachmentType, array_column($attachments, 'type'));
        });

        if (count($missingAttachments)) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin')['attachmentsMissing']);
            $url = add_query_arg('error', Messages::getMessage('orderAdmin')['attachmentsMissing'], wp_get_referer());
            wp_redirect($url);
            exit;
        }

        return true;
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

    public static function getInitialSelectedRate($orderId): ?RateDto
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return null;
        }
        $item = array_pop($order->get_items('shipping'));

        if (!$item
            || get_class($item) !== 'WC_Order_Item_Shipping'
            || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID
        ) {
            return null;
        }

        $shippingRateChecksumMeta = array_find($item->get_meta_data(), function (\WC_Meta_Data $meta) {
            return $meta->key === 'customer_selected_rate';
        });

        return $shippingRateChecksumMeta ? $shippingRateChecksumMeta->value : null;
    }

    /**
     * Update the attachments for a specific order.
     *
     * @param array $data The POST body containing the order ID and attachments.
     */
    public static function updateAttachments(array $data): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $orderId = isset($data['orderId']) ? intval($data['orderId']) : 0;
        $attachments = isset($data['attachments']) ? $data['attachments'] : null;
        $order = wc_get_order($orderId);
        if (!$order || !$attachments) {
            wp_send_json_error(['message' => 'Invalid order ID or attachments data.'], 400);
            return;
        }

        if (!empty($attachments)) {
            $order->update_meta_data(Order::ATTACHMENTS_META_KEY, $attachments);
            $order->save_meta_data();
            $response = [
                'success' => true,
                'message' => 'Attachments updated successfully.',
                'attachments' => $attachments,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No attachments provided or attachments are empty.',
            ];
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Get the list of attachments for a specific order in JSON format.
     */
    public static function getAttachmentListJson(): array
    {
        $orderId = isset($_GET['orderId']) ? intval($_GET['orderId']) : 0;
        if (!$orderId) {
            wp_send_json_error(['message' => 'Invalid order ID.'], 400);
            return [];
        }
        $attachments = self::getAttachmentList($orderId);
        if (empty($attachments)) {
            wp_send_json_error(['message' => 'No attachments found for this order.'], 404);
            return [];
        }

        echo json_encode($attachments);
        exit;
    }

    /**
     * Get the list of attachments for a specific order.
     */
    public static function getAttachmentList(int $orderId): array
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return [];
        }

        $attachments = $order->get_meta(self::ATTACHMENTS_META_KEY, true);
        if (empty($attachments)) {
            return [];
        }

        return $attachments;
    }
}

<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Dto\PackageDto;
use HGeS\Dto\RateDto;
use HGeS\Rate;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Enums\ProductMetaEnum;
use HGeS\Utils\Messages;
use HGeS\Utils\RateHelper;
use HGeS\Utils\Packaging;
use HGeS\WooCommerce\Address;
use HgeS\WooCommerce\ShippingAddressFields;

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
     * The packaging meta key used in database
     */
    public const PACKAGING_META_KEY = 'hges_packaging';

    /**
     * The key used to store the shipping rate checksum in the order meta
     */
    public const CONSUMER_SELECTED_RATE_META_KEY = 'customer_selected_rate';

    /**
     * Constant representing the key used to store or retrieve the shipment ID.
     */
    public const SHIPMENT_ID = 'hges_shipment_id';

    /**
     * Constant representing the shipment label URL.
     */
    public const SHIPMENT_LABEL_URL = 'hges_shipment_label_url';

    /**
     * Constant representing the meta key for the shipping address index in an order.
     */
    public const ORDER_SHIPPING_ADDRESS_INDEX = '_shipping_address_index';

    /**
     * Initialize the order hooks and filters
     */
    public static function init(): void
    {
        add_action('woocommerce_checkout_create_order', [self::class, 'setOrderPickupMeta'], 10, 2);
        add_action('woocommerce_order_edit_status', [self::class, 'checkShippingBeforeStatusUpdate'], 10, 2);
        add_action('woocommerce_order_edit_status', [self::class, 'checkAttachmentsBeforeStatusUpdate'], 10, 2);
        add_action('woocommerce_thankyou', [self::class, 'setInitialPackagingData'], 10);
    }

    /**
     * Set the initial packaging data for the order.
     * This method is called during the thank you page rendering,
     * triggered by the 'woocommerce_thankyou' action.
     *
     * @return void
     */
    public static function setInitialPackagingData(int $orderId): void
    {
        if ($orderId) {
            $order = wc_get_order($orderId);
        }

        if (!$order) {
            throw new \Exception("Order not found.");
        }

        $shippingItem = self::getShippingOrderItem($orderId);
        if (!$shippingItem) {
            throw new \Exception("Shipping item not found for order ID: $orderId.");
        }

        $shippingRateChecksum = self::getShippingRateChecksum($orderId);
        if ($shippingRateChecksum) {
            try {
                $shippingRate = Rate::getByChecksum($shippingRateChecksum);
            } catch (\Exception $e) {
                $shippingRate = null;
            }
        } else {
            throw new \Exception("Shipping rate checksum not found for order ID: $orderId.");
        }

        if ($shippingRate && !empty($shippingRate->getPackages())) {
            $packaging = $shippingRate->getPackages();
            $packaging = Packaging::applyWeight($orderId, $packaging);

            $order->update_meta_data(self::PACKAGING_META_KEY, $packaging);
            $order->save_meta_data();
        } else {
            throw new \Exception("Shipping rate or packages not found for order ID: $orderId.");
        }
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

        // foreach ($pickupPoint as $key => $value) {
        //     $order->update_meta_data('_hges_pickup_point_' . sanitize_key($key), $value);
        // }
        $order->update_meta_data(self::PICKUP_POINT_META_KEY, $pickupPoint);
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
            throw new \Exception("Order not found.");
        }

        $item = $order->get_item($orderShippingItemId);

        $formerShippingRateChecksum = self::getShippingRateChecksum($orderId);
        try {
            $formerShippingRate = Rate::getByChecksum($formerShippingRateChecksum);
        } catch (\Throwable $e) {
            error_log("Error retrieving former shipping rate: " . $e->getMessage());
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

        $customerSelectedRateExists = $item->meta_exists(self::CONSUMER_SELECTED_RATE_META_KEY);
        if (!$customerSelectedRateExists && $formerShippingRate) {
            $metaData[self::CONSUMER_SELECTED_RATE_META_KEY] = $formerShippingRate->toArray();
        }

        foreach ($metaData as $key => $value) {
            $item->get_data_store()->set_prop($key, $value);
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
        $shippingRateChecksum = self::getShippingRateChecksum($orderId);
        $shippingMethodStillAvailable = Rate::isStillAvailable($shippingRateChecksum);
        if (!$shippingMethodStillAvailable) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin.shippingRateNotAvailable'));
            $url = add_query_arg('error', Messages::getMessage('orderAdmin.shippingRateNotAvailable'), wp_get_referer());
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
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin.shippingRateNotAvailable'));
            $url = add_query_arg('error', Messages::getMessage('orderAdmin.shippingRateNotAvailable'), wp_get_referer());
            wp_redirect($url);
            exit;
        }

        $attachmentsRequired = $currentShippingRate->getRequiredAttachments() ?? false;
        if (empty($attachmentsRequired)) {
            return true;
        }

        $attachments = self::getAttachmentList($orderId);
        $missingAttachments = array_filter($currentShippingRate->getRequiredAttachments() ?? [], function ($requiredAttachment) use ($attachments) {
            $requiredAttachmentType = $requiredAttachment['type'] ?? '';
            return !in_array($requiredAttachmentType, array_column($attachments, 'type'));
        });

        if (count($missingAttachments)) {
            \WC_Admin_Meta_Boxes::add_error(Messages::getMessage('orderAdmin.attachmentsMissing'));
            $url = add_query_arg('error', Messages::getMessage('orderAdmin.attachmentsMissing'), wp_get_referer());
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

        if (
            !$item
            || get_class($item) !== 'WC_Order_Item_Shipping'
            || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID
        ) {
            return null;
        }

        $shippingRateChecksumMeta = array_find($item->get_meta_data(), function (\WC_Meta_Data $meta) {
            return $meta->key === self::CONSUMER_SELECTED_RATE_META_KEY;
        });

        return $shippingRateChecksumMeta ? RateDto::fromArray($shippingRateChecksumMeta->value) : null;
    }

    public static function createShipment(int $orderId): array
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            throw new \Exception("Order not found.");
        }

        //get shipping address
        $shippingAddress = $order->get_address('shipping');
        $shippingRateChecksum = self::getShippingRateChecksum($orderId);

        $isCompany = $order->get_meta(ShippingAddressFields::SHIPPING_IS_COMPANY_METANAME);

        $destAddress = [
            "category" => $isCompany ? "company" : "individual",
            "firstname" => $shippingAddress['first_name'] ?? '',
            "lastname" => $shippingAddress['last_name'] ?? '',
            "email" => $order->get_billing_email() ?? '',
            "telephone" => $order->get_billing_phone() ?? '',
            "address" => $shippingAddress['address_1'] . ' ' . $shippingAddress['address_2'],
            "country" => $shippingAddress['country'],
            "zipCode" => $shippingAddress['postcode'],
            "city" => $shippingAddress['city'],
        ];

        if ($isCompany) {
            $destAddress['company'] = $order->get_meta(ShippingAddressFields::SHIPPING_COMPANY_NAME_METANAME);
        }

        $rate = Rate::getByChecksum($shippingRateChecksum);
        $prices = $rate->getPrices();

        $optionalPrices = [];
        foreach ($prices as $key => $price) {
            if ($key !== 'shippingPrice' && (!isset($price['required']) || $price['required'] === false)) {
                $optionalPrices[] = $price['key'];
            }
        }
        $params = [
            "checksum" => $rate->getChecksum(),
            "to" => $destAddress,
            "optionalPrices" => $optionalPrices
        ];

        // if fiscalRepresentation is set, add it to the params
        if (isset($prices['fiscalRepresentation'])) {
            $params['exciseDuties'] = "paid";
            $params['shipmentPurpose'] = "sale";
        }

        if (isset($prices['landedCost'])) {
            $params['incoterm'] = get_option(OptionEnum::HGES_TAX_RIGHTS);
            $params['exciseDuties'] = "paid";
            $params['shipmentPurpose'] = "sale";
        }

        $pickupPoint = $order->get_meta(self::PICKUP_POINT_META_KEY, true);

        if (is_array($pickupPoint) && !empty($pickupPoint)) {
            $params['pickupPoint'] = $pickupPoint;
        }

        try {
            error_log('Payload envoyé : ' . json_encode($params, JSON_PRETTY_PRINT));
            $response = ApiClient::post('/v2/shipments', $params);
            error_log('Réponse API : ' . json_encode($response, JSON_PRETTY_PRINT));

            if (isset($response['data']['shipment']) && is_array($response['data']['shipment'])) {
                error_log('Shipment created with ID: ' . $response['data']['shipment']['id']);

                return $response['data']['shipment'];
            } else {
                error_log('Erreur API createShipment: Réponse invalide');
                throw new \Exception("Invalid response from API when creating shipment.");
            }
        } catch (\Exception $e) {
            error_log('Erreur API createShipment: ' . $e->getMessage());
            error_log('Trace : ' . $e->getTraceAsString());
            throw new \Exception("Error creating shipment: " . $e->getMessage());
        }
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

    public static function updatePackaging(int $orderId, array $packaging): void
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            throw new \Exception("Order not found.");
        }

        $packaging = Packaging::applyWeight($orderId, $packaging);

        $order->update_meta_data(self::PACKAGING_META_KEY, $packaging);
        $order->save_meta_data();

        self::invalidateOrderShippingRate($orderId);
    }

    /**
     * Invalidate the shipping rate for a specific order.
     * This method clears the shipping rate checksum from the order's shipping item.
     * 
     * @param int $orderId The ID of the order to invalidate the shipping rate for.
     * @return void
     */
    public static function invalidateOrderShippingRate(int $orderId): void
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            throw new \Exception("Order not found.");
        }

        $shippingItems = $order->get_items('shipping');
        if (empty($shippingItems)) {
            throw new \Exception("No shipping items found for this order.");
        }

        $item = array_find($shippingItems, function ($shippingItem) {
            return get_class($shippingItem) === 'WC_Order_Item_Shipping'
                && $shippingItem->get_data()['method_id'] === ShippingMethod::METHOD_ID;
        });

        if (!$item) {
            throw new \Exception("Shipping item not found.");
        }

        $item->update_meta_data('checksum', '');
        $item->save_meta_data();
    }

    /**
     * Get the shipping order item for a specific order.
     * 
     * @param int $orderId The ID of the order to retrieve the shipping item for.
     * @return WC_Order_Item_Shipping|null The shipping order item if found, otherwise null.
     */
    public static function getShippingOrderItem(int $orderId): ?\WC_Order_Item_Shipping
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            return null;
        }

        $shippingItems = $order->get_items('shipping');
        if (empty($shippingItems)) {
            return null;
        }

        $item = array_find($shippingItems, function ($shippingItem) {
            return get_class($shippingItem) === 'WC_Order_Item_Shipping'
                && $shippingItem->get_data()['method_id'] === ShippingMethod::METHOD_ID;
        });

        if (!$item) {
            return null;
        }

        if (
            get_class($item) !== 'WC_Order_Item_Shipping'
            || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID
        ) {
            return null;
        }

        return $item;
    }

    public static function hasShipment(int $orderId): bool
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            throw new \Exception("Order not found.");
        }

        $shipmentId = $order->get_meta(self::SHIPMENT_ID, true);

        return !empty($shipmentId);
    }

    public static function checkBeforeValidate(int $orderId): bool
    {
        $order = wc_get_order($orderId);
        if (!$order) {
            throw new \Exception("Order not found.");
        }

        $shippingAddressOrder = $order->get_address('shipping');
        $shippingAddressMeta = $order->get_meta(self::ORDER_SHIPPING_ADDRESS_INDEX, true);

        $fieldsToCheck = [
            "first_name",
            "last_name",
            "address_1",
            "address_2",
            "city",
            "state",
            "postcode",
            "country",
            "phone"
        ];

        $normalizedAddressOrder = [];
        foreach ($fieldsToCheck as $field) {
            if (!empty($shippingAddressOrder[$field])) {
                $normalizedAddressOrder[] = $shippingAddressOrder[$field];
            }
        }
        $stringFromAddressOrder = implode(' ', $normalizedAddressOrder);

        function normalize($string)
        {
            return trim(preg_replace('/\s+/', ' ', $string));
        }

        $stringFromAddressOrder = normalize($stringFromAddressOrder);
        $stringFromAddressMeta = normalize($shippingAddressMeta);

        if ($stringFromAddressOrder === $stringFromAddressMeta) {
            $shippingAddressMatch = true;
        } else {
            $shippingAddressMatch = false;
        }

        $shippingRateChecksum = self::getShippingRateChecksum($orderId);
        $packaging = $order->get_meta(self::PACKAGING_META_KEY, true);

        return !empty($shippingAddressOrder) && !empty($shippingAddressMeta) && !empty($shippingAddressMatch) && !empty($shippingRateChecksum) && !empty($packaging);
    }
}

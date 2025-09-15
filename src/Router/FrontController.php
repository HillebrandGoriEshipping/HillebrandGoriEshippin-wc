<?php

namespace HGeS\Router;

use HGeS\Rate;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Packaging;
use HGeS\WooCommerce\Model\Order;

class FrontController
{
    /**
     * Get the pickup points
     *
     * @param array $data
     * @return void
     */
    public static function getPickupPoints($data): void
    {
        $urlParams = array_map(function ($param) {
            return htmlspecialchars(strip_tags($param));
        }, $_GET);

        $pickupPointsRequest = ApiClient::get(
            '/relay/get-access-points',
            $urlParams,
        );

        if ($pickupPointsRequest['status'] !== 200) {
            self::renderJson([
                'error' => 'Unable to fetch pickup points',
            ], $pickupPointsRequest['status']);
            return;
        }

        self::renderJson([
            'success' => true,
            'data' => $pickupPointsRequest['data'],
        ]);
    }

    /**
     * Update the given order selected pickup point
     *
     * @param array $data
     * @return void
     */
    public static function setCurrentPickupPoint(array $data): void
    {
        $response = [];
        $bodyParams = $data;
        $urlParams = array_map(function ($param) {
            return htmlspecialchars(strip_tags($param));
        }, $_GET);
        if (!empty($bodyParams['pickupPoint']) && !empty($urlParams['orderId'])) {
            Order::setPickupPoint($urlParams['orderId'], $bodyParams['pickupPoint']);
            $response['success'] = true;
        } else {
            $response['error'] = 'Unable to update pickup point, orderId is expected in the query params and pickupPoint json object is expected in the json body.';
            self::renderJson($response, 400);
        }

        self::renderJson($response);
    }

    /**
     * Retrieve the shipping rates for the given order
     *
     * @param array $postData
     * @return void
     */
    public static function getShippingRatesForOrder(array $postData): void
    {
        $orderId = filter_input(INPUT_GET, 'orderId', FILTER_VALIDATE_INT);

        if (!empty($orderId)) {
            $order = wc_get_order($orderId);
            $rates = Rate::getShippingRates([
                'destination' => [
                    'city' => $order->get_shipping_city(),
                    'postcode' => $order->get_shipping_postcode(),
                    'country' => $order->get_shipping_country(),
                ],
                'contents' => $order->get_items(),
            ]);

            self::renderJson([
                'success' => true,
                'shippingRates' => $rates
            ]);
        } else {
            self::renderJson([
                'error' => 'Unable to retrieve shipping rates, orderId is expected in the query params.',
            ], 400);
        }
    }

    public static function setOrderShippingRate(array $data): void
    {
        self::checkUserCanEditOrders();
        $response = [];
        $bodyParams = $data;
        $urlParams = array_map(function ($param) {
            return htmlspecialchars(strip_tags($param));
        }, $_GET);

        if (!empty($bodyParams['shippingRateChecksum']) && !empty($urlParams['orderId']) && !empty($urlParams['orderShippingItemId'])) {

            try {
                $rate = Order::updateSelectedShippingRate(
                    intval($urlParams['orderId']),
                    intval($urlParams['orderShippingItemId']),
                    $bodyParams['shippingRateChecksum']
                );
            } catch (\Exception $e) {
                $response['error'] = 'Unable to update shipping method: ' . $e->getMessage();
                self::renderJson($response, 400);
                return;
            }

            $response['success'] = true;
            $response['shippingRate'] = $rate->toArray();
        } else {
            $response['error'] = 'Unable to update shipping method, orderId and orderShippingItemId are expected in the query params and shippingRateChecksum json object is expected in the json body.';

            self::renderJson($response, 400);
            return;
        }
        self::renderJson($response);
    }

    public static function getPackagingPossibilities($data): void
    {
        self::checkUserCanEditOrders();
        $response = [];
        $urlParams = array_map(function ($param) {
            return htmlspecialchars(strip_tags($param));
        }, $_GET);

        if (!empty($urlParams['orderId'])) {
            $order = wc_get_order($urlParams['orderId'] ?? 0);
            if (!$order) {
                Router::errorNotFound();
                return;
            }

            $products = $order->get_items();

            if (is_array($products)) {
                $packagingPossibilities = Packaging::calculatePackagingPossibilities($products);
                $response['success'] = true;
                $response['packagingPossibilities'] = $packagingPossibilities;
            } else {
                $response['error'] = 'Invalid products data format.';
                self::renderJson($response, 400);
            }
        } else {
            $response['error'] = 'Products data is required.';
            self::renderJson($response, 400);
        }

        self::renderJson($response);
    }

    public static function renderJson(array $data, int $httpStatus = 200): void
    {

        http_response_code($httpStatus);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function checkUserCanEditOrders(): void
    {
        if (!current_user_can('edit_shop_orders')) {
            self::renderJson(['error' => 'Forbidden'], 403);
            return;
        }
    }

    /**
     * Get the available packaging options (as set by the admin in the settings)
     *
     * @param array $data
     * @return void
     */
    public static function getPackagingOptions(array $data): void
    {
        self::checkUserCanEditOrders();
        $availablePackagings = Packaging::getAvailablePackagingOptions();
        $flatAvailablePackagings = [];
        foreach ($availablePackagings as $packagings) {
            $flatAvailablePackagings = array_merge($flatAvailablePackagings, $packagings);
        }
        self::renderJson([
            'success' => true,
            'packagings' => $flatAvailablePackagings,
        ]);
    }

    /**
     * Set the packaging for the given order
     * 
     * @param array $data
     * @return void
     * 
     * @throws \Exception If the order is not found or if the packaging is invalid
     */
    public static function setPackagingForOrder(array $data): void
    {
        self::checkUserCanEditOrders();
        $response = [];
        $bodyParams = $data;

        if (!empty($bodyParams['packaging']) && !empty($bodyParams['orderId'])) {
            try {
                Order::updatePackaging($bodyParams['orderId'], json_decode($bodyParams['packaging'], true));
            } catch (\Exception $e) {
                $response['error'] = 'Unable to set packaging: ' . $e->getMessage();
                self::renderJson($response, 500);
                return;
            }
            $response['success'] = true;
        } else {
            $response['error'] = 'Unable to set packaging, orderId is expected in the query params and packaging json object is expected in the json body.';
            self::renderJson($response, 400);
        }

        self::renderJson($response);
    }

    /**
     * Creates a shipment for a given WooCommerce order.
     *
     * Retrieves the order ID from the GET parameters, validates it, and attempts to create a shipment. If successful, it updates the order's meta data with the shipment ID and label URL.
     *
     * @return void Outputs JSON response and updates order meta data.
     */
    public static function createShipment(): void
    {
        $orderId = intval($_GET['orderId'] ?? 0);
        $order = wc_get_order($orderId);
        if (!$orderId) {
            self::renderJson(['error' => 'orderId is required'], 400);
            return;
        }
        if (Order::checkBeforeValidate($orderId) === false) {
            self::renderJson(['error' => 'Data is missing or invalid'], 400);
            return;
        }
        try {
            $shipment = Order::createShipment($orderId);

            if ($order) {
                $order->add_meta_data(Order::SHIPMENT_ID_META_KEY, $shipment['id']);
                $order->add_meta_data(Order::SHIPMENT_LABEL_URL_META_KEY, $shipment['label']['directLink']);
                $order->save_meta_data();
            }

            $order->add_order_note(__('Shipment created with ID ', 'hges') . $shipment['id']);
            $order->save();

            self::renderJson([
                'success' => true,
                'message' => "Shipment for order #$orderId validated",
                'shipment' => $shipment
            ]);
        } catch (\Exception $e) {
            self::renderJson(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Checks if a WooCommerce order has an associated shipment ID.
     *
     * @return void Outputs a JSON response with the result or error.
     */
    public static function checkIfHasShipment(): void
    {
        $orderId = intval($_GET['orderId'] ?? 0);

        if (!$orderId) {
            self::renderJson(['error' => 'orderId is required'], 400);
            return;
        }

        $order = wc_get_order($orderId);
        if (!$order) {
            self::renderJson(['error' => 'Invalid order'], 404);
            return;
        }

        $shipmentId = Order::hasShipment($orderId);

        self::renderJson([
            'success' => true,
            'hasShipment' => $shipmentId,
        ]);
    }
}

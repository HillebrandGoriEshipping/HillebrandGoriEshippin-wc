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
            ]);
            http_response_code($pickupPointsRequest['status']);
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
            http_response_code(200);
        } else {
            $response['error'] = 'Unable to update pickup point, orderId is expected in the query params and pickupPoint json object is expected in the json body.';
            http_response_code(400);
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
                    'city' => $order->shipping_city,
                    'postcode' => $order->shipping_postcode,
                    'country' => $order->shipping_country,
                ],
                'contents' => $order->get_items(),
            ]);
           
            self::renderJson([
                'success' => true,
                'shippingRates' => $rates
            ]);
        } else {
            http_response_code(400);
            self::renderJson([
                'error' => 'Unable to retrieve shipping rates, orderId is expected in the query params.',
            ]);
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
            $rate = Order::updateSelectedShippingRate(
                intval($urlParams['orderId']),
                intval($urlParams['orderShippingItemId']),
                $bodyParams['shippingRateChecksum']
            );
            $response['success'] = true;
            $response['shippingRate'] = $rate->toArray();
        } else {
            $response['error'] = 'Unable to update shipping method, orderId and orderShippingItemId are expected in the query params and shippingRateChecksum json object is expected in the json body.';
            
            http_response_code(400);
            self::renderJson($response);
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
                http_response_code(400);
            }
        } else {
            $response['error'] = 'Products data is required.';
            http_response_code(400);
        }

        self::renderJson($response);
    }

    public static function renderJson($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function checkUserCanEditOrders(): void
    {
        if (!current_user_can( 'edit_shop_orders' )) {
            http_response_code(403);
            self::renderJson(['error' => 'Forbidden']);
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
}

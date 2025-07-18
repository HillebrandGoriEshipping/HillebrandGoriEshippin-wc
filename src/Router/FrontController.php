<?php

namespace HGeS\Router;

use HGeS\Rate;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Packaging;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Model\Order;

class FrontController
{
    /**
     * Get the pickup points
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function getPickupPoints(\WP_REST_Request $request): \WP_REST_Response
    {
        $urlParams = $request->get_query_params();
        $pickupPointsRequest = ApiClient::get(
            '/relay/get-chronopost-relay-points',
            $urlParams,
        );
        $response = new \WP_REST_Response();

        if ($pickupPointsRequest['status'] !== 200) {
            $response->set_data([
                'error' => 'Unable to fetch pickup points',
            ]);
            $response->set_status($pickupPointsRequest['status']);
            return $response;
        }

        $response->set_data($pickupPointsRequest['data']);

        $response->set_status(200);
        return $response;
    }

    /**
     * Update the given order selected pickup point
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function setCurrentPickupPoint(\WP_REST_Request $request): \WP_REST_Response
    { 
        $response = new \WP_REST_Response();

        $bodyParams = $request->get_json_params();
        $queryParams = $request->get_query_params();
        if (!empty($bodyParams['pickupPoint']) && !empty($queryParams['orderId'])) {
            Order::setPickupPoint($queryParams['orderId'], $bodyParams['pickupPoint']);
            $response->set_data([
                'success' => true
            ]);
            $response->set_status(200);
        } else {
            $response->set_data([
                'error' => 'Unable to update pickup point, orderId is expected in the query params and pickupPoint json object is expected in the json body.',
            ]);
            $response->set_status(400);
        }
        return $response;
    }

    /**
     * Retrieve the shipping rates for the given order
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public static function getShippingRatesForOrderHtml(array $postData): void
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
           

            $html = '';
            foreach ($rates as $rate) {
                $metadata = $rate['meta_data'];
            
                if (empty($metadata['carrier'])) {
                    $imagePath = null;
                } else {
                    $imagePath = HGES_PLUGIN_URL . 'assets/img/' . $metadata['carrier'] . '.png';
                }

                if ($rate['label'] === 'Aérien') {
                    $imagePath = HGES_PLUGIN_URL . 'assets/img/airfreight.png';
                } else if ($rate['label'] === 'Maritime') {
                    $imagePath = HGES_PLUGIN_URL . 'assets/img/seafreight.png';
                }
                $html .= Twig::getTwig()->render('shipping-label.twig', [
                    'rate' => $rate,
                    'metaData' => $rate['meta_data'] ?? [],
                    'label' => $rate['label'] ?? '',
                    'cost' => $rate['cost'] ?? 0,
                    'imagePath' => $imagePath,
                ]);
            }

            self::renderJson([
                'success' => true,
                'shippingRatesHtml' => $html
            ]);
        } else {
            http_response_code(400);
            self::renderJson([
                'error' => 'Unable to retrieve shipping rates, orderId is expected in the query params.',
            ]);
        }
    }

    public static function setOrderShippingRate(\WP_REST_Request $request): \WP_REST_Response
    {
        $response = new \WP_REST_Response();
        $bodyParams = $request->get_json_params();
        $queryParams = $request->get_query_params();

        if (!empty($bodyParams['shippingRateChecksum']) && !empty($queryParams['orderId']) && !empty($queryParams['orderShippingItemId'])) {
            $rate = Order::updateSelectedShippingRate(
                intval($queryParams['orderId']),
                intval($queryParams['orderShippingItemId']),
                $bodyParams['shippingRateChecksum']
            );
            $response->set_data([
                'success' => true,
                'shippingRate' => $rate
            ]);
            $response->set_status(200);
        } else {
            $response->set_data([
                'error' => 'Unable to update shipping method, orderId and orderShippingItemId are expected in the query params and shippingRateChecksum json object is expected in the json body.',
            ]);
            $response->set_status(400);
        }
        return $response;
    }

    public static function getPackagingPossibilities(\WP_Rest_Request $request): \WP_REST_Response
    {
        $response = new \WP_REST_Response();
        $queryParams = $request->get_query_params();
        if (!empty($queryParams['orderId'])) {
            $order = wc_get_order($queryParams['orderId'] ?? 0);
            if (!$order) {
                $response->set_data([
                    'error' => 'Order not found.',
                ]);
                $response->set_status(404);
                return $response;
            }

            $products = $order->get_items();

            if (is_array($products)) {
                $packagingPossibilities = Packaging::calculatePackagingPossibilities($products);
                $response->set_data([
                    'success' => true,
                    'packagingPossibilities' => $packagingPossibilities
                ]);
                $response->set_status(200);
            } else {
                $response->set_data([
                    'error' => 'Invalid products data format.',
                ]);
                $response->set_status(400);
            }
        } else {
            $response->set_data([
                'error' => 'Products data is required.',
            ]);
            $response->set_status(400);
        }

        return $response;
    }

    public static function renderJson($data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
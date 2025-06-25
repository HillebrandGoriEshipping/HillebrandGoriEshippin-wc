<?php

namespace HGeS\Api;

use HGeS\Rate;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Model\Order;

class CustomEndpoints
{

    const NAMESPACE  = 'hges/v1';

    /**
     * Initialize the custom endpoints
     */
    public static function init(): void
    {
        add_action('rest_api_init', [self::class, 'register']);
    }

    /**
     * Register the custom endpoints
     *
     * @return void
     */
    public static function register(): void
    {
        register_rest_route(self::NAMESPACE, '/pickup-points', [
            'methods' => 'GET',
            'callback' => [self::class, 'getPickupPoints'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/order/set-current-pickup-point', [
            'methods' => 'POST',
            'callback' => [self::class, 'setCurrentPickupPoint'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/shipping-rates', [
            'methods' => 'GET',
            'callback' => [self::class, 'getShippingRatesForOrderHtml'],
            'permission_callback' => '__return_true',
        ]);
    }

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
    public static function getShippingRatesForOrderHtml(\WP_REST_Request $request): \WP_REST_Response
    {
        $response = new \WP_REST_Response();
        $queryParams = $request->get_query_params();
        if (!empty($queryParams['orderId'])) {
            $order = wc_get_order($queryParams['orderId']);
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

            $response->set_data([
                'success' => true,
                'shippingRatesHtml' => $html
            ]);
            $response->set_status(200);
        } else {
            $response->set_data([
                'error' => 'Unable to retrieve shipping rates, orderId is expected in the query params.',
            ]);
            $response->set_status(400);
        }
        return $response;
    }
}
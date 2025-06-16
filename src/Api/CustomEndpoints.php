<?php

namespace HGeS\Api;

use HGeS\Utils\ApiClient;
use HGeS\WooCommerce\Order;

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
}

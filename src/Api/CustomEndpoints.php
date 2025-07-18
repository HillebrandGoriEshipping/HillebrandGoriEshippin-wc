<?php

namespace HGeS\Api;

use HGeS\Rate;
use HGeS\Utils\ApiClient;
use HGeS\Utils\Packaging;
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

        register_rest_route(self::NAMESPACE, '/order/set-shipping-rate', [
            'methods' => 'PATCH',
            'callback' => [self::class, 'setOrderShippingRate'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route(self::NAMESPACE, '/packaging-possibilities', [
            'methods' => 'GET',
            'callback' => [self::class, 'getPackagingPossibilities'],
            'permission_callback' => '__return_true',
        ]);
    }

    

    

    

    


    
}
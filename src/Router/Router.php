<?php

namespace HGeS\Router;

use HGeS\Admin\Settings\SettingsController;
use HGeS\WooCommerce\Model\Order;

/**
 * Class Router
 *
 * This class is responsible for handling plugin specific actions and routing.
 */
class Router
{

    private $ajaxRoutes;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->ajaxRoutes = [
            'hges_get_pickup_points' => new Route(
                'GET',
                FrontController::class,
                'getPickupPoints'
            ),
            'hges_set_current_pickup_point' => new Route(
                'POST',
                FrontController::class,
                'setCurrentPickupPoint'
            ),
            'hges_get_shipping_rates_for_order' => new Route(
                'GET',
                FrontController::class,
                'getShippingRatesForOrder'
            ),
            'hges_set_order_shipping_rate' => new Route(
                'POST',
                FrontController::class,
                'setOrderShippingRate',
                true
            ),
            'hges_get_packaging_possibilities' => new Route(
                'GET',
                FrontController::class,
                'getPackagingPossibilities',
                true
            ),
            'hges_update_order_attachments' => new Route(
                'POST',
                Order::class,
                'updateAttachments',
                true
            ),
            'hges_get_packaging_options' => new Route(
                'GET',
                FrontController::class,
                'getPackagingOptions',
                true
            ),
            'hges_set_packaging_for_order' => new Route(
                'POST',
                FrontController::class,
                'setPackagingForOrder',
                true
            ),
            'hges_create_shipment' => new Route(
                'POST',
                FrontController::class,
                'createShipment',
                true
            ),
            'hges_check_if_has_shipment' => new Route(
                'POST',
                FrontController::class,
                'checkIfHasShipment',
                true
            ),
        ];
    }

    /**
     * Initialize the router for plugin specific actions
     */
    public function init(): void
    {
        foreach ($this->ajaxRoutes as $action => $route) {
            $baseActionString = 'wp_ajax_';
            add_action($baseActionString . $action, [$this, 'ajaxRouter']);
        }

        $openRoutes = array_filter($this->ajaxRoutes, function ($route) {
            return !$route->isAdmin();
        });
        foreach ($openRoutes as $action => $route) {
            $baseActionString = 'wp_ajax_nopriv_';
            add_action($baseActionString . $action, [$this, 'ajaxRouter']);
        }
    }

    /**
     * Initialize the admin router
     */
    public function initAdmin(): void
    {
        add_action('admin_init', [$this, 'router']);
    }

    /**
     * Router for plugin specific actions
     *
     * @return void
     */
    public function router(): void
    {
        // Only proceed if in admin area
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }
        
        if (
            isset($_GET['page'])
            && $_GET['page'] === 'hillebrand-gori-eshipping'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            SettingsController::saveSettings();
            exit;
        }

        if (
            isset($_GET['action'])
            && $_GET['action'] === 'hges_save_api_key'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            SettingsController::saveApiKey();
            exit;
        }

        if (
            isset($_GET['action'])
            && $_GET['action'] === 'hges_save_favorite_address'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            SettingsController::saveFavoriteAddress();
            exit;
        }
    }

    public function ajaxRouter(): void
    {
        if (
            !isset($_GET['action'])
            || !isset($this->ajaxRoutes[$_GET['action']])
            || $this->ajaxRoutes[$_GET['action']] instanceof Route === false
            || $this->ajaxRoutes[$_GET['action']]->getHttpMethod() !== $_SERVER['REQUEST_METHOD']
        ) {
            self::errorNotFound();
        }

        $currentRoute = $this->ajaxRoutes[$_GET['action']];

        $postData = [];
        if (in_array($currentRoute->getHttpMethod(), ['POST', 'PATCH', 'PUT'])) {
            wp_check_ajax_referer(GlobalEnum::NONCE_ACTION, 'nonce');
            $postData = json_decode(file_get_contents('php://input'), true);
        }

        $class = $currentRoute->getClass();
        $actionMethod = $currentRoute->getActionMethod();
        $class::$actionMethod($postData);
        exit;
    }

    /**
     * Handle 404 Not Found error
     */
    public static function errorNotFound(): void
    {
        wp_send_json_error(['message' => 'Not Found'], 404);
        exit;
    }
}

<?php

namespace HGeS;

use HGeS\Admin\Settings\SettingsController;
use HGeS\WooCommerce\Model\Order;

/**
 * Class Router
 *
 * This class is responsible for handling plugin specific actions and routing.
 */
class Router {
    
    public const AJAX_ACTIONS = ['hges_update_order_documents'];

    /**
     * Initialize the router for plugin specific actions
     */
    public static function initAdmin(): void
    {
        add_action('admin_init', [self::class, 'router']);
        foreach (self::AJAX_ACTIONS as $action) {
            add_action('wp_ajax_' . $action, [self::class, 'ajaxRouter']);
        }
    }

    /**
     * Router for plugin specific actions
     *
     * @return void
     */
    public static function router(): void
    {
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
    }

    public static function ajaxRouter(): void
    {
        if (isset($_GET['action']) && in_array($_GET['action'], self::AJAX_ACTIONS)) {
            switch ($_GET['action']) {
                case 'hges_update_order_documents':
                    Order::updateDocuments();
                    break;
                default:
                    wp_send_json_error(['message' => 'Invalid action']);
            }
        } else {
            wp_send_json_error(['message' => 'Invalid action']);
        }
    }
}

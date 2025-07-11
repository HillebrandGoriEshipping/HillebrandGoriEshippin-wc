<?php

namespace HGeS;

use HGeS\Admin\Settings\SettingsController;

/**
 * Class Router
 *
 * This class is responsible for handling plugin specific actions and routing.
 */
class Router {

    /**
     * Initialize the router for plugin specific actions
     */
    public static function initAdmin(): void
    {
        add_action('admin_init', [self::class, 'router']);
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

        if (
            isset($_GET['action'])
            && $_GET['action'] === 'hges_save_favorite_address'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            SettingsController::saveFavoriteAddress();
            exit;
        }
    }
}

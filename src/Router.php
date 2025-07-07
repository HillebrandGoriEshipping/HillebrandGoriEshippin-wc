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

        if (isset($_GET['action'])
            && $_GET['action'] === 'hges_upload_documents'
            &&  $_SERVER['REQUEST_METHOD'] === 'POST'
        ) {
            $baseDir = $_SERVER['DOCUMENT_ROOT'] . '/content/uploads/tmp/';
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }
            foreach($_FILES as $key => $file) {
                $filename = $baseDir . uniqid().$file['name'];
                $write = file_put_contents(
                    $filename,
                    file_get_contents($file['tmp_name'])
                );
                var_dump($write);
            }
            exit;
        }
    }
}

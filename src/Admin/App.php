<?php

namespace HGeS;

use HGeS\Admin\Settings\Menu;
use HGeS\Utils\ApiClient;

/**
 * Plugin entry class
 *
 */
class App
{

    public static function run()
    {
        var_dump(ApiClient::get('/package/get-sizes?nbBottles=1'));
        exit();
        if (is_admin()) {
            self::runAdmin();
        }

        
    }

    public static function runAdmin()
    {
        add_action('admin_menu', [Menu::class, 'addSettingsMenu']);

        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
    }

    public static function enqueueAdminAssets()
    {
        // Enqueue your admin scripts and styles here
        wp_enqueue_script_module('hges-admin-script', HGeS_PLUGIN_URL . 'assets/js/settingsPage.js');
    }
}

<?php

namespace HGeS;

use HGeS\Admin\Settings\Menu;

/**
 * Plugin entry class
 *
 */
class App
{

    public static function run()
    {
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
        wp_enqueue_style(
            'hges-admin-style',
            HGeS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            null
        );

        wp_enqueue_script_module(
            'hges-settings-page-script',
            HGeS_PLUGIN_URL . 'assets/js/settingsPage.js'
        );
    }
}

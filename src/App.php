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
    }
}

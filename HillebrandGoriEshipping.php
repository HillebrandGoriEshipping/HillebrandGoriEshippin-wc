<?php
/*
 * Plugin Name: Hillebrand Gori eShipping
 * Description: Hillebrand Gori eShipping plugin for WooCommerce
 * Version: 1.0.0
 * Author: Hillebrand Gori eSHipping
 * Author URI: https://eshipping.hillebrandgori.com
 * Requires Plugins: woocommerce
 * Requires PHP: 8.3
 * License: GPLv3
 * Text Domain: hges
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require __DIR__ . '/vendor/autoload.php';

use HGeS\App;

define('HGES_PLUGIN_DIR', __DIR__);
define('HGES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HGES_PLUGIN_CONFIG_PATH', HGES_PLUGIN_DIR . '/assets/js/config/config.json');
define('HGES_MESSAGES_JSON_PATH', HGES_PLUGIN_DIR . '/assets/js/config/messages.json');
$hgesApp = new App();
$hgesApp->run();


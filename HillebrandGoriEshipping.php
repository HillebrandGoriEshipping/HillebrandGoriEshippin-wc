<?php
/*
 * Plugin Name: Hillebrand Gori eShipping
 * Description: Hillebrand Gori eShipping plugin for WooCommerce
 * Version: 2.0.0
 * Author: Hillebrand Gori eSHipping
 * Author URI: https://eshipping.hillebrandgori.com
 * Requires Plugins: WooCommerce
 * Requires PHP: 8.3
 * License: GPLv3
*/
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require __DIR__ . '/vendor/autoload.php';

use HGeS\App;

App::run();

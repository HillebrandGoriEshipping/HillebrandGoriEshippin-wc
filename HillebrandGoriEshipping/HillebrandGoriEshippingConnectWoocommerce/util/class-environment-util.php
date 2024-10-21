<?php

/**
 * Contains code for environment util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Plugin;
use HillebrandGoriEshipping\HillebrandGoriEshippingPhp\RestClient;

/**
 * Environment util class.
 *
 * Helper to check environment.
 *
 * @class       Environment_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Environment_Util
{

	/**
	 * Get warning about PHP version, WC version.
	 *
	 * @param Plugin $plugin plugin object.
	 * @return string $message
	 */
	public static function check_errors($plugin)
	{
		if (false === RestClient::healthcheck()) {
			return __('Hillebrand Gori eShipping - You need either the curl extension or allow_url_fopen activated on your server for the Hillebrand Gori eShipping plugin to work.', 'HillebrandGoriEshipping');
		}

		if (version_compare(PHP_VERSION, $plugin['min-php-version'], '<')) {
			/* translators: 1) int version 2) int version */
			$message = __('Hillebrand Gori eShipping - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'HillebrandGoriEshipping');

			return sprintf($message, $plugin['min-php-version'], PHP_VERSION);
		}

		if (! defined('WC_VERSION')) {
			return __('Hillebrand Gori eShipping plugin requires WooCommerce to be activated to work.', 'HillebrandGoriEshipping');
		}

		if (version_compare(WC_VERSION, $plugin['min-wc-version'], '<')) {
			/* translators: 1) int version 2) int version */
			$message = __('Hillebrand Gori eShipping - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'HillebrandGoriEshipping');

			return sprintf($message, $plugin['min-wc-version'], WC_VERSION);
		}
		return false;
	}
}

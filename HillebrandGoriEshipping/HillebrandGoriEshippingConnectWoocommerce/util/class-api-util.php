<?php

/**
 * Contains code for api util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Plugin;

/**
 * Api util class.
 *
 * Helper to manage API responses.
 *
 * @class       Api_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Api_Util
{

	/**
	 * API request validation.
	 *
	 * @param integer $code http code.
	 * @param mixed   $body to send along response.
	 * @void
	 */
	public static function send_api_response($code, $body = null)
	{
		$HillebrandGoriEshipping_connect = Plugin::getInstance();
		header('X-Version: ' . $HillebrandGoriEshipping_connect['version']);

		http_response_code($code);
		if (null !== $body) {
			// phpcs:ignore
			echo Auth_Util::encrypt_body($body);
		}
		die();
	}
}

<?php
/**
 * Contains code for api util class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Util;

use Vignoblexport\VignoblexportConnectWoocommerce\Plugin;

/**
 * Api util class.
 *
 * Helper to manage API responses.
 *
 * @class       Api_Util
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 * @category    Class
 * @author      API Vignoblexport
 */
class Api_Util {

	/**
	 * API request validation.
	 *
	 * @param integer $code http code.
	 * @param mixed   $body to send along response.
	 * @void
	 */
	public static function send_api_response( $code, $body = null ) {
		$Vignoblexport_connect = Plugin::getInstance();
		header( 'X-Version: ' . $Vignoblexport_connect['version'] );
		
		http_response_code( $code );
		if ( null !== $body ) {
            // phpcs:ignore
            echo Auth_Util::encrypt_body( $body );
		}
		die();
	}
}

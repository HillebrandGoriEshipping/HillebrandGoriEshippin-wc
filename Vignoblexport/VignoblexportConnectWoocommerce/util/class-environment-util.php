<?php
/**
 * Contains code for environment util class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Util;

use Vignoblexport\VignoblexportConnectWoocommerce\Plugin;
use Vignoblexport\VignoblexportPhp\RestClient;

/**
 * Environment util class.
 *
 * Helper to check environment.
 *
 * @class       Environment_Util
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 * @category    Class
 * @author      API Vignoblexport
 */
class Environment_Util {

	/**
	 * Get warning about PHP version, WC version.
	 *
	 * @param Plugin $plugin plugin object.
	 * @return string $message
	 */
	public static function check_errors( $plugin ) {
		if ( false === RestClient::healthcheck() ) {
			return __( 'VignoblExport - You need either the curl extension or allow_url_fopen activated on your server for the Vignoblexport plugin to work.', 'Vignoblexport' );
		}

		if ( version_compare( PHP_VERSION, $plugin['min-php-version'], '<' ) ) {
			/* translators: 1) int version 2) int version */
			$message = __( 'VignoblExport - The minimum PHP version required for this plugin is %1$s. You are running %2$s.', 'Vignoblexport' );

			return sprintf( $message, $plugin['min-php-version'], PHP_VERSION );
		}

		if ( ! defined( 'WC_VERSION' ) ) {
			return __( 'VignoblExport plugin requires WooCommerce to be activated to work.', 'Vignoblexport' );
		}

		if ( version_compare( WC_VERSION, $plugin['min-wc-version'], '<' ) ) {
			/* translators: 1) int version 2) int version */
			$message = __( 'VignoblExport - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'Vignoblexport' );

			return sprintf( $message, $plugin['min-wc-version'], WC_VERSION );
		}
		return false;
	}
}

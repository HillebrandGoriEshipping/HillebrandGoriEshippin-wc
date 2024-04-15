<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Vignoblexport <api@Vignoblexport.com>
 * @copyright 2007-2020 PrestaShop SA / 2018-2020 Vignoblexport
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * @package   Vignoblexport\VignoblexportConnectWoocommerce\Util
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Contains code for configuration util class.
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Util;

use Vignoblexport\VignoblexportConnectWoocommerce\Util\Shipping_Method_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Controller;

/**
 * Auth util class.
 *
 * Helper to manage API auth.
 *
 * @class       Auth_Util
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Util
 * @category    Class
 * @author      API Vignoblexport
 */
class Configuration_Report_Util {

	/**
	 * Generate a full configuration report.
	 *
	 * @return mixed
	 */
	public static function get_configuration_report() {
		$report = array();

		$report['Vignoblexport_config']  = self::get_Vignoblexport_config();
		$report['zones']          = self::get_zones();
		$report['order_statuses'] = self::get_order_statuses();
		$report['classes']        = self::get_shipping_classes();
		$report['versions']       = self::get_versions();
		$report['php_extensions'] = self::get_php_extensions();
		$report['plugins']        = self::get_plugins();
		$report['settings']       = self::get_settings();

		return $report;
	}

	/**
	 * Get all Vignoblexport configurations.
	 *
	 * @return mixed
	 */
	private static function get_Vignoblexport_config() {
		return Configuration_Util::get_all_configs();
	}

	/**
	 * Get all order statuses
	 *
	 * @return mixed
	 */
	private static function get_order_statuses() {
		return wc_get_order_statuses();
	}

	/**
	 * Get platform versions
	 *
	 * @return mixed
	 */
	private static function get_versions() {
		$versions = array();
		global $wp_version;

		$versions['php']            = phpversion();
		$versions['wordpress']      = isset( $wp_version ) ? $wp_version : null;
		$versions['woocommerce']    = defined( 'WC_VERSION' ) ? WC_VERSION : null;
		$versions['Vignoblexport'] = defined( 'Vignoblexport_CONNECT_VERSION' ) ? Vignoblexport_CONNECT_VERSION : null;

		return $versions;
	}

	/**
	 * Get installed php extensions
	 *
	 * @return mixed
	 */
	private static function get_php_extensions() {
		$extensions = get_loaded_extensions();
		sort( $extensions );
		return $extensions;
	}


	/**
	 * Get installed plugins
	 *
	 * @return mixed
	 */
	private static function get_plugins() {
		$plugins = get_plugins();
		$result  = array();

		foreach ( $plugins as $plugin ) {
			$result[] = array(
				'name'    => $plugin['Name'],
				'version' => $plugin['Version'],
			);
		}

		return $result;
	}

	/**
	 * Get shipping method details
	 *
	 * @param WC_Shipping_Method $shipping_method shipping method object.
	 * @return mixed
	 */
	private static function get_shipping_method_details( $shipping_method ) {
		$class                  = get_class( $shipping_method );
		$shipping_method_detail = array(
			'class' => $class,
		);

		if ( 'WC_Shipping_Flat_Rate' === $class ) {
			$shipping_method_detail['name']       = $shipping_method->title;
			$shipping_method_detail['tax_status'] = $shipping_method->tax_status;
			$shipping_method_detail['cost']       = $shipping_method->cost;
			$shipping_method_detail['enabled']    = $shipping_method->enabled;
		} elseif ( 'WC_Shipping_Free_Shipping' === $class ) {
			$shipping_method_detail['name']           = $shipping_method->title;
			$shipping_method_detail['minimum_amount'] = $shipping_method->min_amount;
			$shipping_method_detail['requires']       = $shipping_method->requires;
			$shipping_method_detail['enabled']        = $shipping_method->enabled;
		} elseif ( 'WC_Shipping_Local_Pickup' === $class ) {
			$shipping_method_detail['name']       = $shipping_method->title;
			$shipping_method_detail['tax_status'] = $shipping_method->tax_status;
			$shipping_method_detail['cost']       = $shipping_method->cost;
			$shipping_method_detail['enabled']    = $shipping_method->enabled;
		} elseif ( 'Vignoblexport\\VignoblexportConnectWoocommerce\\Shipping_Method\\Shipping_Method' === $class ) {
			$shipping_method_detail['name']       = $shipping_method->title;
			$shipping_method_detail['tax_status'] = $shipping_method->tax_status;
			$shipping_method_detail['enabled']    = $shipping_method->enabled;
			$shipping_method_detail['pricing']    = array();

			$pricings = Controller::get_pricing_items( Shipping_Method_Util::get_unique_identifier( $shipping_method ) );
			foreach ( $pricings as $pricing ) {
				$shipping_method_detail['pricing'][] = array(
					'price_from'           => $pricing['price_from'],
					'price_to'             => $pricing['price_to'],
					'weight_from'          => $pricing['weight_from'],
					'weight_to'            => $pricing['weight_to'],
					'shipping_class'       => $pricing['shipping_class'],
					'parcel_point_network' => $pricing['parcel_point_network'],
					'pricing'              => $pricing['pricing'],
					'flat_rate'            => $pricing['flat_rate'],
				);
			}
		}

		return $shipping_method_detail;
	}

	/**
	 * Get zones
	 *
	 * @return mixed
	 */
	private static function get_zones() {
		$result = null;

		if ( class_exists( 'WC_Shipping_Zones', false ) ) {
			$zones = \WC_Shipping_Zones::get_zones();

			foreach ( $zones as $zone ) {
				$shipping_methods = $zone['shipping_methods'];
				$zone_data        = array(
					'name'             => $zone['zone_name'],
					'locations'        => $zone['zone_locations'],
					'shipping-methods' => array(),
				);

				foreach ( $shipping_methods as $shipping_method ) {
					$zone_data['shipping-methods'][] = self::get_shipping_method_details( $shipping_method );
				}

				$result[] = $zone_data;
			}
		}

		return $result;
	}

	/**
	 * Get shipping classes
	 *
	 * @return mixed
	 */
	private static function get_shipping_classes() {
		return Shipping_Method_Util::get_shipping_class_list();
	}

	/**
	 * Get woocommerce settings
	 *
	 * @return mixed
	 */
	private static function get_settings() {
		$result  = array();
		$options = array(
			'woocommerce_enable_shipping_calc',
			'woocommerce_shipping_cost_requires_address',
			'woocommerce_ship_to_destination',
			'woocommerce_shipping_debug_mode',
		);

		foreach ( $options as $option ) {
			$result[ $option ] = get_option( $option );
		}

		return $result;
	}
}

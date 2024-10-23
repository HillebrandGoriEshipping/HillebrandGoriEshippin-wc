<?php

/**
 * Contains code for the configuration util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice\Notice_Controller;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Parcel_Point\Controller;

/**
 * Configuration util class.
 *
 * Helper to manage configuration.
 *
 * @class       Configuration_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Configuration_Util
{

	/**
	 * List of all configuration keys used by the module
	 *
	 * @var mixed
	 */
	private static $all_configs = array(
		'VINW_ACCESS_KEY',
		'VINW_MAP_BOOTSTRAP_URL',
		'VINW_MAP_TOKEN_URL',
		'VINW_MAP_LOGO_IMAGE_URL',
		'VINW_MAP_LOGO_HREF_URL',
		'VINW_PP_NETWORKS',
		'VINW_TRACKING_EVENTS',
		'VINW_NOTICES',
		'VINW_PAIRING_UPDATE',
		'VINW_ORDER_SHIPPED',
		'VINW_ORDER_DELIVERED',
		'VINW_HELP_CENTER_URL',
		'VINW_TUTO_URL',
		'VINW_SHIPPING_RATES_URL',
		'VINW_HELP_SHIPPING_METHOD_URL',
		'VINW_SHIPPING_RULES_URL',
	);

	/**
	 * Build onboarding link.
	 *
	 * @return string onboarding link
	 */
	public static function get_onboarding_link()
	{
		$url    = VINW_ONBOARDING_URL;
		$params = array(
			'acceptLanguage' => get_locale(),
			'email'          => get_option('admin_email'),
			'shopUrl'        => get_option('siteurl'),
			'shopType'       => 'woocommerce',
		);
		return $url . '?' . http_build_query($params);
	}

	/**
	 * Get help center url
	 *
	 * @return string onboarding link
	 */
	public static function get_help_center_link()
	{
		$url = get_option('VINW_HELP_CENTER_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get help center url
	 *
	 * @return string onboarding link
	 */
	public static function get_tuto_link()
	{
		$url = get_option('VINW_TUTO_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get shipping rates url
	 *
	 * @return string shipping rates url
	 */
	public static function get_shipping_rates_link()
	{
		$url = get_option('VINW_SHIPPING_RATES_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get help shipping center url
	 *
	 * @return string help shipping center url
	 */
	public static function get_help_shipping_method_link()
	{
		$url = get_option('VINW_HELP_SHIPPING_METHOD_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get help shipping rules url
	 *
	 * @return string help shipping rules url
	 */
	public static function get_shipping_rules_link()
	{
		$url = get_option('VINW_SHIPPING_RULES_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get map logo href url.
	 *
	 * @return string map logo href url
	 */
	public static function get_map_logo_href_url()
	{
		$url = get_option('VINW_MAP_LOGO_HREF_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get map logo image url.
	 *
	 * @return string map logo image url
	 */
	public static function get_map_logo_image_url()
	{
		$url = get_option('VINW_MAP_LOGO_IMAGE_URL');
		return false !== $url ? $url : null;
	}

	/**
	 * Get all configurations.
	 *
	 * @return array
	 */
	public static function get_all_configs()
	{
		$configs = array();

		foreach (self::$all_configs as $config) {
			$configs[$config] = get_option($config);
		}

		return $configs;
	}

	/**
	 * Has configuration.
	 *
	 * @return boolean
	 */
	public static function has_configuration()
	{
		return false !== get_option('VINW_MAP_BOOTSTRAP_URL') && false !== get_option('VINW_MAP_TOKEN_URL') && false !== Controller::get_network_list();
	}

	/**
	 * Delete configuration.
	 *
	 * @void
	 */
	public static function delete_configuration()
	{
		global $wpdb;

		foreach (self::$all_configs as $config) {
			delete_option($config);
		}
		//phpcs:ignore
		$wpdb->query(
			$wpdb->prepare(
				"
                DELETE FROM $wpdb->options
		        WHERE option_name LIKE %s
		        ",
				'VINW_NOTICE_%'
			)
		);
	}

	/**
	 * Parse configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	public static function parse_configuration($body)
	{
		return self::parse_parcel_point_networks($body)
			&& self::parse_map_configuration($body)
			&& self::parse_links_configuration($body);
	}

	/**
	 * Is first activation.
	 *
	 * @return boolean
	 */
	public static function is_first_activation()
	{
		return false === get_option('VINW_NOTICES');
	}

	/**
	 * Parse pickup point networks response.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_parcel_point_networks($body)
	{
		if (is_object($body) && property_exists($body, 'parcelPointNetworks')) {

			$stored_networks = Controller::get_network_list();
			if (is_array($stored_networks)) {
				$removed_networks = $stored_networks;
				//phpcs:ignore
				foreach ($body->parcelPointNetworks as $new_network => $new_network_carriers) {
					foreach ($stored_networks as $old_network => $old_network_carriers) {
						if ($new_network === $old_network) {
							unset($removed_networks[$old_network]);
						}
					}
				}

				if (count($removed_networks) > 0) {
					Notice_Controller::add_notice(
						Notice_Controller::$custom,
						array(
							'status'  => 'warning',
							'message' => __('There\'s been a change in the pickup point network list, we\'ve adapted your shipping method configuration. Please check that everything is in order.', 'HillebrandGoriEshipping'),
						)
					);
				}

				//phpcs:ignore
				$added_networks = $body->parcelPointNetworks;
				//phpcs:ignore
				foreach ($body->parcelPointNetworks as $new_network => $new_network_carriers) {
					foreach ($stored_networks as $old_network => $old_network_carriers) {
						if ($new_network === $old_network) {
							unset($added_networks[$old_network]);
						}
					}
				}
				if (count($added_networks) > 0) {
					Notice_Controller::add_notice(
						Notice_Controller::$custom,
						array(
							'status'  => 'info',
							'message' => __('There\'s been a change in the pickup point network list, you can add the extra pickup point network(s) to your shipping method configuration.', 'HillebrandGoriEshipping'),
						)
					);
				}
			}
			//phpcs:ignore
			update_option('VINW_PP_NETWORKS', $body->parcelPointNetworks);
			return true;
		}
		return false;
	}

	/**
	 * Parse map configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_map_configuration($body)
	{
		if (
			is_object($body) && property_exists($body, 'mapsBootstrapUrl') && property_exists($body, 'mapsTokenUrl')
			&& property_exists($body, 'mapsLogoImageUrl') && property_exists($body, 'mapsLogoHrefUrl')
		) {
			//phpcs:ignore
			update_option('VINW_MAP_BOOTSTRAP_URL', $body->mapsBootstrapUrl);
			//phpcs:ignore
			update_option('VINW_MAP_TOKEN_URL', $body->mapsTokenUrl);
			//phpcs:ignore
			update_option('VINW_MAP_LOGO_IMAGE_URL', $body->mapsLogoImageUrl);
			//phpcs:ignore
			update_option('VINW_MAP_LOGO_HREF_URL', $body->mapsLogoHrefUrl);
			return true;
		}
		return false;
	}

	/**
	 * Parse help center configuration.
	 *
	 * @param object $body body.
	 * @return boolean
	 */
	private static function parse_links_configuration($body)
	{

		if (is_object($body) && property_exists($body, 'helpCenterUrl')) {
			//phpcs:ignore
			update_option('VINW_HELP_CENTER_URL', $body->helpCenterUrl);
		}
		if (is_object($body) && property_exists($body, 'configurationKitlUrl')) {
			//phpcs:ignore
			update_option('VINW_TUTO_URL', $body->configurationKitlUrl);
		}
		if (is_object($body) && property_exists($body, 'shippingPreferencesUrl')) {
			//phpcs:ignore
			update_option('VINW_SHIPPING_RULES_URL', $body->shippingPreferencesUrl);
		}
		if (is_object($body) && property_exists($body, 'shippingRateTutorialUrl')) {
			//phpcs:ignore
			update_option('VINW_SHIPPING_RATES_URL', $body->shippingRateTutorialUrl);
		}
		if (is_object($body) && property_exists($body, 'shippingMethodConfigurationTutorialUrl')) {
			//phpcs:ignore
			update_option('VINW_HELP_SHIPPING_METHOD_URL', $body->shippingMethodConfigurationTutorialUrl);
		}
		return true;
	}
}

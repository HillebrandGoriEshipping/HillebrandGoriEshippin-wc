<?php

/**
 * Contains code for shipping method util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

/**
 * Shipping method util class.
 *
 * Helper to manage consistency between woocommerce versions shipping methods.
 *
 * @class       Shipping_Method_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Shipping_Method_Util
{

	/**
	 * Get unique instance identifier from shipping method (must be same as rate id).
	 *
	 * @param \WC_Shipping_Method $method woocommerce shipping method.
	 *
	 * @return string $key shipping method identifier
	 */
	public static function get_unique_identifier($method)
	{
		return $method->id . ':' . $method->instance_id;
	}

	/**
	 * Get existing shipping classes + "none" shipping class.
	 *
	 * @return array $shipping_classes shipping classes
	 */
	public static function get_shipping_class_list()
	{
		if (method_exists(WC()->shipping, 'get_shipping_classes')) {
			$shipping_class_list = WC()->shipping->get_shipping_classes();
		} else {
			$shipping_class_list = WC()->shipping->shipping_classes;
		}
		$shipping_classes = array();
		foreach ($shipping_class_list as $class) {
			$shipping_classes[$class->slug] = $class->name;
		}
		$shipping_classes['none'] = __('No shipping class', 'HillebrandGoriEshipping');
		return $shipping_classes;
	}

	/**
	 * If the deprecated parcel point field is used on a shipping method.
	 *
	 * @return boolean
	 */
	public static function is_used_deprecated_parcel_point_field()
	{
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}options WHERE option_name like \"woocommerce_%_settings\"", ARRAY_A); // db call ok.
		foreach ($results as $option) {
			if (isset($option['option_value'])) {
				//phpcs:ignore
				$value = @unserialize($option['option_value']);
				if (false === $value) {
					continue;
				} elseif (isset($value['VINW_parcel_point_networks']) && ! empty($value['VINW_parcel_point_networks'])) {
					return true;
				}
			}
		}
		return false;
	}
}

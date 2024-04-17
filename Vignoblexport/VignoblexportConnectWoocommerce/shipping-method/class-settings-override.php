<?php

/**
 * Contains code for the settings override class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method;

use Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method\Parcel_Point\Controller;

/**
 * Settings_Override class.
 *
 * Add tag setting to shipping methods.
 *
 * @class       Settings_Override
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Shipping_Method
 * @category    Class
 * @author      API Vignoblexport
 */
class Settings_Override
{
	public $plugin_url;
	public $plugin_version;

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct($plugin)
	{
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		add_action('admin_enqueue_scripts', array($this, 'shipping_settings_styles'));
		add_filter('woocommerce_shipping_methods', array($this, 'shipping_methods_settings_override'));
	}

	/**
	 * Add extra field to shipping methods.
	 *
	 * @param array $shipping_methods wc shipping methods.
	 * @return array
	 */
	public function shipping_methods_settings_override($shipping_methods)
	{
		foreach ($shipping_methods as $shipping_method => $classname) {
			if ('Vignoblexport_connect' !== $shipping_method && 'local_pickup' !== $shipping_method) {
				if ('free_shipping' === $shipping_method) {
					add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method, array($this, 'add_form_field_free'));
				} else {
					add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method, array($this, 'add_form_field_default'));
				}
			}

			if ('Vignoblexport_connect' === $shipping_method) {
				add_filter('woocommerce_shipping_instance_form_fields_' . $shipping_method, array($this, 'add_form_field_default'));
			}
		}

		return $shipping_methods;
	}

	/**
	 * Enqueue shipping settings scripts
	 *
	 * @param string $hook hook name.
	 * @void
	 */
	public function shipping_settings_styles($hook)
	{
		// phpcs:ignore
		$current_tab = isset($_GET['tab']) && !empty($_GET['tab']) ? urldecode(sanitize_text_field(wp_unslash($_GET['tab']))) : '';
		if ('woocommerce_page_wc-settings' === $hook && 'shipping' === $current_tab) {
			wp_enqueue_style('VINW_notices', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/css/shipping-method.css', array(), $this->plugin_version);
		}
	}

	/**
	 * Add custom form fields to shipping methods.
	 *
	 * @param array $form_fields existing form fields.
	 * @return array $form_fields
	 */
	public function add_form_field_free($form_fields)
	{
		return $this->add_form_field($form_fields, __('To use only if you want to offer free shipping through coupons.', 'Vignoblexport'));
	}

	/**
	 * Add custom form fields to shipping methods.
	 *
	 * @param array $form_fields existing form fields.
	 * @return array $form_fields
	 */
	public function add_form_field_default($form_fields)
	{
		return $this->add_form_field($form_fields, __('No need to use it if you’re using VignoblExport shipping method, as it’s available directly when you set it up.', 'Vignoblexport'));
	}

	/**
	 * Add custom form fields to shipping methods.
	 *
	 * @param array $form_fields existing form fields.
	 * @return array $form_fields
	 */
	public function add_form_field($form_fields,  $description)
	{
		$network_options                         = Controller::get_network_options();
		$form_fields['VINW_parcel_point_networks'] = array(
			'title'       => __('Pickup point map', 'Vignoblexport'),
			'type'        => 'multiselect',
			'description' => $description,
			'options'     => $network_options,
			'default'     => array(),
			'class'       => 'wc-enhanced-select VINW-parcel-point-networks-dropdown',
		);
		return $form_fields;
	}
}

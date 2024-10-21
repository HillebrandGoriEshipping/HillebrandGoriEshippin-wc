<?php

/**
 * Plugin Name: Hillebrand Gori eShipping Preprod
 * Description: Managing your shipments becomes easier with our plugin! Save time and enjoy negotiated rates with many carriers: Chronopost, UPS, Fedex, DHL, ...
 * Author: Hillebrand Gori eShipping
 * Author URI: https://eshipping.hillebrandgori.com/
 * Text Domain: Hillebrand Gori eShipping
 * Domain Path: /hillebrandGoriEshipping/hillebrandGoriEshippingConnectWoocommerce/translation
 * Version: 1.5.0
 * WC requires at least: 2.6.14
 * WC tested up to: 8.4.0
 *
 * @package HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce
 */

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init\Component;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init\Environment_Check;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init\Setup_Wizard;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init\Translation;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice\Notice_Controller;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Plugin;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Rest_Controller\Order;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Rest_Controller\Shop;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Parcel_Point\Checkout;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Parcel_Point\Label_Override;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Settings_Override;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Settings\Page;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Settings\Page2;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order\Admin_Order_Page;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order\Front_Order_Page;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Auth_Util;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Configuration_Util;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Database_Util;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Environment_Util;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Shipping_Method_Util;

if (!function_exists('is_plugin_active_for_network')) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

require_once trailingslashit(__DIR__) . 'HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce/autoloader.php';

define('HillebrandGoriEshipping_VERSION', '1.4.1');

add_action('plugins_loaded', 'HillebrandGoriEshipping_connect_init');
add_action('init', 'register_awaiting_shipment_order_status');
add_filter('wc_order_statuses', 'add_awaiting_shipment_to_order_statuses');
/**
 * Plugin initialization.
 *
 * @void
 */
function HillebrandGoriEshipping_connect_init()
{

	define('VINW_ONBOARDING_URL', 'https://eshipping.hillebrandgori.app/api/');

	$plugin                      = new Plugin(); // Create container.
	$plugin['path']              = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR;
	$plugin['url']               = plugin_dir_url(__FILE__);
	$plugin['version']           = HillebrandGoriEshipping_VERSION;
	$plugin['min-wc-version']    = '2.6.14';
	$plugin['min-php-version']   = '5.6.0';
	$plugin['check-environment'] = 'HillebrandGoriEshipping_connect_check_environment';
	$plugin['notice']            = 'HillebrandGoriEshipping_connect_init_admin_notices';
	//phpcs:ignore
	if (false === Environment_Util::check_errors($plugin)) {
		$plugin['setup-wizard']         = 'HillebrandGoriEshipping_connect_setup_wizard';
		$plugin['translate']         = 'HillebrandGoriEshipping_connect_translate';
		$plugin['rest-controller-shop'] = 'HillebrandGoriEshipping_connect_rest_controller_shop';
		if (Auth_Util::can_use_plugin()) {
			$plugin['tracking-controller']               = 'HillebrandGoriEshipping_connect_tracking_controller';
			$plugin['front-order-page']                  = 'HillebrandGoriEshipping_connect_front_order_page';
			$plugin['admin-order-page']                  = 'HillebrandGoriEshipping_connect_admin_order_page';
			$plugin['rest-controller-order']             = 'HillebrandGoriEshipping_connect_rest_controller_order';
			$plugin['HillebrandGoriEshipping-shipping-method']    = 'HillebrandGoriEshipping_connect_shipping_method';
			$plugin['shipping-method-settings-override'] = 'HillebrandGoriEshipping_connect_shipping_method_settings_override';
			$plugin['shipping-method-controller']        = 'HillebrandGoriEshipping_connect_shipping_method_controller';
			$plugin['parcel-point-label-override']       = 'HillebrandGoriEshipping_connect_parcel_point_label_override';
			$plugin['parcel-point-controller']           = 'HillebrandGoriEshipping_connect_parcel_point_controller';
			$plugin['parcel-point-checkout']             = 'HillebrandGoriEshipping_connect_parcel_point_checkout';
			$plugin['settings-page2']                     = 'HillebrandGoriEshipping_connect_settings_page2';
		}
		$plugin['settings-page']                     = 'HillebrandGoriEshipping_connect_settings_page';
	}

	$plugin->run();
}


register_activation_hook(__FILE__, 'HillebrandGoriEshipping_connect_activate_network');
/**
 * Network activation.
 *
 * @param boolean $network_wide whether it is a network wide activation or not.
 * @void
 */
function HillebrandGoriEshipping_connect_activate_network($network_wide)
{
	if (function_exists('is_multisite') && is_multisite() && $network_wide) {
		global $wpdb;
		$current_blog = $wpdb->blogid;

		//phpcs:ignore
		$blog_ids = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
		foreach ($blog_ids as $blog_id) {
			//phpcs:ignore
			switch_to_blog($blog_id);
			HillebrandGoriEshipping_connect_activate_simple();
		}
		//phpcs:ignore
		switch_to_blog($current_blog);
	} else {
		HillebrandGoriEshipping_connect_activate_simple();
	}

	$setup_wizzard = new Setup_Wizard(true);
	$setup_wizzard->run();
}


/**
 * Simple activation.
 *
 * @void
 */
function HillebrandGoriEshipping_connect_activate_simple()
{

	Database_Util::create_tables();

	if (!Configuration_Util::is_first_activation() && Auth_Util::can_use_plugin() && Shipping_Method_Util::is_used_deprecated_parcel_point_field()) {

		Notice_Controller::add_notice(
			Notice_Controller::$custom,
			array(
				'status'       => 'warning',
				'message'      => __('Hillebrand Gori eShipping - from version 1.1.0, use of parcel point map additional field on shipping methods is deprecated. Use the HillebrandGoriEshipping method instead.', 'HillebrandGoriEshipping'),
				'autodestruct' => false,
			)
		);
	}
}

register_uninstall_hook(__FILE__, 'HillebrandGoriEshipping_connect_uninstall_network');
/**
 * Network uninstall.
 *
 * @param boolean $network_wide whether it is a network wide uninstall or not.
 * @void
 */
function HillebrandGoriEshipping_connect_uninstall_network($network_wide)
{
	if (function_exists('is_multisite') && is_multisite() && $network_wide) {
		global $wpdb;
		$current_blog = $wpdb->blogid;

		//phpcs:ignore
		$blog_ids = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
		foreach ($blog_ids as $blog_id) {
			//phpcs:ignore
			switch_to_blog($blog_id);
			HillebrandGoriEshipping_connect_uninstall_simple();
		}
		//phpcs:ignore
		switch_to_blog($current_blog);
	} else {
		HillebrandGoriEshipping_connect_uninstall_simple();
	}
}

/**
 * Simple uninstall.
 *
 * @void
 */
function HillebrandGoriEshipping_connect_uninstall_simple()
{
	Configuration_Util::delete_configuration();
}

add_action('wpmu_new_blog', 'HillebrandGoriEshipping_connect_network_activated', 10, 6);
/**
 * Runs activation for a plugin on a new site if plugin is already set as network activated on multisite
 *
 * @param int    $blog_id blog id of the created blog.
 * @param int    $user_id user id of the user creating the blog.
 * @param string $domain domain used for the new blog.
 * @param string $path path to the new blog.
 * @param int    $site_id site id.
 * @param array  $meta meta data.
 *
 * @void
 */
function HillebrandGoriEshipping_connect_network_activated($blog_id, $user_id, $domain, $path, $site_id, $meta)
{
	if (is_plugin_active_for_network('HillebrandGoriEshipping/HillebrandGoriEshipping.php')) {

		//phpcs:ignore
		switch_to_blog($blog_id);
		HillebrandGoriEshipping_connect_activate_simple();
		restore_current_blog();
	}
}

add_action('wpmu_drop_tables', 'HillebrandGoriEshipping_connect_uninstall_multisite_instance');
/**
 * Runs uninstall for a plugin on a multisite site if site is deleted
 *
 * @param array $tables the site tables to be dropped.
 * @param int   $blog_id the id of the site to drop tables for.
 *
 * @return array
 */
function HillebrandGoriEshipping_connect_uninstall_multisite_instance($tables, $blog_id)
{
	global $wpdb;
	$tables[] = $wpdb->prefix . 'VINW_pricing_items';
	return $tables;
}

/**
 * Initializes common admin components.
 *
 * @param array $plugin plugin array.
 * @return Translation $object static translation instance.
 */
function HillebrandGoriEshipping_connect_init_admin_components($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Component($plugin);

	return $object;
}


/**
 * Check PHP version, WC version.
 *
 * @param array $plugin plugin array.
 * @return Environment_Check $environment_check static environment check instance.
 */
function HillebrandGoriEshipping_connect_check_environment($plugin)
{
	static $environment_check;

	if (null !== $environment_check) {
		return $environment_check;
	}

	$environment_check = new Environment_Check($plugin);

	return $environment_check;
}

/**
 * Runs install.
 *
 * @param array $plugin plugin array.
 * @return Install $object static setup wizard instance.
 */
function HillebrandGoriEshipping_connect_setup_wizard($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Setup_Wizard();
	return $object;
}

/**
 * Runs install.
 *
 * @param array $plugin plugin array.
 * @return Install $object static translate instance.
 */
function HillebrandGoriEshipping_connect_translate($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Translation($plugin);
	return $object;
}

/**
 * Get new Order instance.
 *
 * @param array $plugin plugin array.
 * @return Order $object
 */
function HillebrandGoriEshipping_connect_rest_controller_order($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Order($plugin);
	return $object;
}

/**
 * Get new Shop instance.
 *
 * @param array $plugin plugin array.
 * @return Shop $object
 */
function HillebrandGoriEshipping_connect_rest_controller_shop($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Shop($plugin);
	return $object;
}

/**
 * Return admin notices singleton.
 *
 * @param array $plugin plugin array.
 * @return Notice_Controller $object
 */
function HillebrandGoriEshipping_connect_init_admin_notices($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Notice_Controller($plugin);
	return $object;
}

/**
 * HillebrandGoriEshipping shipping method init.
 *
 * @void
 */
/**
 * add status.
 *
 * @void
 */
function register_awaiting_shipment_order_status()
{
	register_post_status('wc-awaiting-shipment', array(
		'label'                     => 'Awaiting shipment',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop('Awaiting shipment (%s)', 'Awaiting shipment (%s)')
	));
	register_post_status('wc-shipmented', array(
		'label'                     => 'Shipped',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop('Shipmented (%s)', 'Shipmented (%s)')
	));
	register_post_status('wc-delivered', array(
		'label'                     => 'Delivered',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop('Delivered (%s)', 'Delivered (%s)')
	));
}
// Add to list of WC Order statuses
function add_awaiting_shipment_to_order_statuses($order_statuses)
{

	$new_order_statuses = array();

	// add new order status after processing
	foreach ($order_statuses as $key => $status) {

		$new_order_statuses[$key] = $status;

		if ('wc-processing' === $key) {
			$new_order_statuses['wc-awaiting-shipment'] = __('Awaiting shipment', 'HillebrandGoriEshipping');
			$new_order_statuses['wc-shipmented'] = __('Shipped', 'HillebrandGoriEshipping');
			$new_order_statuses['wc-delivered'] = __('Delivered', 'HillebrandGoriEshipping');
		}
	}

	return $new_order_statuses;
}

function HillebrandGoriEshipping_connect_shipping_method_init()
{
	add_action('woocommerce_shipping_init', 'HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Shipping_Method');
}

/**
 * Add HillebrandGoriEshipping shipping method.
 *
 * @param array $methods woocommerce loaded shipping methods.
 *
 * @return array
 */
function HillebrandGoriEshipping_connect_shipping_method_add($methods)
{
	$methods['HillebrandGoriEshipping_connect'] = 'HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Shipping_Method';
	return $methods;
}

/**
 * Add HillebrandGoriEshipping shipping method.
 *
 * @param array $plugin plugin array.
 * @void
 */
function HillebrandGoriEshipping_connect_shipping_method($plugin)
{
	add_action('woocommerce_shipping_init', 'HillebrandGoriEshipping_connect_shipping_method_init');
	add_filter('woocommerce_shipping_methods', 'HillebrandGoriEshipping_connect_shipping_method_add');
}

/**
 * Return settings override singleton.
 *
 * @param array $plugin plugin array.
 * @return Settings_Override $object
 */
function HillebrandGoriEshipping_connect_shipping_method_settings_override($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Settings_Override($plugin);
	return $object;
}

/**
 * Shipping method controller.
 *
 * @param array $plugin plugin array.
 * @return Controller $object
 */
function HillebrandGoriEshipping_connect_shipping_method_controller($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Controller($plugin);
	return $object;
}

/**
 * Return label override singleton.
 *
 * @param array $plugin plugin array.
 * @return Label_Override $object
 */
function HillebrandGoriEshipping_connect_parcel_point_label_override($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Label_Override($plugin);
	return $object;
}

/**
 * Parcel point controller.
 *
 * @param array $plugin plugin array.
 * @return Controller $object
 */
function HillebrandGoriEshipping_connect_parcel_point_controller($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Shipping_Method\Parcel_Point\Controller($plugin);
	return $object;
}

/**
 * Manage parcel point checkout.
 *
 * @param array $plugin plugin array.
 * @return Checkout $object
 */
function HillebrandGoriEshipping_connect_parcel_point_checkout($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Checkout($plugin);
	return $object;
}

/**
 * Tracking controller.
 *
 * @param array $plugin plugin array.
 * @return Controller $object static controller instance.
 */
function HillebrandGoriEshipping_connect_tracking_controller($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order\Controller($plugin);
	return $object;
}

/**
 * Front order page.
 *
 * @param array $plugin plugin array.
 * @return Front_Order_Page $object static Front_Order_Page instance.
 */
function HillebrandGoriEshipping_connect_front_order_page($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Front_Order_Page($plugin);

	return $object;
}

/**
 * Admin order page.
 *
 * @param array $plugin plugin array.
 * @return Admin_Order_Page $object static Admin_Order_Page instance.
 */
function HillebrandGoriEshipping_connect_admin_order_page($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Admin_Order_Page($plugin);
	return $object;
}


/**
 * Plugin settings page.
 *
 * @param array $plugin plugin array.
 * @return Page $object static Page instance.
 */
function HillebrandGoriEshipping_connect_settings_page($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Page($plugin);

	return $object;
}
/**
 * Plugin settings page.
 *
 * @param array $plugin plugin array.
 * @return Page $object static Page instance.
 */
function HillebrandGoriEshipping_connect_settings_page2($plugin)
{
	static $object;

	if (null !== $object) {
		return $object;
	}

	$object = new Page2($plugin);

	return $object;
}

add_action("admin_init", "HillebrandGoriEshipping_connect_translate_script");
function HillebrandGoriEshipping_connect_translate_script()
{
	wp_register_script('vig_validateOrder', plugin_dir_url(__FILE__) . 'HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/js/validateOrder.js', array('jquery'));

	wp_enqueue_script('vig_validateOrder');

	wp_localize_script('vig_validateOrder', 'vig_js_strings', array(

		'nbr_colis' => __('Number of packages', 'HillebrandGoriEshipping'),

		'poids_colis' => __('Weight of each package', 'HillebrandGoriEshipping'),

		'choix_colissage' => __('Conditionnement', 'HillebrandGoriEshipping')

	));

	// product admin page translations
	wp_register_script('product_translations', get_option('siteurl') . '/wp-content/plugins/HillebrandGoriEshipping/HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/js/product-admin.js');

	$translation_array = array(
		'no_appellation' => __('No appellation available for this country. Please contact Hillebrand Gori eShipping support', 'HillebrandGoriEshipping'),
		'select_appellation' => __('Select an appellation', 'HillebrandGoriEshipping'),
		'hs_code_ok' => __('The product settings are correct.', 'HillebrandGoriEshipping'),
		'error_hscode' => __('The product settings are not correct. Please verify them and if this error persists contact us.', 'HillebrandGoriEshipping'),
	);
	wp_localize_script('product_translations', 'product_translation', $translation_array);

	wp_enqueue_script('product_translations');
}

/**
 * @snippet       Add Custom Field to Product Variations - WooCommerce
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.6
 */

// -----------------------------------------
// 1. Add custom field input @ Product Data > Variations > Single Variation

add_action('woocommerce_variation_options_pricing', 'quantity_add_custom_field_to_variations', 10, 3);

function quantity_add_custom_field_to_variations($loop, $variation_data, $variation)
{
	woocommerce_wp_text_input(array(
		'id' => '_variation_quantity[' . $loop . ']',
		'type' => 'number',
		'class' => 'short',
		'label' => __('Quantity', 'HillebrandGoriEshipping'),
		'value' => get_post_meta($variation->ID, '_variation_quantity', true),
		'custom_attributes' => array(
			'step' 	=> 'any',
			'min'	=> '1'
		)
	));
}
// 2. Save custom field on product variation save

add_action('woocommerce_save_product_variation', 'quantity_save_custom_field_variations', 10, 2);

function quantity_save_custom_field_variations($variation_id, $i)
{
	$custom_field = $_POST['_variation_quantity'][$i];
	if (isset($custom_field)) update_post_meta($variation_id, '_variation_quantity', esc_attr($custom_field));
}
// 3. Store custom field value into variation data

add_filter('woocommerce_available_variation', 'quantity_add_custom_field_variation_data');

function quantity_add_custom_field_variation_data($variations)
{
	$variations['_variation_quantity'] = '<div class="woocommerce_custom_field">Variation quantity: <span>' . get_post_meta($variations['variation_id'], '_variation_quantity', true) . '</span></div>';
	return $variations;
}

// Custom product datas fields
// Display Fields
add_action('woocommerce_product_options_general_product_data', 'HillebrandGoriEshipping_product_custom_fields');
// Save Fields
add_action('woocommerce_process_product_meta', 'HillebrandGoriEshipping_product_custom_fields_save');
/**
 * @snippet Add Custom fields to Product
 */
function HillebrandGoriEshipping_product_custom_fields()
{
	global $woocommerce, $post;
	$min_nbr = get_post_meta($post->ID, '_custom_number_bottle', true);

	if (!$min_nbr) {
		$min_nbr = 1;
	}

	echo '<div class="product_custom_field">';

	// Custom Product number field
	woocommerce_wp_text_input(
		array(
			'id' => '_custom_number_bottle',
			'label' => __('Number of bottle', 'HillebrandGoriEshipping'),
			'type' => 'number',
			'value' => $min_nbr,
			'custom_attributes' => array(
				'step' => '1',
				'min' => '1'
			)
		)
	);

	// Custom bottle size select field
	woocommerce_wp_select(array( // Text Field type
		'id'          => '_custom_bottle_size',
		'label'       => __('Size of bottle', 'HillebrandGoriEshipping'),
		'options'  	  => array(
			'bottle'     => __('Standard bottle', 'HillebrandGoriEshipping'),
			'magnum'     => __('Magnum', 'HillebrandGoriEshipping'),
		)
	));

	// Custom Product select field
	woocommerce_wp_select(array( // Text Field type
		'id'          => '_custom_type',
		'label'       => __('Type', 'HillebrandGoriEshipping'),
		'description' => __('Type of product', 'HillebrandGoriEshipping'),
		'desc_tip'    => true,
		'options'  	  => array(
			NULL          => __('Select product type', 'HillebrandGoriEshipping'),
			'still'     => __('Still Wine', 'HillebrandGoriEshipping'),
			'sparkling' => __('Sparkling Wine', 'HillebrandGoriEshipping'),
			'spirits'    => __('Spirit', 'HillebrandGoriEshipping'),
			'dry'       => __('Non Alcoholic', 'HillebrandGoriEshipping'),
		),
		'custom_attributes' => array(
			'required' => 'required'
		)
	));

	// Custom Product select field
	woocommerce_wp_select(array( // Text Field type
		'id'          => '_custom_color',
		'label'       => __('Color', 'HillebrandGoriEshipping'),
		'description' => __('Color of the product', 'HillebrandGoriEshipping'),
		'desc_tip'    => true,
		'options'  	  => array(
			NULL          => __('Select product color', 'HillebrandGoriEshipping'),
			'Red'     => __('Red', 'HillebrandGoriEshipping'),
			'White' => __('White', 'HillebrandGoriEshipping'),
			'Rose'    => __('Rose', 'HillebrandGoriEshipping'),
			'Spirits'       => __('Spirits', 'HillebrandGoriEshipping'),
			'Beer' => __('Beer', 'HillebrandGoriEshipping'),
		),
		'custom_attributes' => array(
			'required' => 'required'
		)
	));

	//Custom Product Number Field
	woocommerce_wp_text_input(
		array(
			'id' => '_custom_capacity',
			'placeholder' => 'in mL',
			'label' => __('Capacity of a single product (in mL)', 'HillebrandGoriEshipping'),
			'type' => 'number',
			'custom_attributes' => array(
				'step' => 'any',
				'min' => '0',
				'required' => 'required'

			)
		)
	);

	//Custom Product Number Field
	woocommerce_wp_text_input(
		array(
			'id' => '_custom_alcohol_degree',
			'placeholder' => 'in %',
			'label' => __('Alcohol Degree', 'HillebrandGoriEshipping'),
			'type' => 'number',
			'custom_attributes' => array(
				'step' => '0.1',
				'min' => '0',
				'required' => 'required'
			)
		)
	);

	woocommerce_wp_text_input(
		array(
			'id' => '_custom_vintage',
			'label' => __('Vintage', 'HillebrandGoriEshipping'),
			'type' => 'number',
			'custom_attributes' => array(
				'step' => '1',
				'min' => '0',
				'required' => 'required'
			)
		)
	);

	woocommerce_wp_select(array( // Text Field type
		'id'          => '_custom_circulation',
		'label'       => __('Circulation', 'HillebrandGoriEshipping'),
		'description' => __('Circulation of the commodity (excise duty paid or suspended)', 'HillebrandGoriEshipping'),
		'desc_tip'    => true,
		'options'  	  => array(
			'CRD'     => __('CRD', 'HillebrandGoriEshipping'),
			'DAE' 	  => __('DAE', 'HillebrandGoriEshipping'),
		),
		'custom_attributes' => array(
			'required' => 'required'
		)
	));

	// Custom Product select field
	woocommerce_wp_select(array( // Text Field type
		'id'          => '_custom_producing_country',
		'label'       => __('Producing Country', 'HillebrandGoriEshipping'),
		'description' => __('If you can\'t find the producing country in the list, please contact us', 'HillebrandGoriEshipping'),
		'desc_tip'    => true,
		'options'  	  => array(
			NULL => __('Select producing country', 'HillebrandGoriEshipping'),
			'DZ' => __('Algeria', 'HillebrandGoriEshipping'),
			'AR' => __('Argentina', 'HillebrandGoriEshipping'),
			'AU' => __('Australia', 'HillebrandGoriEshipping'),
			'AT' => __('Austria', 'HillebrandGoriEshipping'),
			'BB' => __('Barbados', 'HillebrandGoriEshipping'),
			'BE' => __('Belgium', 'HillebrandGoriEshipping'),
			'BZ' => __('Belize', 'HillebrandGoriEshipping'),
			'BG' => __('Bulgaria', 'HillebrandGoriEshipping'),
			'CA' => __('Canada', 'HillebrandGoriEshipping'),
			'CL' => __('Chile', 'HillebrandGoriEshipping'),
			'CN' => __('China', 'HillebrandGoriEshipping'),
			'CU' => __('Cuba', 'HillebrandGoriEshipping'),
			'DO' => __('Dominican Republic', 'HillebrandGoriEshipping'),
			'FR' => __('France', 'HillebrandGoriEshipping'),
			'GB' => __('Great Britain', 'HillebrandGoriEshipping'),
			'GF' => __('French Guiana', 'HillebrandGoriEshipping'),
			'DE' => __('Germany', 'HillebrandGoriEshipping'),
			'GR' => __('Greece', 'HillebrandGoriEshipping'),
			'GP' => __('Guadeloupe', 'HillebrandGoriEshipping'),
			'HU' => __('Hungary', 'HillebrandGoriEshipping'),
			'IN' => __('India', 'HillebrandGoriEshipping'),
			'IL' => __('Israel', 'HillebrandGoriEshipping'),
			'IT' => __('Italy', 'HillebrandGoriEshipping'),
			'JM' => __('Jamaica', 'HillebrandGoriEshipping'),
			'JP' => __('Japan', 'HillebrandGoriEshipping'),
			'LB' => __('Lebanon', 'HillebrandGoriEshipping'),
			'MX' => __('Mexico', 'HillebrandGoriEshipping'),
			'MD' => __('Moldova', 'HillebrandGoriEshipping'),
			'NZ' => __('New Zealand', 'HillebrandGoriEshipping'),
			'PT' => __('Portugal', 'HillebrandGoriEshipping'),
			'ZA' => __('South Africa', 'HillebrandGoriEshipping'),
			'ES' => __('Spain', 'HillebrandGoriEshipping'),
			'CH' => __('Switzerland', 'HillebrandGoriEshipping'),
			'TH' => __('Thailand', 'HillebrandGoriEshipping'),
			'TT' => __('Trinidad and Tobago', 'HillebrandGoriEshipping'),
			'UY' => __('Uruguay', 'HillebrandGoriEshipping'),
			'US' => __('USA', 'HillebrandGoriEshipping')
		),
		'custom_attributes' => array(
			'required' => 'required'
		)
	));

	// Custom Product select field
	if (get_post_meta($post->ID, '_custom_producing_country', true) != NULL) {
		$producingCountry = get_post_meta($post->ID, '_custom_producing_country', true);

		$curl = curl_init();
		$url = "https://test.eshipping.hillebrandgori.app/api/get-appellations?";
		$url .= "producingCountry=" . $producingCountry;

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
			),
		));

		$get_appellations = curl_exec($curl);
		$appelationsNames = json_decode($get_appellations, null);

		if (is_array($appelationsNames)) {
			curl_close($curl);
			$keys = array();
			foreach ($appelationsNames as $name) {
				$esc_name = esc_attr($name);
				array_push($keys, $esc_name);
			}
			$allAppellations = array_combine($keys, $appelationsNames);

			if (isset($producingCountry) || !empty($producingCountry)) {

				woocommerce_wp_select(
					array( // Text Field type
						'id'          => '_custom_appelation',
						'label'       => __('Appellation', 'HillebrandGoriEshipping'),
						'description' => __('If you can\'t find the appellation in the list, please contact us', 'HillebrandGoriEshipping'),
						'desc_tip'    => true,
						'options'  	  => $allAppellations
					),
				);
			}
		} else {
			woocommerce_wp_select(
				array( // Text Field type
					'id'          => '_custom_appelation',
					'label'       => __('Appellation', 'HillebrandGoriEshipping'),
					'description' => __('If you can\'t find the appellation in the list, please contact us', 'HillebrandGoriEshipping'),
					'desc_tip'    => true,
					'options'  	  => array(
						NULL => __('Select appellation', 'HillebrandGoriEshipping'),
					),
				),
			);
		}
	} else {
		woocommerce_wp_select(
			array( // Text Field type
				'id'          => '_custom_appelation',
				'label'       => __('Appellation', 'HillebrandGoriEshipping'),
				'description' => __('If you can\'t find the appellation in the list, please contact us', 'HillebrandGoriEshipping'),
				'desc_tip'    => true,
				'options'  	  => array(
					NULL => __('Select appellation', 'HillebrandGoriEshipping'),
				),
			),
		);
	}
	echo '<div id="product-error"><p></p></div>';
	echo '</div>';
}

/**
 * @snippet Custom product data fields save
 */
function HillebrandGoriEshipping_product_custom_fields_save($post_id)
{
	// Custom Product select Field
	$woocommerce_custom_product_type = $_POST['_custom_number_bottle'];

	if (!empty($woocommerce_custom_product_type))
		update_post_meta(
			$post_id,
			'_custom_number_bottle',
			esc_attr($woocommerce_custom_product_type)
		);


	// Custom Bottle Size select Field
	$woocommerce_custom_bottle_size = $_POST['_custom_bottle_size'];

	if (!empty($woocommerce_custom_bottle_size))
		update_post_meta(
			$post_id,
			'_custom_bottle_size',
			esc_attr($woocommerce_custom_bottle_size)
		);

	// Custom Product select Field
	$woocommerce_custom_product_type = $_POST['_custom_type'];

	if (!empty($woocommerce_custom_product_type))
		update_post_meta(
			$post_id,
			'_custom_type',
			esc_attr($woocommerce_custom_product_type)
		);

	// Custom Product select Field
	$woocommerce_custom_product_color = $_POST['_custom_color'];

	if (!empty($woocommerce_custom_product_color))
		update_post_meta(
			$post_id,
			'_custom_color',
			esc_attr($woocommerce_custom_product_color)
		);


	// Custom Product Number Field
	$woocommerce_custom_product_capacity = $_POST['_custom_capacity'];

	if (!empty($woocommerce_custom_product_capacity))
		update_post_meta(
			$post_id,
			'_custom_capacity',
			esc_attr($woocommerce_custom_product_capacity)
		);

	// Custom Product Number Field
	$woocommerce_custom_product_alcohol_degree = $_POST['_custom_alcohol_degree'];

	if (!empty($woocommerce_custom_product_alcohol_degree))
		update_post_meta(
			$post_id,
			'_custom_alcohol_degree',
			esc_attr($woocommerce_custom_product_alcohol_degree)
		);

	// Custom Product select Field
	$woocommerce_custom_product_appelation = $_POST['_custom_appelation'];
	update_post_meta(
		$post_id,
		'_custom_appelation',
		esc_attr($woocommerce_custom_product_appelation)
	);

	$woocommerce_custom_product_producing_country = $_POST['_custom_producing_country'];
	// Custom product select	
	if (!empty($woocommerce_custom_product_producing_country))
		update_post_meta(
			$post_id,
			'_custom_producing_country',
			esc_attr($woocommerce_custom_product_producing_country)
		);

	$woocommerce_custom_product_circulation = $_POST['_custom_circulation'];
	// Custom product circulation
	if (!empty($woocommerce_custom_product_circulation))
		update_post_meta(
			$post_id,
			'_custom_circulation',
			esc_attr($woocommerce_custom_product_circulation)
		);

	$woocommerce_custom_product_vintage = $_POST['_custom_vintage'];
	// Custom product vintage
	if (!empty($woocommerce_custom_product_vintage))
		update_post_meta(
			$post_id,
			'_custom_vintage',
			esc_attr($woocommerce_custom_product_vintage)
		);
}

add_action('woocommerce_product_data_panels', 'product_meta_script');
function product_meta_script()
{
	echo
	'<script src="' . get_option('siteurl') . '/wp-content/plugins/HillebrandGoriEshipping/HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/js/product-admin.js"></script>';
}

add_action('wp_ajax_check_country_has_appellations', 'check_country_has_appellations');
function check_country_has_appellations()
{
	$producingCountry = $_GET['country'];

	$curl = curl_init();
	$url = "https://test.eshipping.hillebrandgori.app/api/get-appellations?";
	$url .= "producingCountry=" . $producingCountry;

	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
		),
	));

	$get_appellations = curl_exec($curl);
	$appelationsNames = json_decode($get_appellations, true);
	curl_close($curl);
	if (!in_array(400, $appelationsNames)) {
		$keys = array();
		foreach ($appelationsNames as $name) {
			$esc_name = esc_attr($name);
			array_push($keys, $esc_name);
		}
		$allAppellations = array_combine($keys, $appelationsNames);
	} else {
		$allAppellations = false;
	}
	echo json_encode($allAppellations);
	die();
}

add_action('wp_ajax_is_product_info_correct', 'is_product_info_correct');

function is_product_info_correct()
{
	$appellation_name = htmlspecialchars_decode($_GET['appellation_name']);
	$capacity = $_GET['capacity'];
	$alcohol_degree = $_GET['alcohol_degree'];
	$color = $_GET['color'];
	if ($color === 'Red') {
		$color = 'red';
	} elseif ($color === 'Rose') {
		$color = 'rose';
	} else {
		$color = 'no-color';
	}

	$curlHscode = curl_init();
	$hscodeURL = "https://test.eshipping.hillebrandgori.app/api/get-hscode";
	$hscodeURL .= "?appellationName=" . $appellation_name;
	$hscodeURL .= "&capacity=" . $capacity;
	$hscodeURL .= "&alcoholDegree=" . $alcohol_degree;
	$hscodeURL .= "&color=" . $color;

	$hscodeURL = str_replace(" ", "%20", $hscodeURL);

	curl_setopt_array($curlHscode, array(
		CURLOPT_URL => $hscodeURL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"X-AUTH-TOKEN: " . get_option('VINW_ACCESS_KEY'),
		),
	));
	$responseHscode = curl_exec($curlHscode);
	curl_close($curlHscode);
	echo $responseHscode;
	die();
}

<?php

/**
 * Contains code for the order controller class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order;

use HillebrandGoriEshipping\HillebrandGoriEshippingPhp\ApiClient;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Auth_Util;

/**
 * Controller class.
 *
 * Handles additional info hooks and functions.
 *
 * @class       Controller
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Order
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Controller
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
	public function run() {}

	/**
	 * Get order tracking.
	 *
	 * @param string $order_id \WC_Order id.
	 * @return object tracking
	 */
	public function get_order_tracking($order_id)
	{
		$lib      = new ApiClient(Auth_Util::get_access_key());
		$response = $lib->getOrder($order_id);
		return $response;
	}

	/**
	 * Enqueue tracking styles
	 *
	 * @void
	 */
	public function tracking_styles()
	{
		wp_enqueue_style('VINW_tracking', $this->plugin_url . 'HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/css/tracking.css', array(), $this->plugin_version);
	}
}

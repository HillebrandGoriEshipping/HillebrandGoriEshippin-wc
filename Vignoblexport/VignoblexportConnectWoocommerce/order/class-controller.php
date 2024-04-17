<?php

/**
 * Contains code for the order controller class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Order
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Order;

use Vignoblexport\VignoblexportPhp\ApiClient;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Auth_Util;

/**
 * Controller class.
 *
 * Handles additional info hooks and functions.
 *
 * @class       Controller
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Order
 * @category    Class
 * @author      API Vignoblexport
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
	public function run()
	{
	}

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
		wp_enqueue_style('VINW_tracking', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/css/tracking.css', array(), $this->plugin_version);
	}
}

<?php

/**
 * Contains code for the environment check class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init;

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice\Notice_Controller;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Plugin;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Environment_Util;

/**
 * Environment check class.
 *
 * Display environment warning if needed.
 *
 * @class       Environment_Check
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Environment_Check
{
	public $plugin;

	/**
	 * Environment warning message.
	 *
	 * @var string.
	 */
	private $environment_warning;

	/**
	 * Construct function.
	 *
	 * @param Plugin $plugin plugin array.
	 * @void
	 */
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		$this->environment_warning = Environment_Util::check_errors($this->plugin);
		if (false !== $this->environment_warning) {
			Notice_Controller::remove_all_notices();
			Notice_Controller::add_notice(
				Notice_Controller::$environment_warning,
				array(
					'message' => $this->environment_warning,
				)
			);
		} elseif (Notice_Controller::has_notice(Notice_Controller::$environment_warning)) {
			Notice_Controller::remove_notice(Notice_Controller::$environment_warning);
		}
	}
}

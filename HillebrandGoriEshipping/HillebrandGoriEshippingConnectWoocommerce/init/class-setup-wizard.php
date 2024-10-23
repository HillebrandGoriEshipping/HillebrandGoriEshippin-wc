<?php

/**
 * Contains code for the setup wizard class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init;

use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice\Notice_Controller;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Auth_Util;
use HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util\Configuration_Util;

/**
 * Setup_Wizard class.
 *
 * Display setup wizard if needed.
 *
 * @class       Setup_Wizard
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Setup_Wizard
{

	public $activation;

	/**
	 * Construct function.
	 *
	 * @param bool $activation : is called on plugin activation.
	 * @void
	 */
	public function __construct($activation = false)
	{

		$this->activation = $activation;
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		Notice_Controller::add_notice(Notice_Controller::$setup_wizard);
	}
}

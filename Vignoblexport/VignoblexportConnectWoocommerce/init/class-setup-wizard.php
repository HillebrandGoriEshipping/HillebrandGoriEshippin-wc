<?php

/**
 * Contains code for the setup wizard class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Init;

use Vignoblexport\VignoblexportConnectWoocommerce\Notice\Notice_Controller;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Auth_Util;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Configuration_Util;

/**
 * Setup_Wizard class.
 *
 * Display setup wizard if needed.
 *
 * @class       Setup_Wizard
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 * @category    Class
 * @author      API Vignoblexport
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

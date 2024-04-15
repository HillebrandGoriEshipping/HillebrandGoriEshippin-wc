<?php
/**
 * Contains code for the environment check class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Init;

use Vignoblexport\VignoblexportConnectWoocommerce\Notice\Notice_Controller;
use Vignoblexport\VignoblexportConnectWoocommerce\Plugin;
use Vignoblexport\VignoblexportConnectWoocommerce\Util\Environment_Util;

/**
 * Environment check class.
 *
 * Display environment warning if needed.
 *
 * @class       Environment_Check
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 * @category    Class
 * @author      API Vignoblexport
 */
class Environment_Check {

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
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		$this->environment_warning = Environment_Util::check_errors( $this->plugin );
		if ( false !== $this->environment_warning ) {
			Notice_Controller::remove_all_notices();
			Notice_Controller::add_notice(
				Notice_Controller::$environment_warning, array(
					'message' => $this->environment_warning,
				)
			);
		} elseif ( Notice_Controller::has_notice( Notice_Controller::$environment_warning ) ) {
			Notice_Controller::remove_notice( Notice_Controller::$environment_warning );
		}
	}
}

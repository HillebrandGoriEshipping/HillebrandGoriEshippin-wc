<?php
/**
 * Contains code for the pairing notice class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Notice
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Notice;

/**
 * Pairing notice class.
 *
 * Successful pairing notice.
 *
 * @class       Pairing_Notice
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Notice
 * @category    Class
 * @author      API Vignoblexport
 */
class Pairing_Notice extends Abstract_Notice {

	/**
	 * Construct function.
	 *
	 * @param string $key key for notice.
	 * @param array  $args additional args.
	 * @void
	 */
	public function __construct( $key, $args ) {
		parent::__construct( $key );
		$this->type         = 'pairing';
		$this->autodestruct = false;
		$this->template     = $args['result'] ? 'html-pairing-success-notice' : 'html-pairing-failure-notice';
	}
}

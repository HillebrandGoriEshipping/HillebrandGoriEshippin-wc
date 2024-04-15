<?php
/**
 * Contains code for the pairing update notice class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Notice
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Notice;

/**
 * Pairing update notice class.
 *
 * Enables pairing update validation.
 *
 * @class       Pairing_Update_Notice
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Notice
 * @category    Class
 * @author      API Vignoblexport
 */
class Pairing_Update_Notice extends Abstract_Notice {

	/**
	 * Construct function.
	 *
	 * @param string $key key for notice.
	 * @void
	 */
	public function __construct( $key ) {
		parent::__construct( $key );
		$this->type         = 'pairing-update';
		$this->autodestruct = false;
		$this->template     = 'html-pairing-update-notice';
	}
}

<?php

/**
 * Contains code for the configuration failure notice class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice;

/**
 * Configuration failure notice class.
 *
 * Configuration failure notice used to display setup error.
 *
 * @class       Configuration_Failure_Notice
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Configuration_Failure_Notice extends Abstract_Notice
{

	/**
	 * Construct function.
	 *
	 * @param string $key key for notice.
	 * @void
	 */
	public function __construct($key)
	{
		parent::__construct($key);
		$this->type         = 'configuratino-failure';
		$this->autodestruct = false;
		$this->template     = 'html-configuration-failure-notice';
	}
}

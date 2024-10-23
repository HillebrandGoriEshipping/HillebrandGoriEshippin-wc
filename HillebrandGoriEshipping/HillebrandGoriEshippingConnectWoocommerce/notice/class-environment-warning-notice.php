<?php

/**
 * Contains code for the environment warning notice class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice;

/**
 * Environment warning notice class.
 *
 * Environment warning notice displays warning message.
 *
 * @class       Environment_Warning_Notice
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Notice
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Environment_Warning_Notice extends Abstract_Notice
{

	/**
	 * Notice message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Construct function.
	 *
	 * @param string $key key for notice.
	 * @param array  $args additional args.
	 * @void
	 */
	public function __construct($key, $args)
	{
		parent::__construct($key);
		$this->type         = 'custom';
		$this->autodestruct = false;
		$this->message      = isset($args['message']) ? $args['message'] : '';
		$this->template     = 'html-environment-warning-notice';
	}
}

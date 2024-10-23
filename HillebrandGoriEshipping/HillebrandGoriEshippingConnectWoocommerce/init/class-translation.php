<?php

/**
 * Contains code for the translation class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init;

/**
 * Translation class.
 *
 * Inits translation for WP < 4.6.
 *
 * @class       Translation
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Translation
{

	public $path;

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct($plugin)
	{
		$this->path = $plugin['path'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		add_action('init', array($this, 'HillebrandGoriEshipping_connect_load_textdomain'));
	}

	/**
	 * Loads plugin textdomain.
	 *
	 * @void
	 */
	public function HillebrandGoriEshipping_connect_load_textdomain()
	{
		$translation_folder_path = plugin_basename($this->path . '/HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/translation');
		load_plugin_textdomain('HillebrandGoriEshipping', false, $translation_folder_path);
	}
}

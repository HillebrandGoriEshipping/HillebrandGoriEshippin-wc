<?php

/**
 * Contains code for the translation class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Init;

/**
 * Translation class.
 *
 * Inits translation for WP < 4.6.
 *
 * @class       Translation
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 * @category    Class
 * @author      API Vignoblexport
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
		add_action('init', array($this, 'Vignoblexport_connect_load_textdomain'));
	}

	/**
	 * Loads plugin textdomain.
	 *
	 * @void
	 */
	public function Vignoblexport_connect_load_textdomain()
	{
		$translation_folder_path = plugin_basename($this->path . '/Vignoblexport/VignoblexportConnectWoocommerce/translation');
		load_plugin_textdomain('Vignoblexport', false, $translation_folder_path);
	}
}

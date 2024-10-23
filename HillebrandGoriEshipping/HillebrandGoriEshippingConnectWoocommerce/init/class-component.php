<?php

/**
 * Contains code for the component class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init;

/**
 * Component class.
 *
 * Inits components.
 *
 * @class       Component
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Init
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Component
{

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct($plugin)
	{
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run()
	{
		add_action('admin_enqueue_scripts', array($this, 'component_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'component_styles'));
	}

	/**
	 * Enqueue component scripts
	 *
	 * @void
	 */
	public function component_scripts()
	{
		wp_enqueue_script('VINW_components', $this->plugin_url . 'HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/js/component.min.js', array(), $this->plugin_version);
	}

	/**
	 * Enqueue component styles
	 *
	 * @void
	 */
	public function component_styles()
	{
		wp_enqueue_style('VINW_components', $this->plugin_url . 'HillebrandGoriEshipping/HillebrandGoriEshippingConnectWoocommerce/assets/css/component.css', array(), $this->plugin_version);
	}
}

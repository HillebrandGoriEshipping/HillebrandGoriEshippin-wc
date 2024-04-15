<?php
/**
 * Contains code for the component class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce\Init;

/**
 * Component class.
 *
 * Inits components.
 *
 * @class       Component
 * @package     Vignoblexport\VignoblexportConnectWoocommerce\Init
 * @category    Class
 * @author      API Vignoblexport
 */
class Component {

	/**
	 * Construct function.
	 *
	 * @param array $plugin plugin array.
	 * @void
	 */
	public function __construct( $plugin ) {
		$this->plugin_url     = $plugin['url'];
		$this->plugin_version = $plugin['version'];
	}

	/**
	 * Run class.
	 *
	 * @void
	 */
	public function run() {
		add_action( 'admin_enqueue_scripts', array( $this, 'component_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'component_styles' ) );
	}

	/**
	 * Enqueue component scripts
	 *
	 * @void
	 */
	public function component_scripts() {
		wp_enqueue_script( 'VINW_components', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/js/component.min.js', array(), $this->plugin_version );

	}

	/**
	 * Enqueue component styles
	 *
	 * @void
	 */
	public function component_styles() {
		wp_enqueue_style( 'VINW_components', $this->plugin_url . 'Vignoblexport/VignoblexportConnectWoocommerce/assets/css/component.css', array(), $this->plugin_version );
	}
}

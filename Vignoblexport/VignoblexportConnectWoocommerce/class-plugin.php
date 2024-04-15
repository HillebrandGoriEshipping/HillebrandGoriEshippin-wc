<?php
/**
 * Contains code for the plugin container class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce;

/**
 * Plugin container class.
 *
 * Allows plugin to be used as an array.
 *
 * @class       Plugin
 * @package     Vignoblexport\VignoblexportConnectWoocommerce
 * @category    Class
 * @author      API Vignoblexport
 */
class Plugin implements \ArrayAccess {

	/**
	 * Plugin instance content.
	 *
	 * @var Plugin
	 */
	public static $instance;

	/**
	 * Store content.
	 *
	 * @var contents
	 */
	protected $contents;

	/**
	 * Construct function. Initializes contents.
	 *
	 * @void
	 */
	public function __construct() {
		$this->contents  = array();
		$this::$instance = $this;
	}

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function getInstance() {
		return self::$instance;
	}

	/**
	 * Set value.
	 *
	 * @param string $offset key.
	 * @param mixed  $value value.
	 * @void
	 */
	public function offsetSet( $offset, $value ) {
		$this->contents[ $offset ] = $value;
	}

	/**
	 * Key exists.
	 *
	 * @param string $offset key.
	 * @boolean
	 */
	public function offsetExists( $offset ) {
		return isset( $this->contents[ $offset ] );
	}

	/**
	 * Unset key.
	 *
	 * @param string $offset key.
	 * @void
	 */
	public function offsetUnset( $offset ) {
		unset( $this->contents[ $offset ] );
	}

	/**
	 * Get value.
	 *
	 * @param string $offset key.
	 * @mixed
	 */
	public function offsetGet( $offset ) {
		if ( is_callable( $this->contents[ $offset ] ) ) {
			return call_user_func( $this->contents[ $offset ], $this );
		}
		return isset( $this->contents[ $offset ] ) ? $this->contents[ $offset ] : null;
	}

	/**
	 * Run container.
	 *
	 * @void
	 */
	public function run() {
		foreach ( $this->contents as $key => $content ) { // Loop on contents.
			if ( is_callable( $content ) ) {
				$content = $this[ $key ];
			}
			if ( is_object( $content ) ) {
				$reflection = new \ReflectionClass( $content );
				if ( $reflection->hasMethod( 'run' ) ) {
					$content->run(); // Call run method on object.
				}
			}
		}
	}
}

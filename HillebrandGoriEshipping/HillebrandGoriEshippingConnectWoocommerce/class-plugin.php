<?php

/**
 * Contains code for the plugin container class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce;

use ArrayAccess;

/**
 * Plugin container class.
 *
 * Allows plugin to be used as an array.
 *
 * @class       Plugin
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce
 * @category    Class
 * @author      API Hillebrand Gori eShipping
 */
class Plugin implements ArrayAccess
{

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
	public function __construct()
	{
		$this->contents  = array();
		$this::$instance = $this;
	}

	/**
	 * Get plugin instance.
	 *
	 * @return Plugin
	 */
	public static function getInstance()
	{
		return self::$instance;
	}

	/**
	 * Set value.
	 *
	 * @param mixed $offset key.
	 * @param mixed $value value.
	 * @return void
	 */
	public function offsetSet($offset, $value): void
	{
		if (is_null($offset)) {
			$this->contents[] = $value;
		} else {
			$this->contents[$offset] = $value;
		}
	}

	/**
	 * Key exists.
	 *
	 * @param mixed $offset key.
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->contents[$offset]);
	}

	/**
	 * Unset key.
	 *
	 * @param mixed $offset key.
	 * @void
	 */
	public function offsetUnset($offset): void
	{
		unset($this->contents[$offset]);
	}

	/**
	 * Get value.
	 *
	 * @param mixed $offset key.
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if (is_callable($this->contents[$offset])) {
			return call_user_func($this->contents[$offset], $this);
		}
		return $this->contents[$offset] ?? null;
	}

	/**
	 * Run container.
	 *
	 * @void
	 */
	public function run()
	{
		foreach ($this->contents as $key => $content) { // Loop on contents.
			if (is_callable($content)) {
				$content = $this[$key];
			}
			if (is_object($content)) {
				$reflection = new \ReflectionClass($content);
				if ($reflection->hasMethod('run')) {
					$content->run(); // Call run method on object.
				}
			}
		}
	}
}

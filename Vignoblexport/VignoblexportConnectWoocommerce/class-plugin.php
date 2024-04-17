<?php

/**
 * Contains code for the plugin container class.
 *
 * @package     Vignoblexport\VignoblexportConnectWoocommerce
 */

namespace Vignoblexport\VignoblexportConnectWoocommerce;

use ArrayAccess;

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
	 * @param string $offset key.
	 * @param mixed  $value value.
	 * @void
	 */
	public function offsetSet(mixed $offset, mixed $value): void
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
	 * @param string $offset key.
	 * @void
	 */
	public function offsetUnset(mixed $offset): void
	{
		unset($this->contents[$offset]);
	}

	/**
	 * Get value.
	 *
	 * @param string $offset key.
	 * @mixed
	 */
	public function offsetGet(mixed $offset): mixed
	{
		if (is_callable($this->contents[$offset])) {
			return call_user_func($this->contents[$offset], $this);
		}
		return isset($this->contents[$offset]) ? $this->contents[$offset] : null;
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

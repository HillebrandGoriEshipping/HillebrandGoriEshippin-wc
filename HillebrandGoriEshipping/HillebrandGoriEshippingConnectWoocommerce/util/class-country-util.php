<?php

/**
 * Contains code for country util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

/**
 * Country util class.
 *
 * Helper to manage consistency between woocommerce versions country getters and setters.
 *
 * @class       Country_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Country_Util
{

	/**
	 * Get activated countries.
	 *
	 * @return array $activated_countries activated countries
	 */
	public static function get_activated_countries()
	{
		static $activated_countries;

		if (null !== $activated_countries) {
			return $activated_countries;
		}

		$activated_countries = new \WC_Countries();
		return $activated_countries;
	}
}

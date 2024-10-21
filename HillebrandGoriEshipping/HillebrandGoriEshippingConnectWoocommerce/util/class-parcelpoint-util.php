<?php

/**
 * Contains code for parcelpoint util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

/**
 * Parcelpoint util class.
 *
 * Helper to manage parcelpoint consistency between plugin's versions
 *
 * @class       Parcelpoint_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Parcelpoint_Util
{

	/**
	 * Normalize a parcel point opening hours
	 *
	 * @param mixed $opening_hours opening hours in standard or non standard format.
	 *
	 * @return mixed opening hours in standard format
	 */
	private static function normalize_opening_hours($opening_hours)
	{
		$result = null;

		if (null !== $opening_hours && is_array($opening_hours)) {
			$result = array();
			foreach ($opening_hours as $opening_hour) {
				$valid_opening_hours = property_exists($opening_hour, 'weekday')
					&& property_exists($opening_hour, 'openingPeriods')
					// phpcs:ignore
					&& is_array($opening_hour->openingPeriods);

				if ($valid_opening_hours) {
					$day_opening_hours = new \stdClass();

					$day_opening_hours->weekday         = $opening_hour->weekday;
					$day_opening_hours->opening_periods = array();
					// phpcs:ignore
					foreach ($opening_hour->openingPeriods as $period) {
						$open = property_exists($period, 'openingTime')
							// phpcs:ignore
							? $period->openingTime
							: (property_exists($period, 'open') ? $period->open : null);
						$close = property_exists($period, 'closingTime')
							// phpcs:ignore
							? $period->closingTime
							: (property_exists($period, 'close') ? $period->close : null);

						$hours        = new \stdClass();
						$hours->open  = $open;
						$hours->close = $close;

						$day_opening_hours->opening_periods[] = $hours;
					}
					$result[] = $day_opening_hours;
				}
			}
		}

		return $result;
	}

	/**
	 * Create a new parcelpoint object
	 *
	 * @param integer $network woocommerce product id.
	 * @param integer $code woocommerce product id.
	 * @param integer $name woocommerce product id.
	 * @param integer $address woocommerce product id.
	 * @param integer $zipcode woocommerce product id.
	 * @param integer $city woocommerce product id.
	 * @param integer $country woocommerce product id.
	 * @param integer $opening_hours woocommerce product id.
	 * @return mixed parcelpoint
	 */
	public static function create_parcelpoint($network, $code, $name, $address, $zipcode, $city, $country, $opening_hours, $distance)
	{
		$point = null;

		if (null !== $network && null !== $code) {
			$point = new \stdClass();

			$point->network       = $network;
			$point->code          = $code;
			$point->name          = $name;
			$point->address       = $address;
			$point->zipcode       = $zipcode;
			$point->city          = $city;
			$point->country       = $country;
			$point->opening_hours = static::normalize_opening_hours($opening_hours);
			$point->distance      = $distance;
		}

		return $point;
	}


	/**
	 * Normalize the point format for retrocompatibility reasons
	 *
	 * Default format   : format used globally in the module after 1.1.9
	 * Old order format : format used in order params in version 1.1.9 and before
	 * Old cart format  : format used in cart session in version 1.1.9 and before
	 * Api format       : format returned by Hillebrand Gori eShipping api
	 *
	 * @param mixed $point in default or old format.
	 *
	 * @return mixed point in default format
	 */
	public static function normalize_parcelpoint($point)
	{
		$result = null;

		if (null !== $point && false !== $point) {
			$has_network       = property_exists($point, 'network');
			$has_code          = property_exists($point, 'code');
			$has_name          = property_exists($point, 'name');
			$has_address       = property_exists($point, 'address');
			$has_zipcode       = property_exists($point, 'zipcode');
			$has_city          = property_exists($point, 'city');
			$has_country       = property_exists($point, 'country');
			$has_opening_hours = property_exists($point, 'opening_hours');
			$has_opening_days  = property_exists($point, 'openingDays');
			$has_distance      = property_exists($point, 'distanceFromSearchLocation');
			$has_location      = property_exists($point, 'location')
				&& property_exists($point->location, 'street')
				&& property_exists($point->location, 'zipCode')
				&& property_exists($point->location, 'city')
				&& property_exists($point->location, 'country');
			$has_parcelpoint   = property_exists($point, 'parcelPoint')
				// phpcs:ignore
				&& property_exists($point->parcelPoint, 'network')
				// phpcs:ignore
				&& property_exists($point->parcelPoint, 'code')
				// phpcs:ignore
				&& property_exists($point->parcelPoint, 'name');

			$is_default_format     = $has_network && $has_code && $has_name && $has_address
				&& $has_zipcode && $has_city && $has_country && $has_opening_hours && ! $has_location;
			$is_old_param_format   = $has_network && $has_code && $has_name && ! $has_address
				&& ! $has_zipcode && ! $has_city && ! $has_country && ! $has_opening_hours && ! $has_location;
			$is_old_session_format = $has_parcelpoint;
			$is_api_format         = $has_network && $has_code && $has_name && ! $has_address
				&& ! $has_zipcode && ! $has_city && ! $has_country && ! $has_opening_hours && $has_location && $has_opening_days;

			if ($is_api_format) {
				$result = static::create_parcelpoint(
					$point->network,
					$point->code,
					$point->name,
					$point->location->street,
					$point->location->zipCode,
					$point->location->city,
					$point->location->country,
					// phpcs:ignore
					$point->openingDays,
					null
				);
			} elseif ($is_default_format) {
				$result = $point;
			} elseif ($is_old_param_format) {
				$result = static::create_parcelpoint(
					$point->network,
					$point->code,
					$point->name,
					'',
					'',
					'',
					'',
					array(),
					null
				);
			} elseif ($is_old_session_format) {
				// phpcs:ignore
				$result = static::normalize_parcelpoint($point->parcelPoint);

				if ($has_distance && $result->distance === null) {
					$result = static::create_parcelpoint(
						$result->network,
						$result->code,
						$result->name,
						$result->address,
						$result->zipcode,
						$result->city,
						$result->country,
						$result->opening_hours,
						// phpcs:ignore
						$point->distanceFromSearchLocation
					);
				}
			}
		}

		return $result;
	}
}

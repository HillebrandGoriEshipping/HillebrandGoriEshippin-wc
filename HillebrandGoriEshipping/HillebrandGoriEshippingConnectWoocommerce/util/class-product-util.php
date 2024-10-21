<?php

/**
 * Contains code for product util class.
 *
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 */

namespace HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util;

/**
 * Product util class.
 *
 * Helper to manage consistency between woocommerce versions product getters and setters.
 *
 * @class       Product_Util
 * @package     HillebrandGoriEshipping\HillebrandGoriEshippingConnectWoocommerce\Util
 * @category    Class
 * @author      Hillebrand Gori eShipping
 */
class Product_Util
{
	/**
	 * Get product weight from product id.
	 *
	 * @param integer $product_id woocommerce product id.
	 * @return mixed $weight
	 */
	public static function get_product_weight($product_id)
	{
		if (isset($product_id) && ! empty($product_id)) {
			$product = self::get_product($product_id);
			if ($product->get_weight() && $product->get_weight() !== '') {
				$weight = wc_format_decimal(wc_get_weight($product->get_weight(), 'kg'), false);
			} else {
				return false;
			}
			return $weight;
		}
	}

	/**
	 * Get WC product price from product id.
	 *
	 * @param integer $product_id woocommerce product id.
	 * @return float|false
	 */
	public static function get_product_price($product_id)
	{
		if (isset($product_id) && ! empty($product_id)) {
			$product = self::get_product($product_id);
			return self::get_price($product);
		}
		return false;
	}

	/**
	 * Get product description.
	 *
	 * @param WC_Order_Item $item woocommerce order item.
	 * @return string $description
	 */
	public static function get_product_description($item)
	{
		$variation_id = $item['variation_id'];
		$check_id     = ('0' === $variation_id || 0 === $variation_id) ? $item['product_id'] : $variation_id;
		$product      = self::get_product($check_id);
		$description  = self::get_name($product);
		// add attributes to title for variations.
		$product_type = self::get_product_type($product);
		if ('variation' === $product_type) {
			$parent_id      = self::get_parent_id($product);
			$parent_product = self::get_product($parent_id);
			$parent_product_type = self::get_product_type($parent_product);
			if ('variation' === $parent_product_type) {
				foreach ($parent_product->get_available_variations() as $variation) {
					if ($variation['variation_id'] === (int) $check_id) {
						foreach ($variation['attributes'] as $attributes) {
							$description .= ' ' . $attributes;
						}
					}
				}
			}
		}
		return $description;
	}

	/**
	 * Get product type. Fix for WC > 3.1.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @return string $product_type
	 */
	private static function get_product_type($product)
	{
		if (method_exists($product, 'get_type')) {
			return $product->get_type();
		} else {
			return $product->product_type;
		}
	}

	/**
	 * Get WC product from product id. Fix for WC > 2.5.
	 *
	 * @param integer $product_id woocommerce product id.
	 * @return mixed $product
	 */
	public static function get_product($product_id)
	{
		if (function_exists('wc_get_product')) {
			$product = wc_get_product($product_id);
		} else {
			// fix for WC < 2.5.
			$product = WC()->product_factory->get_product($product_id);
		}
		return $product;
	}

	/**
	 * Set WC product weight.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @param float             $weight desired weight.
	 * @void
	 */
	public static function set_weight($product, $weight)
	{
		if (method_exists($product, 'set_weight')) {
			$product->set_weight($weight);
		} else {
			update_post_meta(self::get_id($product), '_weight', $weight);
		}
	}

	/**
	 * Get WC product price.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @return float
	 */
	public static function get_price($product)
	{
		if (method_exists($product, 'get_price')) {
			return (float) $product->get_price();
		}
		return (float) $product->price;
	}

	/**
	 * Set WC product price.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @param float             $price desired price.
	 * @void
	 */
	public static function set_price($product, $price)
	{
		if (method_exists($product, 'set_price')) {
			$product->set_price($price);
		}
		update_post_meta(self::get_id($product), '_price', $price);
	}

	/**
	 * Set WC product regular price.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @param float             $price desired price.
	 * @void
	 */
	public static function set_regular_price($product, $price)
	{
		if (method_exists($product, 'set_regular_price')) {
			$product->set_regular_price($price);
		}
		update_post_meta(self::get_id($product), '_regular_price', $price);
	}

	/**
	 * Get product id.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @return string $id
	 */
	public static function get_id($product)
	{
		if (method_exists($product, 'get_id')) {
			return $product->get_id();
		}
		return $product->id;
	}

	/**
	 * Get product name.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @return string $name
	 */
	public static function get_name($product)
	{
		if (method_exists($product, 'get_name')) {
			return $product->get_name();
		}
		return $product->name;
	}

	/**
	 * Set WC product name.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @param string            $name desired name.
	 * @void
	 */
	public static function set_name($product, $name)
	{
		if (method_exists($product, 'set_name')) {
			$product->set_name($name);
		} else {
			update_post_meta($product->id, '_name', $name);
		}
	}

	/**
	 * Get WC product variation parent id.
	 *
	 * @param WC variation $variation woocommerce product variation.
	 * @return string $id
	 */
	public static function get_parent_id($variation)
	{
		if (method_exists($variation, 'get_parent_id')) {
			return $variation->get_parent_id();
		}
		return $variation->parent->id;
	}

	/**
	 * Save WC product.
	 *
	 * @param WC_Product_Simple $product woocommerce product.
	 * @void
	 */
	public static function save($product)
	{
		if (method_exists($product, 'save')) {
			$product->save();
		}
	}
}

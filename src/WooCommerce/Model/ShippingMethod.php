<?php

namespace HGeS\WooCommerce\Model;

use HGeS\Rate;

class ShippingMethod extends \WC_Shipping_Method
{
    const METHOD_ID = 'hges_shipping';
    const METHOD_TITLE = 'Hillebrand Gori eShipping';
    const METHOD_DESCRIPTION = 'Hillebrand Gori eShipping Shipping Method';
    const ENABLED = 'yes';
    const SUPPORTS = ['shipping-zones'];

    public $id;
    public $instance_id = 0;
    public $method_title;
    public $method_description;
    public $enabled;
    public $title;
    public $supports = [];

    /**
     * Constructor for the shipping method
     * 
     * @param int $instance_id
     */
    public function __construct(int $instance_id = 0)
    {
        $this->id = self::METHOD_ID;
        $this->instance_id = $instance_id;
        $this->method_title = self::METHOD_TITLE;
        $this->method_description = __(self::METHOD_DESCRIPTION, 'hges');
        $this->enabled = self::ENABLED;
        $this->title = self::METHOD_TITLE;
        $this->supports = self::SUPPORTS;
    }

    /**
     * Initialize the shipping method
     */
    public static function init(): void
    {
        add_filter('woocommerce_shipping_methods', [self::class, 'register']);
    }

    /**
     * Register the shipping method with WooCommerce
     * 
     * @param array $methods
     * @return array
     * @see https://woocommerce.github.io/code-reference/classes/WC-Shipping-Method.html#property_methods
     */
    public static function register(array $methods): array
    {
        $methods[self::METHOD_ID] = self::class;
        return $methods;
    }

    /**
     * Calculate the shipping rates for the given package
     * 
     * @param array $package
     * @return void
     * @see https://woocommerce.github.io/code-reference/classes/WC-Shipping-Method.html#method_calculate_shipping
     */
    public function calculate_shipping($package = [])
    {
        try {
           $rates = Rate::getShippingRates($package);
           foreach ($rates as $rate) {
               $this->add_rate($rate);
           }
        } catch (\Exception $e) {
            error_log('Error calculating shipping: ' . $e->getMessage());
            \Sentry\captureException($e);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                throw new \Exception('Error calculating shipping: ' . $e->getMessage());
            }
        } 
    }
}

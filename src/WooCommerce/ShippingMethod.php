<?php

namespace HGeS\WooCommerce;

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
<<<<<<< HEAD
        $rates = Rate::getShippingRates($package);
        foreach ($rates as $rate) {
            $this->add_rate($rate);
        }
=======
        //TODO: Implement the logic to calculate shipping rates based on the package details
        $this->add_rate([
            'id'    => $this->id . uniqid(),
            'label' => 'Door delivery',
            'cost' => 11.00,
            'meta_data' => [
                'eta' => '24/08/2029'
            ]
        ]);

        $this->add_rate([
            'id'    => $this->id . uniqid(),
            'label' => 'Pickup',
            'cost' => 8.35,
            'meta_data' => [
                'eta' => '24/08/2028'
            ]

        ]);
>>>>>>> 9f084f9 (tests overriding checkout shipping block)
    }
}

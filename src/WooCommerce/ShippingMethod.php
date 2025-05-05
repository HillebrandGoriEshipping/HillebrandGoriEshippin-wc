<?php

namespace HGeS\WooCommerce;

use HGeS\Rate;

class ShippingMethod extends \WC_Shipping_Method
{
    const METHOD_ID = 'hges_shipping';
    const METHOD_TITLE = 'Hillebrand Gori eShipping';
    const METHOD_DESCRIPTION = 'Hillebrand Gori eShipping Shipping Method';
    const ENABLED = 'yes';

    public $id;
    public $method_title;
    public $method_description;
    public $enabled;
    public $title;
    public $supports = [];
    public $instance_id = '';

    public function __construct($instance_id = 0)
    {
        $this->id = self::METHOD_ID;
        $this->instance_id = $instance_id;
        $this->method_title = self::METHOD_TITLE;
        $this->method_description = __(self::METHOD_DESCRIPTION, 'hges');
        $this->enabled = self::ENABLED;
        $this->title = self::METHOD_TITLE;
        $this->supports[] = 'shipping-zones';
    }

    public static function register($methods)
    {
        $methods[self::METHOD_ID] = self::class;
        return $methods;
    }

    public function calculate_shipping($package = [])
    {
        $rates = Rate::getShippingRates($package);
        foreach ($rates as $rate) {
            $this->add_rate($rate);
        }
    }
}

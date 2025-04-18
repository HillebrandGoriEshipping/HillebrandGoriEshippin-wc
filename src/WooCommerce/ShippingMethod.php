<?php

namespace HGeS\WooCommerce;

class ShippingMethod extends \WC_Shipping_Method
{
    const METHOD_ID = 'hges_shipping';
    const METHOD_TITLE = 'Hillebrand Gori eShipping';
    const METHOD_DESCRIPTION = 'Hillebrand Gori eShipping Shipping Method';
    const ENABLED = 'yes';

    public function __construct()
    {
        $this->id = self::METHOD_ID;
        $this->method_title = self::METHOD_TITLE;
        $this->method_description = __(self::METHOD_DESCRIPTION, 'hges');
        $this->enabled = 'yes';
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
        // Calculate shipping rates here
    }
}

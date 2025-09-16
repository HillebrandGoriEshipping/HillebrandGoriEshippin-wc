<?php

namespace HGeS;

use HGeS\WooCommerce\ShippingAddressFields;

class LifeCycle
{
    /**
     * Handles actions to perform when the plugin is activated.
     *
     * @return void
     */
    public static function onPluginActivation(): void
    {
        ShippingAddressFields::makePhoneRequired();
    }
}

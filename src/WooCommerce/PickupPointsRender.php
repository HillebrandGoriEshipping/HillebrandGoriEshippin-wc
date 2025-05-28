<?php

namespace HGeS\WooCommerce;

use Automattic\WooCommerce\Admin\Overrides\Order as WooCommerceOrder;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Order;
/**
 * Class PickupPointsRender
 * 
 * This class is responsible for rendering all the hookable template parts related to pickup points.
 */
class PickupPointsRender
{
    /**
     * Render the pickup points in the order confirmation page.
     *
     * @param array $pickupPoints
     * @return string
     */
    public static function renderOrderConfirmation(string $address, array $rawAddress, WooCommerceOrder $order): string
    {
        if (!$order->get_meta(Order::PICKUP_POINT_META_KEY, true)) {
            return $address;
        }
        $shippingMethodName = $order->get_shipping_method();
        return Twig::getTwig()->render('checkout/confirm-shipping-address-pickuppoint.twig',
            [
                'pickupPoint' => $order->get_meta(Order::PICKUP_POINT_META_KEY, true),
                'shippingMethodName' => $shippingMethodName,
            ]
        );
    }
}
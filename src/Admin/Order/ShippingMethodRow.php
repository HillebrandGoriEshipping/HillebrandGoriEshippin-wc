<?php

namespace HGeS\Admin\Order;

use HGeS\Rate;
use HGeS\Utils\Messages;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Model\ShippingMethod;

class ShippingMethodRow {
    
    /**
     * Initialize the admin hooks and filters for the shipping method row
     */
    public static function initAdmin() {
        add_action('woocommerce_before_order_itemmeta', [self::class, 'beforeOrderItemMeta'], 10, 3);
        add_action('woocommerce_after_order_itemmeta', [self::class, 'afterOrderItemMeta'], 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', [self::class, 'hiddenOrderItemMeta'], 10, 1);
    }

    /**
     * Displays the shipping method edition button
     * triggered by the 'woocommerce_before_order_itemmeta' action
     */
    public static function beforeOrderItemMeta($item_id, $item, $order = null) {
        if (
            get_class($item) !== 'WC_Order_Item_Shipping' 
            || $item->get_data()['method_id'] !== ShippingMethod::METHOD_ID
        ) {
            return;
        }
        $shippingRateChecksumMeta = array_find($item->get_meta_data(), function (\WC_Meta_Data $meta) {
            return $meta->key === 'checksum';
        });
        $shippingRateChecksum = $shippingRateChecksumMeta ? $shippingRateChecksumMeta->value : null;
        
        $shippingMethodStillAvailable = false;
        if ($shippingRateChecksum) {
            $shippingRate = Rate::getByChecksum($shippingRateChecksum);
            if ($shippingRate) {
                $shippingMethodStillAvailable = true;
            }
        } else {
            $shippingMethodStillAvailable = false;
        }

        
        $templateData = [
            'errorMessage' => Messages::getMessage('orderAdmin')['shippingRateNotAvailable'],
            'stillAvailable' => $shippingMethodStillAvailable,
            'shippingRate' => $shippingRate ?? null,
        ];

        echo Twig::getTwig()->render('admin/order/shipping-method-row.twig', $templateData);

        echo '<div style="display: none">';
    }

    /**
     * Closes the div opened in beforeOrderItemMeta
     * triggered by the 'woocommerce_after_order_itemmeta' action
     */
    public static function afterOrderItemMeta($item_id, $item, $order) {
        echo '</div>';
    }

    /**
     * Hide all the meta data related to the shipping method so they can't be edited one by one
     * triggered by the 'woocommerce_hidden_order_itemmeta' filter
     */
    public static function hiddenOrderItemMeta($hidden) {
        $hidden[] = 'deliveryDate';
        $hidden[] = 'pickupDate';
        $hidden[] = 'insurancePrice';
        $hidden[] = 'carrier';
        $hidden[] = 'deliveryMode';
        $hidden[] = 'firstPickupDelivery';
        return $hidden;
    }
}
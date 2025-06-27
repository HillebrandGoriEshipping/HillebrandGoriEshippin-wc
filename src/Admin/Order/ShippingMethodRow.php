<?php

namespace HGeS\Admin\Order;

use HGeS\Rate;
use HGeS\Utils\Messages;
use HGeS\Utils\Twig;
use HGeS\WooCommerce\Model\Order;
use HGeS\WooCommerce\Model\ShippingMethod;

/**
 * Handles the display of the shipping method row in the WooCommerce order admin page
 */
class ShippingMethodRow {
    
    /**
     * Initialize the admin hooks and filters for the shipping method row
     */
    public static function initAdmin()
    {
        add_action('woocommerce_before_order_itemmeta', [self::class, 'beforeOrderItemMeta'], 10, 3);
        add_action('woocommerce_after_order_itemmeta', [self::class, 'afterOrderItemMeta'], 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', [self::class, 'hiddenOrderItemMeta'], 10, 1);
    }

    /**
     * Displays the shipping method edition button
     * triggered by the 'woocommerce_before_order_itemmeta' action
     * @param int $item_id The item ID
     * @param \WC_Order_Item $item The order item object
     * @param \WC_Product|null $product The product object, if available
     * 
     * @return void
     */
    public static function beforeOrderItemMeta(int $item_id, \WC_Order_Item $item, ?\WC_Product $product = null): void
    {
       
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
            'itemId' => $item_id,
        ];

        echo Twig::getTwig()->render('admin/order/shipping-method-row.twig', $templateData);

        echo '<div style="display: none">';
    }

    /**
     * Closes the div opened in beforeOrderItemMeta
     * triggered by the 'woocommerce_after_order_itemmeta' action
     * 
     * @param int $item_id The item ID
     * @param \WC_Order_Item $item The order item object
     * @param \WC_Product|null $product The product object, if available
     * @return void
     */
    public static function afterOrderItemMeta(int $item_id, \WC_Order_Item $item, ?\WC_Product $product = null): void
    {
        echo '</div>';
    }

    /**
     * Hide all the meta data related to the shipping method so they can't be edited one by one
     * triggered by the 'woocommerce_hidden_order_itemmeta' filter
     * 
     * @param array $hidden The array of hidden meta keys
     * @return array The modified array of hidden meta keys
     */
    public static function hiddenOrderItemMeta(array $hidden): array
    {
        $hidden[] = 'deliveryDate';
        $hidden[] = 'pickupDate';
        $hidden[] = 'insurancePrice';
        $hidden[] = 'carrier';
        $hidden[] = 'deliveryMode';
        $hidden[] = 'firstPickupDelivery';
        return $hidden;
    }
}
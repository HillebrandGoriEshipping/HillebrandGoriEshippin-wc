<?php

namespace HGeS\Admin\Order;

use HGeS\Rate;
use HGeS\Utils\Messages;
use HGeS\Utils\Packaging;
use HGeS\Utils\RateHelper;
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
        add_action('woocommerce_admin_order_totals_after_shipping', [self::class, 'afterCartItems'], 10, 1);
    }

    public static function afterCartItems(int $orderId): void
    {
        $item = Order::getShippingOrderItem($orderId);
        
        if (!$item) {
            return;
        }       

        $shippingRateChecksum = Order::getShippingRateChecksum($orderId);

        if ($shippingRateChecksum) {

            $shippingMethodStillAvailable = Rate::isStillAvailable($shippingRateChecksum);
            $attachments = Order::getAttachmentList($orderId);
            
            try {
                $shippingRate = Rate::getByChecksum($shippingRateChecksum);
            } catch (\Exception $e) {
                $shippingRate = null;
            }
        } else {
            $shippingMethodStillAvailable = false;
            $shippingRate = null;
        }
        
        if ($shippingRate) {
            $remainingAttachments = array_filter($shippingRate->getRequiredAttachments() ?? [], function ($requiredAttachment) use ($attachments) {
                $requiredAttachmentType = $requiredAttachment['type'] ?? '';
                return !in_array($requiredAttachmentType, array_column($attachments, 'type'));
            });
            // reinit array indexes to avoid gaps in the array
            $remainingAttachments = array_values($remainingAttachments);
        } else {
            $remainingAttachments = [];
        }

        $initialSelectedRate = Order::getInitialSelectedRate($orderId);

        if ($shippingRate && $initialSelectedRate && $initialSelectedRate->getChecksum() !== $shippingRateChecksum) {
            $priceDelta = RateHelper::calculateTotal($shippingRate) - RateHelper::calculateTotal($initialSelectedRate);
            
            $plusPrefix = '';
            if ($priceDelta > 0) {
                $plusPrefix = '+';
            }

            $priceDelta = html_entity_decode(wp_strip_all_tags(wc_price($priceDelta)));
            $priceDelta = $plusPrefix . $priceDelta;
            $shippingRate->addMetaData('priceDelta', $priceDelta);
        }
        
        $products = array_map(function ($item) {
            $productPost = $item->get_product();
            if (!$productPost) {
                return null;
            }

            return $productPost->get_data();
        }, wc_get_order($orderId)->get_items());

        $packaging = wc_get_order($orderId)->get_meta(Order::PACKAGING_META_KEY, true);
        $shippingLabelLink = Order::getLabelLink($orderId);

        $templateData = [
            'componentData' => [
                'initialSelectedRate' => $initialSelectedRate ? $initialSelectedRate->toArray() : null,
                'errorMessage' => Messages::getMessage('orderAdmin.shippingRateNotAvailable'),
                'stillAvailable' => $shippingMethodStillAvailable,
                'shippingRate' => !empty($shippingRate) ? $shippingRate->toArray() : null,
                'attachments' => $attachments ?? [],
                'remainingAttachments' => $remainingAttachments,
                'itemId' => $item->get_id(),
                'products' => $products,
                'packaging' => $packaging,
                'shippingLabelLink' => $shippingLabelLink,
            ],
        ];

        echo Twig::getTwig()->render('admin/order/shipping-method-row.twig', $templateData);
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
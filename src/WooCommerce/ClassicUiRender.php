<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Twig;
use WC_Shipping_Rate;

class ClassicUiRender
{

    /**
     * Sorts shipping methods into three categories: door delivery, pickup, and others.
     * Adds metadata to the first shipping method in each category to sort them in the Twig template.
     *
     * @param array $rates  An array of shipping rate objects to be sorted.
     *
     * @return array An array of shipping rates sorted in the order: pickup, door delivery, others.
     *
     */
    public static function sortShippingMethods(array $rates): array
    {
        $doorDeliveryRates = [];
        $pickupRates = [];
        $otherRates = [];
        foreach ($rates as $key => $rate) {
            if (!isset($rate->get_meta_data()['deliveryMode'])) {
                $otherRates[$key] = $rate;
                if (count($otherRates) === 1) {
                    $otherRates[$key]->add_meta_data('firstOthersDelivery', true);
                }
            } else if ($rate->get_meta_data()['deliveryMode'] === 'door') {
                $doorDeliveryRates[$key] = $rate;
                if (count($doorDeliveryRates) === 1) {
                    $doorDeliveryRates[$key]->add_meta_data('firstDoorDelivery', true);
                }
            } else if ($rate->get_meta_data()['deliveryMode'] === 'pickup') {
                $pickupRates[$key] = $rate;
                if (count($pickupRates) === 1) {
                    $pickupRates[$key]->add_meta_data('firstPickupDelivery', true);
                }
            }
        }

        $arraymerge = array_merge(
            $pickupRates,
            $doorDeliveryRates,
            $otherRates
        );

        return $arraymerge;
    }

    /**
     * Renders the shipping label HTML for a given shipping rate.
     *
     * @param string $labelHtml The initial label HTML (unused in the method).
     * @param WC_Shipping_Rate $method The shipping rate object containing metadata and label information.
     * 
     * @return string The rendered shipping label HTML.
     *
     */
    public static function renderLabel(string $labelHtml, WC_Shipping_Rate $rate): string
    {
        $metadata = $rate->get_meta_data();
        if (empty($metadata['carrier'])) {
            $assetsPath = null;
        } else {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/' . $metadata['carrier'] . '.png';
        }

        if ($rate->get_label() === 'Aérien') {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/airfreight.png';
        } else if ($rate->get_label() === 'Maritime') {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/seafreight.png';
        }

        $metadata['deliveryMode'] = $metadata['deliveryMode'] ?? false;

        $locale = get_user_locale();
        $dateFormatMapping = [
            'fr_FR' => 'd/m/Y',
            'en_US' => 'm/d/Y',
            'en_GB' => 'd/m/Y',
            'de_DE' => 'd.m.Y',
            'ja_JP' => 'Y年m月d日',
        ];

        $dateFormat = $dateFormatMapping[$locale] ?? 'Y-m-d';

        $rawDate = $metadata['deliveryDate'] ?? null;

        if ($rawDate) {
            $formattedDate = date_i18n($dateFormat, strtotime($rawDate));
        } else {
            $formattedDate = null;
        }
        $metadata['deliveryDate'] = $formattedDate;

        $twig = Twig::getTwig();
        $labelHtml = $twig->render('shipping-label.twig', [
            'label' => $rate->get_label(),
            'cost' => wc_price($rate->get_cost()),
            'metaData' => $metadata,
            'assetsPath' => $assetsPath,
            'deliveryMode' => $metadata['deliveryMode'],
        ]);

        return $labelHtml;
    }

    /**
     * Renders the classic pickup modal using the Twig templating engine.
     *
     * @return void
     */
    public static function renderClassicPickupModal(): void
    {
        $twig = Twig::getTwig();
        echo $twig->render('classic-pickup-modal.twig');
    }

    /**
     * Invalidates the rate cache for the given shipping packages.
     *
     * @param array $packages An array of shipping packages.
     * @return array The modified array of shipping packages with invalidated rate caches.
     */
    public static function invalidateRatesCache(array $packages): array
    {
        foreach ($packages as &$package) {
            $package['rate_cache'] = wp_rand();
        }

        return $packages;
    }
}

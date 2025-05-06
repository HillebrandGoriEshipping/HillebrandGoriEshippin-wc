<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Twig;

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
    public static function sortShippingMethods($rates)
    {
        $doorDeliveryRates = [];
        $pickupRates = [];
        $otherRates = [];
        foreach ($rates as $key => $rate) {
            if (!isset($rate->get_meta_data()['doorDelivery'])) {
                $otherRates[$key] = $rate;
                if (count($otherRates) === 1) {
                    $otherRates[$key]->add_meta_data('firstOthersDelivery', true);
                }
            } else if ($rate->get_meta_data()['doorDelivery'] === '1') {
                $doorDeliveryRates[$key] = $rate;
                if (count($doorDeliveryRates) === 1) {
                    $doorDeliveryRates[$key]->add_meta_data('firstDoorDelivery', true);
                }
            } else if ($rate->get_meta_data()['doorDelivery'] === '') {
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
     * Renders the shipping label HTML for a given shipping method.
     *
     * @param string $labelHtml The initial label HTML (unused in the method).
     * @param WC_Shipping_Method $method The shipping method object containing metadata and label information.
     * 
     * @return string The rendered shipping label HTML.
     *
     */
    public static function renderLabel($labelHtml, $method)
    {
        $metadata = $method->get_meta_data();
        if (empty($metadata['carrierName'])) {
            $assetsPath = null;
        } else {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/' . $metadata['carrierName'] . '.png';
        }

        if ($method->get_label() === 'Aérien') {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/airfreight.png';
        } else if ($method->get_label() === 'Maritime') {
            $assetsPath = HGeS_PLUGIN_URL . 'assets/img/seafreight.png';
        }

        $metadata['doorDelivery'] = $metadata['doorDelivery'] ?? false;

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
            'label' => $method->get_label(),
            'cost' => wc_price($method->get_cost()),
            'metaData' => $metadata,
            'assetsPath' => $assetsPath,
            'doorDelivery' => $metadata['doorDelivery'],
        ]);

        return $labelHtml;
    }
}

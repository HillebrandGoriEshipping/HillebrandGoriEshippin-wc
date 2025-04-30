<?php

namespace HGeS\WooCommerce;

use HGeS\Utils\Twig;

class ClassicUiRender
{
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

        $twig = Twig::getTwig();
        $labelHtml = $twig->render('shipping-label.twig', [
            'label' => $method->get_label(),
            'cost' => wc_price($method->get_cost()),
            'metaData' => $metadata,
            'assetsPath' => $assetsPath,
        ]);

        return $labelHtml;
    }
}

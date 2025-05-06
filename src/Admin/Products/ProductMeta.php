<?php

namespace HGeS\Admin\Products;

use HGeS\Utils\Twig;

class ProductMeta
{

    public static function customTab($tabs)
    {
        $tabs['HGeS_product_tab'] = [
            'label'    => __('eSHipping Hillebrand Gori product settings', 'woocommerce'),
            'target'   => 'HGeS_product_tab_options',
            'class'    => [],
            'priority' => 21,
        ];
        return $tabs;
    }

    public static function displayProductFields()
    {
        $twig = Twig::getTwig();

        $valeur = get_post_meta(get_the_ID(), '_mon_champ_perso', true);
        $html = $twig->render('product-metas.twig', [
            'valeur' => $valeur,
        ]);

        echo $html;
    }
}

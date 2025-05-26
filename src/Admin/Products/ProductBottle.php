<?php

namespace HGeS\Admin\Products;

use WC_Product_Variable;

class ProductBottle extends WC_Product_Variable
{
    public function getType()
    {
        return 'bottleProduct';
    }

    public static function addToSelect($types)
    {
        $types['bottleProduct'] = __('Bottle Product', 'hges');
        return $types;
    }
}

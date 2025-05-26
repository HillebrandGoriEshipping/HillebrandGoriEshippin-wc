<?php

namespace HGeS\Admin\Products;

use WC_Product_Simple;

class SimpleProductBottle extends WC_Product_Simple
{
    /**
     * Gets the type identifier for the product.
     *
     * @return string Returns the product type as 'simpleBottleProduct'.
     */
    public function getType(): string
    {
        return 'simpleBottleProduct';
    }

    public static function addToSelect($types): array
    {
        $types['simpleBottleProduct'] = __('Simple Bottle Product', 'hges');
        return $types;
    }
}

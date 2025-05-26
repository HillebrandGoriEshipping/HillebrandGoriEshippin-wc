<?php

namespace HGeS\Admin\Products;

use WC_Product_Variable;

class VariableProductBottle extends WC_Product_Variable
{
    /**
     * Gets the type identifier for the product.
     *
     * @return string Returns the product type as 'variableBottleProduct'.
     */
    public function getType(): string
    {
        return 'variableBottleProduct';
    }

    public static function addToSelect($types): array
    {
        $types['variableBottleProduct'] = __('Variable Bottle Product', 'hges');
        return $types;
    }
}

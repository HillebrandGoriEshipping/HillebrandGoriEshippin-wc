<?php

namespace HGeS\Utils;

use HGeS\Dto\RateDto;
use HGeS\Utils\Enums\OptionEnum;

class RateHelper
{   
    /**
     * Calculate the total price from the RateDto object.
     *
     * @param RateDto $rateDto
     * @return float
     * 
     */
    public static function calculateTotal(RateDto $rateDto): float
    {
        $totalPrice = 0.0;
        if ($rateDto->getPrices()) {
            $totalPrice = array_reduce($rateDto->getPrices(), function ($carry, $price) {
                if (empty($price['amountAllIn'])) {
                    return $carry;
                }

                // @TEMP - Do not include insurance price if insurance is not selected
                if (get_option(OptionEnum::HGES_INSURANCE) == "no" && $price['key'] === 'insurance_price') {
                    return $carry;
                }
                return $carry + $price['amountAllIn'];
            }, 0);
        }
        return $totalPrice;
    }
}
<?php

namespace HGeS\Utils;

use HGeS\Utils\Enums\ProductMetaEnum;

class Packaging
{

    public const PACKAGING_BOTTLE = 'bottle';
    public const PACKAGING_MAGNUM = 'magnum';

    public static function getAvailablePackagingOptions(): array
    {
        $allOptions = ApiClient::get('/v2/packages')['data'];

        $packagingBottle = array_filter($allOptions, function ($packagingOption) {
            return in_array($packagingOption['id'], get_option('HGES_PACKAGING_BOTTLE', []));
        });
        $packagingMagnum = array_filter($allOptions, function ($packagingOption) {
            return in_array($packagingOption['id'], get_option('HGES_PACKAGING_MAGNUM', []));
        });

        return [
            self::PACKAGING_BOTTLE => $packagingBottle,
            self::PACKAGING_MAGNUM => $packagingMagnum,
        ];
    }

    public static function calculatePackagingPossibilities(mixed $products): array
    {
        $packagingAvailable = self::getAvailablePackagingOptions();
        usort($packagingAvailable[self::PACKAGING_BOTTLE], function ($a, $b) {
            return $a['itemNumber'] <=> $b['itemNumber'];
        });
        usort($packagingAvailable[self::PACKAGING_MAGNUM], function ($a, $b) {
            return $a['itemNumber'] <=> $b['itemNumber'];
        });

        $packagingAvailable[self::PACKAGING_BOTTLE] = array_reverse($packagingAvailable[self::PACKAGING_BOTTLE]);
        $packagingAvailable[self::PACKAGING_MAGNUM] = array_reverse($packagingAvailable[self::PACKAGING_MAGNUM]);

        foreach ([self::PACKAGING_BOTTLE, self::PACKAGING_MAGNUM] as $packagingType) {

            $nbItems = array_reduce($products, function ($carry, $item) use ($packagingType) {

                $bottleType = get_post_meta($item['product_id'], ProductMetaEnum::CAPACITY_TYPE, true);
                if ($bottleType === $packagingType) {
                    return $carry + $item['quantity'];
                } else {
                    return $carry;
                }
            });

            $packages[$packagingType] = [];
            $delta = 0;
            while ($nbItems > 0) {
                self::makePackaging($packages[$packagingType], $packagingAvailable[$packagingType], $nbItems, $delta);
                $delta++;
            }
        }

        return [
            self::PACKAGING_BOTTLE => $packages[self::PACKAGING_BOTTLE] ?? [],
            self::PACKAGING_MAGNUM => $packages[self::PACKAGING_MAGNUM] ?? [],
        ];
    }

    /**
     * Make packaging from the available packaging options
     */
    public static function makePackaging(&$packages, $packagingAvailable, &$nbItems, $allowedDelta = 0)
    {
        foreach ($packagingAvailable as $packaging) {
            if ($packaging['itemNumber'] > $nbItems + $allowedDelta) {
                continue;
            }

            $nbItems -= $packaging['itemNumber'];
            $packaging['delta'] = $allowedDelta;

            $packages[] = $packaging;
            if ($nbItems == 0) {
                break;
            }
        }
    }
}

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

        $packaging = array_filter($allOptions, function ($packagingOption) {
            $packagingIdsInOption = array_map(function ($option) {
                $option = json_decode(stripcslashes($option), true);
                return $option['id'];
            }, get_option('HGES_PACKAGING_AVAILABLE', []));
            return in_array($packagingOption['id'], $packagingIdsInOption);
        });

        $packagingBottle = array_filter($packaging, function ($packagingOption) {
            return $packagingOption['containerType'] === self::PACKAGING_BOTTLE;
        });

        $packagingMagnum = array_filter($packaging, function ($packagingOption) {
            return $packagingOption['containerType'] === self::PACKAGING_MAGNUM;
        });
        
        return [
            self::PACKAGING_BOTTLE => array_values($packagingBottle),
            self::PACKAGING_MAGNUM => array_values($packagingMagnum),
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

                $bottleCapacity = get_post_meta($item['product_id'], ProductMetaEnum::CAPACITY, true);

                if ($bottleCapacity == 750) {
                    $bottleType = self::PACKAGING_BOTTLE;
                } else if ($bottleCapacity == 1500) {
                    $bottleType = self::PACKAGING_MAGNUM;
                }

                if ($bottleType === $packagingType) {
                    return $carry + $item['quantity'];
                } else {
                    return $carry;
                }
            });
            $packages[$packagingType] = [];
            $delta = 0;

            if ($packagingAvailable[$packagingType] === []) {
                wc_add_notice( __(Messages::getMessage('frontOffice.packagingNotAvailable', ['packagingType' => $packagingType]), 'hges' ), 'error' );
                break;
            }

            while ($nbItems > 0) {
                self::makePackaging($packages[$packagingType], $packagingAvailable[$packagingType], $nbItems, $delta);
                $delta++;
                if ($delta > 10) {
                    throw new \Exception("Too many iterations for packaging type: $packagingType with items left: $nbItems");
                }
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

    /**
     * Get the packaging type for a product based on its capacity
     *
     * @param \WC_Product $product
     * @return string|null Returns the packaging type or null if not applicable
     */
    public static function getProductPackaging(\WC_Product $product): ?string
    {
        $capacity = get_post_meta($product->get_id(), ProductMetaEnum::CAPACITY, true);
        
        if (empty($capacity)) {
            return null;
        }

        return match($capacity) {
            "750" => self::PACKAGING_BOTTLE,
            "1500" => self::PACKAGING_MAGNUM,
            default => null,
        };
    }
}

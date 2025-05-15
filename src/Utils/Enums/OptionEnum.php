<?php

namespace HGeS\Utils\Enums;

use Symfony\Component\Validator\Constraints\NotBlank;

class OptionEnum
{
    const HGES_ACCESS_KEY = "HGES_ACCESS_KEY";
    const ACCESS_KEY_VALIDATE = "access_key_validate";
    const HGES_MAPBOX_ACCESS_KEY = "HGES_MAPBOX_ACCESS_KEY";
    const mapbox_api_key_validate = "mapbox_api_key_validate";
    const HGES_PREF_TRANSP = "HGES_PREF_TRANSP";
    const HGES_TAX_RIGHTS = "HGES_TAX_RIGHTS";
    const HGES_VAT_NUMBER = "HGES_VAT_NUMBER";
    const HGES_VAT_OSS = "HGES_VAT_OSS";
    const HGES_EORI_NUMBER = "HGES_EORI_NUMBER";
    const HGES_FDA_NUMBER = "HGES_FDA_NUMBER";
    const HGES_ASSURANCE = "HGES_ASSURANCE";
    const HGES_NBR_MIN = "HGES_NBR_MIN";
    const HGES_PREP_TIME = "HGES_PREP_TIME";
    const HGES_PREF_DEL = "HGES_PREF_DEL";
    const HGES_MINHOUR = "HGES_MINHOUR";
    const HGES_CUTOFF = "HGES_CUTOFF";
    const HGES_WORKING_DAYS = "HGES_WORKING_DAYS";


    // WP setting group name
    const HGES_SETTINGS_GROUP = "hges_settings_group";

    /**
     * Get the list of options
     * 
     * @return array
     */
    public static function getList(): array
    {
        return [
            self::HGES_ACCESS_KEY,
            self::ACCESS_KEY_VALIDATE,
            self::HGES_MAPBOX_ACCESS_KEY,
            self::mapbox_api_key_validate,
            self::HGES_PREF_TRANSP,
            self::HGES_TAX_RIGHTS,
            self::HGES_VAT_NUMBER,
            self::HGES_VAT_OSS,
            self::HGES_EORI_NUMBER,
            self::HGES_FDA_NUMBER,
            self::HGES_ASSURANCE,
            self::HGES_NBR_MIN,
            self::HGES_PREP_TIME,
            self::HGES_PREF_DEL,
            self::HGES_MINHOUR,
            self::HGES_CUTOFF,
            self::HGES_WORKING_DAYS,
        ];
    }

    /**
     * Return the constraints for a given option
     */
    public static function getConstraints($option): array | null
    {
        $constraints = [
            self::HGES_PREF_TRANSP => [
                new NotBlank(),
            ],
            self::HGES_TAX_RIGHTS => [
                new NotBlank(),
            ],
            self::HGES_VAT_NUMBER => [
                new NotBlank(),
            ],
            self::HGES_VAT_OSS => [
                new NotBlank(),
            ],
            self::HGES_EORI_NUMBER => [
                new NotBlank(),
            ],
            self::HGES_FDA_NUMBER => [
                new NotBlank(),
            ],
            self::HGES_ASSURANCE => [
                new NotBlank(),
            ],
            self::HGES_NBR_MIN => [
                new NotBlank(),
            ],
            self::HGES_PREP_TIME => [
                new NotBlank(),
            ],
            self::HGES_PREF_DEL => [
                new NotBlank(),
            ],
            self::HGES_MINHOUR => [
                new NotBlank(),
            ],
            self::HGES_CUTOFF => [
                new NotBlank(),
            ],
            self::HGES_WORKING_DAYS => [
                new NotBlank(),
            ],
        ];

        return $constraints[$option] ?? null;
    }
}

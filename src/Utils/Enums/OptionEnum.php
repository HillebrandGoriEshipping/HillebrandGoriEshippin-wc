<?php

namespace HGeS\Utils\Enums;

class OptionEnum
{
    const HGES_ACCESS_KEY = "HGES_ACCESS_KEY";
    const access_key_validate = "access_key_validate";
    const HGES_MAPBOX_ACCESS_KEY = "HGES_MAPBOX_ACCESS_KEY";
    const mapbox_api_key_validate = "mapbox_api_key_validate";
    const HGES_PREF_TRANSP = "HGES_PREF_TRANSP";
    const HGES_PREF_STAT = "HGES_PREF_STAT";
    const HGES_TAX_RIGHTS = "HGES_TAX_RIGHTS";
    const HGES_VAT_CHOICE = "HGES_VAT_CHOICE";
    const HGES_VAT_NUMBER = "HGES_VAT_NUMBER";
    const HGES_VAT_OSS = "HGES_VAT_OSS";
    const HGES_EORI_NUMBER = "HGES_EORI_NUMBER";
    const HGES_FDA_NUMBER = "HGES_FDA_NUMBER";
    const HGES_ASSURANCE = "HGES_ASSURANCE";
    const HGES_NBR_MIN = "HGES_NBR_MIN";
    const HGES_PREP_TIME = "HGES_PREP_TIME";
    const HGES_PREF_DEL = "HGES_PREF_DEL";


    // WP setting group name
    const HGES_SETTINGS_GROUP = "hges_settings_group";

    public static function getList()
    {
        return [
            self::HGES_ACCESS_KEY,
            self::access_key_validate,
            self::HGES_MAPBOX_ACCESS_KEY,
            self::mapbox_api_key_validate,
            self::HGES_PREF_TRANSP,
            self::HGES_PREF_STAT,
            self::HGES_TAX_RIGHTS,
            self::HGES_VAT_CHOICE,
            self::HGES_VAT_NUMBER,
            self::HGES_VAT_OSS,
            self::HGES_EORI_NUMBER,
            self::HGES_FDA_NUMBER,
            self::HGES_ASSURANCE,
            self::HGES_NBR_MIN,
            self::HGES_PREP_TIME,
            self::HGES_PREF_DEL,
        ];
    }
}

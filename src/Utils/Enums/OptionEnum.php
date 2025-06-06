<?php

namespace HGeS\Utils\Enums;

use HGeS\Utils\Messages;
use HGeS\Utils\ValidationConstraints\EoriNumber;
use HGeS\Utils\ValidationConstraints\FdaNumber;
use HGeS\Utils\ValidationConstraints\VatNumber;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class OptionEnum implements EnumInterface
{
    const HGES_ACCESS_KEY = "HGES_ACCESS_KEY";
    const ACCESS_KEY_VALIDATE = "access_key_validate";
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
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_TAX_RIGHTS => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_VAT_NUMBER => [
                new VatNumber(),
            ],
            self::HGES_VAT_OSS => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_EORI_NUMBER => [
                new EoriNumber(),
            ],
            self::HGES_FDA_NUMBER => [
                new FdaNumber(),
            ],
            self::HGES_ASSURANCE => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_NBR_MIN => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_PREP_TIME => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_PREF_DEL => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_MINHOUR => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_CUTOFF => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
            self::HGES_WORKING_DAYS => [
                new NotBlank(['message' => Messages::getMessage('settings')['notEmpty']]),
            ],
        ];

        return $constraints[$option] ?? null;
    }

    public static function getSanitizationType(string $option): string | null
    {
        $sanitizationTypes = [
            self::HGES_ACCESS_KEY => 'string',
            self::ACCESS_KEY_VALIDATE => 'string',
            self::HGES_MAPBOX_ACCESS_KEY => 'string',
            self::mapbox_api_key_validate => 'string',
            self::HGES_PREF_TRANSP => 'string',
            self::HGES_TAX_RIGHTS => 'string',
            self::HGES_VAT_NUMBER => 'string',
            self::HGES_VAT_OSS => 'string',
            self::HGES_EORI_NUMBER => 'string',
            self::HGES_FDA_NUMBER => 'string',
            self::HGES_ASSURANCE => 'string',
            self::HGES_NBR_MIN => 'int',
            self::HGES_PREP_TIME => 'int',
            self::HGES_PREF_DEL => 'int',
            self::HGES_MINHOUR => 'int',
            self::HGES_CUTOFF => 'int',
            self::HGES_WORKING_DAYS => 'int',
        ];
        
        return $sanitizationTypes[$option] ?? null;
    }
}

<?php

namespace HGeS\Utils\Enums;

use HGeS\Form\ValidationConstraints\VatNumber;
use HGeS\Form\ValidationConstraints\EoriNumber;
use HGeS\Form\ValidationConstraints\FdaNumber;
use HGeS\Utils\Messages;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Count;

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
    const HGES_INSURANCE = "HGES_INSURANCE";
    const HGES_NBR_MIN = "HGES_NBR_MIN";
    const HGES_PREP_TIME = "HGES_PREP_TIME";
    const HGES_PREF_DEL = "HGES_PREF_DEL";
    const HGES_MINHOUR = "HGES_MINHOUR";
    const HGES_CUTOFF = "HGES_CUTOFF";
    const HGES_WORKING_DAYS = "HGES_WORKING_DAYS";
    const HGES_PACKAGING_BOTTLE = "HGES_PACKAGING_BOTTLE";
    const HGES_PACKAGING_MAGNUM = "HGES_PACKAGING_MAGNUM";


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
            self::HGES_INSURANCE,
            self::HGES_NBR_MIN,
            self::HGES_PREP_TIME,
            self::HGES_PREF_DEL,
            self::HGES_MINHOUR,
            self::HGES_CUTOFF,
            self::HGES_WORKING_DAYS,
            self::HGES_PACKAGING_BOTTLE,
            self::HGES_PACKAGING_MAGNUM,
        ];
    }

    /**
     * Return the constraints for a given option
     */
    public static function getConstraints(string $option): ?array
    {
        $constraints = [
            self::HGES_PREF_TRANSP => [
                new Count(['min' => 1, 'minMessage' => Messages::getMessage('settings')['carrierCountError']]),
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
            self::HGES_INSURANCE => [
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
                new Count(['min' => 1, 'minMessage' => Messages::getMessage('settings')['workingDaysError']]),
            ],
        ];

        return $constraints[$option] ?? null;
    }

    public static function getSanitizationType(string $option): ?string
    {
        $sanitizationTypes = [
            self::HGES_ACCESS_KEY => 'string',
            self::ACCESS_KEY_VALIDATE => 'string',
            self::HGES_PREF_TRANSP => 'array',
            self::HGES_TAX_RIGHTS => 'string',
            self::HGES_VAT_NUMBER => 'string',
            self::HGES_VAT_OSS => 'string',
            self::HGES_EORI_NUMBER => 'string',
            self::HGES_FDA_NUMBER => 'string',
            self::HGES_INSURANCE => 'string',
            self::HGES_NBR_MIN => 'int',
            self::HGES_PREP_TIME => 'int',
            self::HGES_PREF_DEL => 'string',
            self::HGES_MINHOUR => 'string',
            self::HGES_CUTOFF => 'string',
            self::HGES_WORKING_DAYS => 'array',
        ];

        return $sanitizationTypes[$option] ?? null;
    }
}

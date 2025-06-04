<?php

namespace HGeS\Admin\Settings;

use HGeS\Utils\Enums\OptionEnum;
use HGeS\Utils\Traits\Sanitizable;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use HGeS\Utils\Traits\Validable;

/**
 * This class is used to define the data structure for the settings form, 
 * enables the use of Symfony Validator.
 */
class SettingsFormData
{
    /**
     * This trait is used to add validation capabilities to a class.
     * This class must implement the loadValidatorMetadata method to define the validation rules.
     */
    use Validable;

    /**
     * This trait is used to add sanitization capabilities to a class.
     */
    use Sanitizable;

    /**
     * We need to explicitly define the properties here,
     * otherwise they won't be recognized by the Symfony Validator
     */
    private $HGES_ACCESS_KEY;
    private $access_key_validate;
    private $HGES_MAPBOX_ACCESS_KEY;
    private $mapbox_api_key_validate;
    private $HGES_PREF_TRANSP;
    private $HGES_TAX_RIGHTS;
    private $HGES_VAT_NUMBER;
    private $HGES_VAT_OSS;
    private $HGES_EORI_NUMBER;
    private $HGES_FDA_NUMBER;
    private $HGES_ASSURANCE;
    private $HGES_NBR_MIN;
    private $HGES_PREP_TIME;
    private $HGES_PREF_DEL;
    private $HGES_MINHOUR;
    private $HGES_CUTOFF;
    private $HGES_WORKING_DAYS;

    /**
     * The class properties are dynamically created based on the OptionEnum class
     * 
     * @param array $postData the data from the form
     */
    public function __construct(array $postData)
    {
        // Each post data is assigne to the corresponding property if it's defined in OptionEnum
        foreach (OptionEnum::getList() as $option) {
            $this->$option = isset($postData[$option]) ? $postData[$option] : '';
        }
    }

    /**
     * Loads the validation rules from the OptionEnum class
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        foreach (OptionEnum::getList() as $optionName) {
            // each option can have multiple constraints, we need to iterate over them
            $optionConstraints = OptionEnum::getConstraints($optionName);
            if ($optionConstraints === null) {
                continue;
            }
            foreach ($optionConstraints as $constraint) {
                $metadata->addPropertyConstraint($optionName, $constraint);
            }
        }
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
}

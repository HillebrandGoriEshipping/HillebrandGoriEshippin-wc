<?php

namespace HGeS\Assets;

use HGeS\Utils\Enums\OptionEnum;
use Symfony\Component\Validator\Constraint;

/**
 * Class FrontendValidator
 * This class is used to retrieve the validation constraints for the frontend.
 * It extracts the constraints from the different enums available
 */
class FrontendValidator
{
    /**
     * Get the constraints for a specific enum class.
     * The enum class must implement the getList() and getConstraints() methods.
     *
     * @param string $enum The enum class name
     * @return array An associative array of option names and their constraints
     */
    public static function getConstraintsByEnum(string $enum): array
    {
        if (class_exists($enum)) {
            $optionList = $enum::getList();
            $optionConstraints = [];
            foreach($optionList as $option) {
                $constraints = $enum::getConstraints($option);
                if (!$constraints) {
                    $optionConstraints[$option] = false;
                    continue;
                }
                $constraintsWithKeys = [];
                foreach ($constraints as $constraint) {
                    $key = array_pop(explode('\\', get_class($constraint)));
                    $constraintsWithKeys[$key] = json_decode(json_encode($constraint), true);
                }

                $optionConstraints[$option] = $constraintsWithKeys;
            }

            return $optionConstraints;
        } else {
            return [];
        }
    }

    /**
     * Get all the constraints for the frontend.
     * This method retrieves the constraints for all enums that are relevant for the frontend.
     * Here, we list the enums that are used for validation in the frontend by adding a new entry in the $constraints array.
     *
     * @return array An associative array of option names and their constraints
     */
    public static function getAll(): array
    {
        $constraints = [];
        $constraints['settings'] = self::getConstraintsByEnum(OptionEnum::class);

        return $constraints;
    }

}
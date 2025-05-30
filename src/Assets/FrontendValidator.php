<?php

namespace HGeS\Assets;

use HGeS\Utils\Enums\OptionEnum;
use Symfony\Component\Validator\Constraint;

class FrontendValidator
{
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

    public static function getAll(): array
    {
        $constraints = [];
        $constraints['settings'] = self::getConstraintsByEnum(OptionEnum::class);

        return $constraints;
    }

}
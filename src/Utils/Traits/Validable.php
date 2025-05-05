<?php

namespace HGeS\Utils\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * Trait Validable
 * This trait is used to add validation capabilities to a class.
 * It uses the Symfony Validator component to validate the class properties
 * based on the constraints defined in the OptionEnum class.
 * 
 * The class must implement the loadValidatorMetadata method to define the validation rules.
 */
Trait Validable
{
    /**
     * Used to trigger the validation process from the controller
     */
    public function validate(): array 
    {
        $validator = Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
        return self::formatErrors($validator->validate($this));
    }

    public static function formatErrors(ConstraintViolationListInterface $errors): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formattedErrors;
    }
}
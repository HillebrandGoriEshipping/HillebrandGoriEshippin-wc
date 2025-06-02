<?php

namespace HGeS\Utils\ValidationConstraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VATConstraintValidator extends ConstraintValidator
{
    /**
     * Validates the VAT number against the defined pattern.
     * This method checks if the value is not null or empty before applying the parent validation logic.
     *
     * @param mixed $value The value to validate
     * @param Constraint $constraint The constraint for validation
     *
     * @return void
     */
     
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!$constraint instanceof VATConstraint) {
            throw new \UnexpectedValueException(sprintf('Expected argument of type "%s", "%s" given', VATConstraint::class, get_class($constraint)));
        }

        if (!preg_match($constraint->pattern, $value, $matches)) {
            $this->context->buildViolation($constraint->message)
                ->setCode(VATConstraint::PATTERN)
                ->addViolation();
        }
    }
}
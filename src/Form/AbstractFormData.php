<?php

namespace HGeS\Form;

use HGeS\Utils\Traits\Sanitizable;
use HGeS\Utils\Traits\Validable;

class AbstractFormData
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


    public function __get($name): mixed
    {
        if (!property_exists($this, $name)) {
            return null;
        }
        
        return $this->$name;
    }

}
<?php

namespace HGeS\Utils\ValidationConstraints;

use HGeS\Utils\Messages;

class VATConstraint extends \Symfony\Component\Validator\Constraints\Regex
{
    public const PATTERN = '/^([A-Z]{2})([0-9A-Z]{8,12})$/';

    public function __construct($options = null)
    {
        parent::__construct([
            'pattern' => self::PATTERN,
            'message' => Messages::getMessage('settings')['vatNumberError'],
            'htmlPattern' => self::PATTERN,
        ]);
    }
}
<?php

namespace HGeS\Form\ValidationConstraints;

use HGeS\Utils\Messages;

class EoriNumber extends \Symfony\Component\Validator\Constraints\Regex
{
    public const PATTERN = '/^([A-Z]{2})([0-9A-Z]{5,15})$/';

    public function __construct($options = null)
    {
        parent::__construct([
            'pattern' => self::PATTERN,
            'message' => Messages::getMessage('settings')['eoriNumberError'],
            'htmlPattern' => self::PATTERN,
        ]);
    }
}
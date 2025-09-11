<?php

namespace HGeS\Form\ValidationConstraints;

use HGeS\Utils\Messages;

class FdaNumber extends \Symfony\Component\Validator\Constraints\Regex
{
    public const PATTERN = '/^([0-9A-Z]{11})$/';

    public function __construct($pattern = self::PATTERN, $message = null, $htmlPattern = self::PATTERN)
    {
        parent::__construct(
            pattern: $pattern,
            message: $message ?? Messages::getMessage('settings.fdaNumberError'),
            htmlPattern: $htmlPattern,
        );
    }
}

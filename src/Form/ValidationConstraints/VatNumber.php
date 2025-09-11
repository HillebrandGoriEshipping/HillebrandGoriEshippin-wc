<?php

namespace HGeS\Form\ValidationConstraints;

use HGeS\Utils\Messages;
use Symfony\Component\Validator\Attribute\HasNamedArguments;

class VatNumber extends \Symfony\Component\Validator\Constraints\Regex
{
    public const PATTERN = '/^([A-Z]{2})([0-9A-Z]{8,12})$/';


    #[HasNamedArguments]
    public function __construct($pattern = self::PATTERN, $message = null, $htmlPattern = self::PATTERN)
    {
        parent::__construct(
            pattern: $pattern,
            message: $message ?? Messages::getMessage('settings.vatNumberError'),
            htmlPattern: $htmlPattern,
        );
    }
}

<?php

namespace HGeS\Utils\Traits;

Trait Sanitizable
{
    public function sanitize(string $enumClass): void
    {
        foreach ($this as $property => $value) {
            // you can find the wordpress sanitization functions here:
            // https://developer.wordpress.org/apis/security/sanitizing/
            $this->$property = match ($enumClass::getSanitizationType($property)) {
                'string' => sanitize_text_field($value),
                'html' => wp_kses_post($value),
                'int' => intval($value),
                'float' => floatval($value),
                'email' => sanitize_email($value),
                default => $value,
            };
        }
    }
}
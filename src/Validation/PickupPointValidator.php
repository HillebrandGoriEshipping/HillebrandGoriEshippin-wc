<?php

namespace HGeS\Validation;

use InvalidArgumentException;
use RuntimeException;

class PickupPointValidator
{
    public static function validate(array $data): array
    {
        $schema = [
            'id'                          => 'string',
            'name'                        => 'string',
            'addLine1'                    => 'string',
            'city'                        => 'string',
            'zipCode'                     => 'string',
            'country'                     => 'string',
            'latitude'                    => 'float',
            'longitude'                   => 'float',
            'distance'                    => 'float',
            'distanceUnitOfMeasurement'   => 'string',
            'openingHours'                => 'string',
        ];

        foreach ($schema as $key => $type) {
            if (!array_key_exists($key, $data)) {
                throw new InvalidArgumentException("Missing key: $key");
            }
        }

        $unexpected = array_diff(array_keys($data), array_keys($schema));
        if (!empty($unexpected)) {
            throw new InvalidArgumentException(
                'Unexpected keys: ' . implode(', ', $unexpected)
            );
        }

        $clean = [];
        foreach ($schema as $key => $type) {
            $value = $data[$key];

            switch ($type) {
                case 'string':
                    $clean[$key] = htmlspecialchars(
                        wp_strip_all_tags(trim((string)$value)),
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    break;

                case 'float':
                    if (!is_numeric($value)) {
                        throw new InvalidArgumentException("Invalid float for key: $key");
                    }
                    $clean[$key] = (float)$value;
                    break;

                default:
                    throw new RuntimeException("Unknown type in schema: $type");
            }
        }

        return $clean;
    }
}
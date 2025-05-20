<?php

namespace HGeS\Utils;

class Messages
{
    // Create a PHP class that provides a `getMessage(messageKey)` from a json file based on a given `messageKey`.
    //This JSON file must contain all the frontend messages and be also usable in the javascript files.
    public static function getMessage(string $messageKey): string
    {
        $jsonFilePath = HGeS_PLUGIN_DIR . '/assets/js/config/messages.json';

        $jsonContent = file_get_contents($jsonFilePath);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read JSON file: " . $jsonFilePath);
        }

        $messages = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON: " . json_last_error_msg());
        }

        if (isset($messages[$messageKey])) {
            return $messages[$messageKey];
        } else {
            throw new \Exception("Message key not found: " . $messageKey);
        }
    }

    public static function getMessageList(): array
    {
        $jsonFilePath = HGeS_PLUGIN_DIR . '/assets/js/config/messages.json';

        $jsonContent = file_get_contents($jsonFilePath);
        if ($jsonContent === false) {
            throw new \Exception("Failed to read JSON file: " . $jsonFilePath);
        }

        $messages = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to decode JSON: " . json_last_error_msg());
        }

        if (!is_array($messages)) {
            throw new \Exception("Invalid JSON format: Expected an array.");
        }

        return $messages;
    }
}

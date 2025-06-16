<?php

namespace HGeS\Utils;


/**
 * Class Messages
 *
 * Utility class for retrieving message strings from a JSON configuration file.
 * Provides static methods to fetch individual messages by key or retrieve the entire list of messages.
 */
class Messages
{
    /**
     * Retrieves a message string from a JSON configuration file based on the provided message key.
     *
     * @param string $messageKey The key identifying the message to retrieve.
     * @return string The message string associated with the provided key.
     * @throws \Exception If the JSON file cannot be read, decoded, or if the message key is not found.
     */
    public static function getMessage(string $messageKey): array | string
    {
        $jsonFilePath = HGES_MESSAGES_JSON_PATH;

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

    /**
     * Retrieves a list of messages from a JSON configuration file.
     *
     * @throws \Exception If the JSON file cannot be read, if the JSON cannot be decoded,
     *                    or if the decoded content is not an array.
     *
     * @return array The list of messages as an associative array.
     */
    public static function getMessageList(): array
    {
        $jsonFilePath = HGES_MESSAGES_JSON_PATH;

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

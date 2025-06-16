<?php

namespace HGeS\Utils;

/**
 * Class FormSessionMessages
 * This class is used to manage session messages for forms.
 * It allows setting and getting messages in the session.
 */
class FormSessionMessages
{
    /**
     * add a single message to the session
     * 
     * @param string $type the type of message (e.g. 'error', 'success')
     * @param string $key the key to identify the message
     * @param string $message the message to be stored
     */
    public static function setMessage(string $type, string $key, string $message): void
    {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['form-messages'][$type][$key] = $message;
    }
    
    /**
     * add multiple messages to the session
     * 
     * @param string $type the type of messages to be stored
     * @param array $messages an associative array of messages to be stored
     */
    public static function setMessages(string $type, array $messages): void
    {
        if (!session_id()) {
            session_start();
        }
        foreach ($messages as $key => $message) {
            self::setMessage($type, $key, $message);
        }
    }
    
    /**
     * get and clear messages from the session
     * 
     * @param string $key the key to identify the messages
     * @param string|null $type the type of messages to be retrieved (all if null)
     * @return array the messages stored in the session
     */
    public static function getMessages($type = null): array
    {
        if (!session_id()) {
            session_start();
        }

        if ($type !== null) {
            if (isset($_SESSION['form-messages'][$type])) {
                $messages = $_SESSION['form-messages'][$type];
                unset($_SESSION['form-messages'][$type]);
                return $messages;
            } else {
                return [];
            }
        }

        unset($_SESSION['form-messages']);
        return [];
    }
}

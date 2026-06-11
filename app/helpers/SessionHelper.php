<?php

namespace App\Helpers;

/**
 * SessionHelper
 *
 * Helper class for session and flash message management.
 */
class SessionHelper
{
    public static function hasFlashMessage()
    {
        return self::hasValue('flash_message');
    }
    /**
     * Set a flash message in session
     * @param string $message
     * @param string $type
     * @return void
     */
    public static function setFlashMessage($message, $type = 'info')
    {
        $_SESSION['flash_message'] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Retrieve and remove the flash message from session
     * @return array|null
     */
    public static function getFlashMessage()
    {
        if (self::hasFlashMessage()) {
            $msg = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $msg;
        }
        return null;
    }


    /**
     * Set a value in session by key
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setValue($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a value from session by key
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public static function getValue($key, $default = null)
    {
        return self::hasValue($key) ? $_SESSION[$key] : $default;
    }

    /**
     * Remove a key from session
     * @param string $key
     * @return void
     */
    public static function removeValue($key)
    {
        if (self::hasValue($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Check if a key exists in session
     * @param string $key
     * @return bool
     */
    public static function hasValue($key)
    {
        return isset($_SESSION[$key]);
    }
}

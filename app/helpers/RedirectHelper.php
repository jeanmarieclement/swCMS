<?php

namespace App\Helpers;

/**
 * RedirectHelper
 *
 * Helper class for HTTP redirects.
 */
class RedirectHelper
{
    /**
     * Redirect to a given URL
     * @param string $url
     * @return void
     */
    public static function redirect($url)
    {
        // Ensure session data is written before redirect
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Location: ' . $url, true, 303);
        exit;
    }

    /**
     * Redirect only to a local path, falling back when the URL is external.
     * Use for redirect targets taken from user input (prevents open redirect).
     * @param string $url
     * @param string $fallback
     * @return void
     */
    public static function redirectLocal($url, $fallback = '/')
    {
        // Accept only site-relative paths: must start with a single '/'
        // ('//host' and '/\host' are protocol-relative URLs, %2F hides slashes)
        $decoded = rawurldecode((string) $url);
        if (
            $decoded === '' ||
            $decoded[0] !== '/' ||
            (isset($decoded[1]) && ($decoded[1] === '/' || $decoded[1] === '\\'))
        ) {
            $decoded = $fallback;
        }
        self::redirect($decoded);
    }
}

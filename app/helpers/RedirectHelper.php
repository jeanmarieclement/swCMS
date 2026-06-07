<?php
namespace App\Helpers;

/**
 * RedirectHelper
 * 
 * Helper class for HTTP redirects.
 */
class RedirectHelper {
    /**
     * Redirect to a given URL
     * @param string $url
     * @return void
     */
    public static function redirect($url) {
        // Ensure session data is written before redirect
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        header('Location: ' . $url, true, 303);
        exit;
    }
}

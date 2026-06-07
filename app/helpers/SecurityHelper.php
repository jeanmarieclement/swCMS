<?php
namespace App\Helpers;

use App\Helpers\SessionHelper;

/**
 * SecurityHelper
 * 
 * Helper class for security-related functions.
 */
class SecurityHelper {

    /**
     * Generate or retrieve the CSRF token
     * @return string
     */
    public static function csrf_token() {
        if (!SessionHelper::hasValue('csrf_token')) {
            SessionHelper::setValue('csrf_token', bin2hex(random_bytes(32)));
        }
        return SessionHelper::getValue('csrf_token');
    }

    /**
     * Generate a hidden CSRF field for forms
     * @return string
     */
    public static function csrf_field() {
        $token = self::csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Verify a CSRF token
     * @param string $token
     * @return bool
     */
    public static function verify_csrf_token($token) {
        return SessionHelper::hasValue('csrf_token') && hash_equals(SessionHelper::getValue('csrf_token'), $token);
    }

    /**
     * Sanitize output for HTML
     * @param string $data
     * @return string
     */
    public static function sanitize($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeHtml($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', ENT_HTML5);
    }
}

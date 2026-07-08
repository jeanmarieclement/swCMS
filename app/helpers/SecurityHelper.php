<?php

namespace App\Helpers;

use App\Helpers\SessionHelper;

/**
 * SecurityHelper
 *
 * Helper class for security-related functions.
 *
 * The csrf_* methods keep their snake_case names on purpose: they are part of
 * the public template/plugin API (WordPress-style), renaming them would break
 * existing themes and plugins.
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class SecurityHelper
{
    /**
     * Generate or retrieve the CSRF token
     * @deprecated Delegates to CSRFHelper::getToken() — single token implementation
     * @return string
     */
    public static function csrf_token()
    {
        return CSRFHelper::getToken();
    }

    /**
     * Generate a hidden CSRF field for forms
     * @deprecated Delegates to CSRFHelper::getTokenField()
     * @return string
     */
    public static function csrf_field()
    {
        return CSRFHelper::getTokenField();
    }

    /**
     * Verify a CSRF token
     * @deprecated Delegates to CSRFHelper::validateToken()
     * @param string $token
     * @return bool
     */
    public static function verify_csrf_token($token)
    {
        return CSRFHelper::validateToken((string) $token);
    }

    /**
     * Sanitize output for HTML
     * @param string $data
     * @return string
     */
    public static function sanitize($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeHtml($data)
    {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8', ENT_HTML5);
    }
}

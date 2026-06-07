<?php

namespace App\Helpers;

/**
 * CSRF (Cross-Site Request Forgery) Protection Helper
 * Generates and validates CSRF tokens for form submissions
 *
 * @package App\Helpers
 * @author swCMS Team
 */
class CSRFHelper
{
    /**
     * Session key for storing CSRF token
     */
    private const TOKEN_KEY = 'csrf_token';

    /**
     * Token length in bytes (will be 64 hex characters)
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Generate new CSRF token and store in session
     *
     * @return string Generated token
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        SessionHelper::setValue(self::TOKEN_KEY, $token);

        return $token;
    }

    /**
     * Get current CSRF token, generate if doesn't exist
     *
     * @return string CSRF token
     */
    public static function getToken(): string
    {
        if (!SessionHelper::hasValue(self::TOKEN_KEY)) {
            return self::generateToken();
        }

        return SessionHelper::getValue(self::TOKEN_KEY);
    }

    /**
     * Validate CSRF token against session token
     * Uses timing-safe comparison to prevent timing attacks
     *
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateToken(string $token): bool
    {
        if (!SessionHelper::hasValue(self::TOKEN_KEY)) {
            return false;
        }

        $sessionToken = SessionHelper::getValue(self::TOKEN_KEY);

        // Use hash_equals for timing-safe comparison
        return hash_equals($sessionToken, $token);
    }

    /**
     * Regenerate CSRF token (useful after login/logout)
     *
     * @return string New token
     */
    public static function regenerateToken(): string
    {
        return self::generateToken();
    }

    /**
     * Get CSRF token from request
     * Checks POST, then header
     *
     * @return string|null Token from request or null
     */
    public static function getTokenFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // Check custom header (for AJAX requests)
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return null;
    }

    /**
     * Validate CSRF token from current request
     *
     * @return bool True if valid
     */
    public static function validateRequest(): bool
    {
        $token = self::getTokenFromRequest();

        if ($token === null) {
            return false;
        }

        return self::validateToken($token);
    }

    /**
     * Get HTML input field for CSRF token
     *
     * @return string HTML input field
     */
    public static function getTokenField(): string
    {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Get meta tag for CSRF token (for AJAX)
     *
     * @return string HTML meta tag
     */
    public static function getTokenMeta(): string
    {
        $token = self::getToken();
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
}

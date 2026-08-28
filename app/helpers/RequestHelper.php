<?php

namespace App\Helpers;

/**
 * Request Helper
 * Provides secure access to user input with automatic sanitization and validation
 *
 * @package App\Helpers
 * @author swCMS Team
 */
class RequestHelper
{
    /**
     * Supported filter types for input validation
     * Note: FILTER_SANITIZE_STRING is deprecated in PHP 8.1+, we use htmlspecialchars instead
     */
    private const FILTERS = [
        'string' => 'string', // Custom sanitization
        'int' => FILTER_VALIDATE_INT,
        'float' => FILTER_VALIDATE_FLOAT,
        'email' => FILTER_VALIDATE_EMAIL,
        'url' => FILTER_VALIDATE_URL,
        'bool' => FILTER_VALIDATE_BOOLEAN,
        'ip' => FILTER_VALIDATE_IP,
        'raw' => 'raw', // No filtering
        'array' => 'array', // Recursively sanitized array
    ];

    /**
     * Get value from $_GET with sanitization
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if key doesn't exist
     * @param string $filter Filter type (string, int, email, url, etc.)
     * @return mixed Sanitized value or default
     */
    public static function get(string $key, $default = null, string $filter = 'string')
    {
        return self::getFromSource($_GET, $key, $default, $filter);
    }

    /**
     * Get value from $_POST with sanitization
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if key doesn't exist
     * @param string $filter Filter type
     * @return mixed Sanitized value or default
     */
    public static function post(string $key, $default = null, string $filter = 'string')
    {
        return self::getFromSource($_POST, $key, $default, $filter);
    }

    /**
     * Get value from $_REQUEST with sanitization
     *
     * @param string $key Parameter name
     * @param mixed $default Default value if key doesn't exist
     * @param string $filter Filter type
     * @return mixed Sanitized value or default
     */
    public static function input(string $key, $default = null, string $filter = 'string')
    {
        return self::getFromSource($_REQUEST, $key, $default, $filter);
    }

    /**
     * Get value from $_SERVER
     *
     * @param string $key Parameter name
     * @param mixed $default Default value
     * @return mixed Server value or default
     */
    public static function server(string $key, $default = null)
    {
        return $_SERVER[$key] ?? $default;
    }

    /**
     * Get all parameters from specified source
     *
     * @param string $source Source type (get, post, request)
     * @return array Sanitized parameters
     */
    public static function all(string $source = 'request'): array
    {
        $data = match (strtolower($source)) {
            'get' => $_GET,
            'post' => $_POST,
            'request' => $_REQUEST,
            default => []
        };

        return self::sanitizeArray($data);
    }

    /**
     * Check if parameter exists in source
     *
     * @param string $key Parameter name
     * @param string $source Source type (get, post, request)
     * @return bool True if exists
     */
    public static function has(string $key, string $source = 'request'): bool
    {
        return match (strtolower($source)) {
            'get' => isset($_GET[$key]),
            'post' => isset($_POST[$key]),
            'request' => isset($_REQUEST[$key]),
            default => false
        };
    }

    /**
     * Get value from specified source with sanitization
     *
     * @param array $source Data source ($_GET, $_POST, etc.)
     * @param string $key Parameter name
     * @param mixed $default Default value
     * @param string $filter Filter type
     * @return mixed Sanitized value or default
     */
    private static function getFromSource(array $source, string $key, $default, string $filter)
    {
        if (!isset($source[$key])) {
            return $default;
        }

        $value = $source[$key];

        // Array input (field[]=x) is only accepted by the filters that expect one;
        // every other filter treats it as invalid input rather than passing it to
        // string functions that do not accept arrays.
        if ($filter === 'array') {
            return is_array($value) ? self::sanitizeArray($value) : $default;
        }

        if (is_array($value) && $filter !== 'raw') {
            return $default;
        }

        return self::sanitize($value, $filter);
    }

    /**
     * Sanitize value based on filter type
     *
     * @param mixed $value Value to sanitize
     * @param string $filter Filter type
     * @return mixed Sanitized value or null if validation fails
     */
    private static function sanitize($value, string $filter)
    {
        if (!isset(self::FILTERS[$filter])) {
            $filter = 'string';
        }

        $filterType = self::FILTERS[$filter];

        // Raw filter - return value without any modification
        if ($filter === 'raw') {
            return $value;
        }

        // Non-scalar values cannot be sanitized as a string
        if (!is_scalar($value) && $value !== null) {
            return null;
        }

        // String filter - use htmlspecialchars for XSS protection (PHP 8.1+ compatible)
        if ($filter === 'string') {
            return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        }

        // Validation filters (int, email, url, etc.)
        if (in_array($filter, ['int', 'float', 'email', 'url', 'bool', 'ip'])) {
            $result = filter_var($value, $filterType);
            return $result !== false ? $result : null;
        }

        // Default fallback - sanitize as string
        return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array recursively
     *
     * @param array $data Array to sanitize
     * @return array Sanitized array
     */
    private static function sanitizeArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value, 'string');
            }
        }

        return $sanitized;
    }

    /**
     * Get current request method
     *
     * @return string Request method (GET, POST, etc.)
     */
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * Check if request is POST
     *
     * @return bool True if POST request
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool True if GET request
     */
    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    /**
     * Check if request is AJAX
     *
     * @return bool True if AJAX request
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

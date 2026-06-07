<?php

namespace App\Middlewares;

use App\Helpers\CSRFHelper;
use App\Helpers\LogHelper;
use App\Helpers\RequestHelper;
use App\Exceptions\SecurityException;

/**
 * CSRF Protection Middleware
 * Validates CSRF tokens for state-changing requests (POST, PUT, DELETE, PATCH)
 *
 * @package App\Middlewares
 * @author swCMS Team
 */
class CSRFMiddleware
{
    /**
     * Routes exempt from CSRF validation
     * Add API endpoints or specific routes that use alternative protection
     */
    private static array $exemptRoutes = [
        'api/*',       // API endpoints use token authentication
    ];

    /**
     * Validate CSRF token for state-changing requests
     *
     * @throws SecurityException If CSRF validation fails
     * @return void
     */
    public static function validate(): void
    {
        // Only validate state-changing methods
        $method = RequestHelper::method();
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return;
        }

        // Check if route is exempt
        $currentRoute = self::getCurrentRoute();
        if (self::isExempt($currentRoute)) {
            return;
        }

        // Validate token
        if (!CSRFHelper::validateRequest()) {
            LogHelper::warning('CSRF validation failed', [
                'route' => $currentRoute,
                'method' => $method,
                'ip' => RequestHelper::server('REMOTE_ADDR'),
                'user_agent' => RequestHelper::server('HTTP_USER_AGENT')
            ]);

            throw new SecurityException(
                'CSRF token validation failed. Please refresh the page and try again.',
                403
            );
        }
    }

    /**
     * Get current route from request
     *
     * @return string Current route
     */
    private static function getCurrentRoute(): string
    {
        $uri = RequestHelper::server('REQUEST_URI', '');
        $uri = explode('?', $uri, 2)[0];
        return trim($uri, '/');
    }

    /**
     * Check if route is exempt from CSRF validation
     *
     * @param string $route Route to check
     * @return bool True if exempt
     */
    private static function isExempt(string $route): bool
    {
        foreach (self::$exemptRoutes as $exemptRoute) {
            // Support wildcard matching
            if (str_ends_with($exemptRoute, '*')) {
                $prefix = rtrim($exemptRoute, '*');
                if (str_starts_with($route, $prefix)) {
                    return true;
                }
            } elseif ($route === $exemptRoute) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add route to exemption list
     *
     * @param string $route Route pattern to exempt
     * @return void
     */
    public static function exempt(string $route): void
    {
        self::$exemptRoutes[] = $route;
    }

    /**
     * Get list of exempt routes
     *
     * @return array Exempt routes
     */
    public static function getExemptRoutes(): array
    {
        return self::$exemptRoutes;
    }
}

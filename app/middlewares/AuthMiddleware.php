<?php

namespace App\Middlewares;

use App\Controllers\Frontend\AuthController;
use App\Helpers\SessionHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SystemSettingsHelper;
use App\Helpers\RequestHelper;

/**
 * Auth Middleware
 * Protects routes that require authentication
 */

class AuthMiddleware
{
    /**
     * Check if user is authenticated
     *
     * @return bool True if authenticated, false otherwise
     */
    public static function isAuthenticated()
    {
        // Check if user session exists. An anonymous visitor has no session to end:
        // destroying it here would throw away the CSRF token, any flash message and
        // the return URL that requireAuth() is about to store.
        if (!SessionHelper::hasValue('user_id') || !SessionHelper::hasValue('user_role')) {
            return false;
        }

        // Check for session timeout (default 30 minutes when the setting is missing/invalid)
        $timeout = (int) \App\Helpers\SystemSettingsHelper::get('SESSION_TIMEOUT');
        if ($timeout <= 0) {
            $timeout = 1800;
        }
        if (SessionHelper::hasValue('last_activity')) {
            if (time() - (int) SessionHelper::getValue('last_activity') > $timeout) {
                // Session expired, destroy it
                self::logout();
                return false;
            }
        }
        // Update last activity time (also initializes it for pre-existing sessions)
        SessionHelper::setValue('last_activity', time());

        return true;
    }

    /**
     * Check if user has required role
     *
     * @param string|array $roles Required role(s)
     * @return bool True if user has required role, false otherwise
     */
    public static function hasRole($roles)
    {
        self::requireAuth();

        // Convert single role to array
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        return in_array(SessionHelper::getValue('user_role'), $roles);
    }

    /**
     * Require authentication to access a page
     * Redirects to login page if not authenticated
     *
     * @return void
     */
    public static function requireAuth()
    {
        if (!self::isAuthenticated()) {
            // A timed-out session has just been destroyed, so make sure a session is
            // active before writing to it, otherwise the return URL goes nowhere.
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            // Store intended URL for redirect after login
            SessionHelper::setValue('redirect_after_login', RequestHelper::server('REQUEST_URI'));

            // Redirect to login page
            RedirectHelper::redirect(SystemSettingsHelper::get('SITE_URL') . '/auth/login');
            exit;
        }
    }

    /**
     * Require specific role to access a page
     * Redirects to login page or unauthorized page if not authorized
     *
     * @param string|array $roles Required role(s)
     * @return void
     */
    public static function requireRole($roles)
    {
        self::requireAuth();

        if (!self::hasRole($roles)) {
            // Redirect to unauthorized page
            $unauthorizedController = new AuthController();
            $unauthorizedController->unauthorizedAction();
            exit;
        }
    }

    /**
     * Require admin role to access a page
     * Shorthand for requireRole('admin')
     *
     * @return void
     */
    public static function requireAdmin()
    {
        self::requireRole(['admin', 'super_admin']); // Pass roles as an array
    }

    /**
     * Require editor or higher role to access a page
     *
     * @return void
     */
    public static function requireEditor()
    {
        self::requireRole(['admin', 'editor', 'super_admin']);
    }

    /**
     * Require author or higher role to access a page
     *
     * @return void
     */
    public static function requireAuthor()
    {
        self::requireRole(['admin', 'editor', 'author', 'super_admin']);
    }

    /**
     * Logout user
     *
     * @return void
     */
    public static function logout()
    {
        // Unset all session variables
        session_unset(); // Clear all session variables

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
    }
}

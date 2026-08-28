<?php

namespace App\Helpers;

use App\Helpers\SessionHelper;

/**
 * AuthHelper
 *
 * Helper class for authentication and authorization logic.
 */
class AuthHelper
{
    /**
     * Check if the user is logged in
     * @return bool
     */
    public static function isLoggedIn()
    {
        return SessionHelper::hasValue('user_id');
    }

    /**
     * Check if the current user has a specific role
     * @param string $role
     * @return bool
     */
    public static function hasRole($role)
    {
        return SessionHelper::hasValue('user_role') && SessionHelper::getValue('user_role') === $role;
    }

    /**
     * Get the current user ID
     * @return int|null
     */
    public static function getCurrentUserId()
    {
        return SessionHelper::hasValue('user_id') ? SessionHelper::getValue('user_id') : null;
    }

    /**
     * Get the current user's display name, falling back to the username
     *
     * Reads the keys login writes ('user_display_name', 'user_username'), so
     * callers do not have to remember the prefix.
     *
     * @return string|null
     */
    public static function getCurrentUserDisplayName()
    {
        return SessionHelper::getValue('user_display_name')
            ?? SessionHelper::getValue('user_username');
    }

    /**
     * Get the current user's email address
     *
     * @return string|null
     */
    public static function getCurrentUserEmail()
    {
        return SessionHelper::getValue('user_email');
    }
}

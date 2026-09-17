<?php

/**
 * Helper for managing system settings (site name, url, theme, etc.)
 * Retrieves from database with fallback to default values.
 */

namespace App\Helpers;

use App\Models\Settings;

class SystemSettingsHelper
{
    protected static $cache = [];
    /** Per-request cache of the full settings map (invalidated by set()) */
    protected static $allCache = null;
    protected static $defaults = [
        'SITE_NAME' => 'swCMS',
        'SITE_URL' => '',
        'ADMIN_URL' => '',
        'THEME_ACTIVE' => 'default',
        'ALLOW_REGISTRATION' => true,
        'SESSION_TIMEOUT' => 3600,
        'DEBUG_MODE' => true,
        'COMMENTS_ENABLED' => '1',
        'comments_enabled' => '1'
    ];

    /**
     * Recupera una impostazione di sistema
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $settings = new Settings();
        } catch (\Throwable $e) {
            $val = self::$defaults[$key] ?? null;
            if ($key === 'site_title' && empty($val)) {
                $val = self::$defaults['SITE_NAME'] ?? 'swCMS';
            }
            if ($val !== null) {
                self::$cache[$key] = $val;
            }
            return $val;
        }

        if ($key === 'COMMENTS_ENABLED' || $key === 'comments_enabled') {
            // Check both uppercase and lowercase keys; prefer explicit value in DB if either is set
            $val = $settings->get('COMMENTS_ENABLED');
            if ($val === null) {
                $val = $settings->get('comments_enabled', '1');
            }
            $val = (string)$val;
            self::$cache['COMMENTS_ENABLED'] = $val;
            self::$cache['comments_enabled'] = $val;
            return $val;
        }

        $value = $settings->get($key, self::$defaults[$key] ?? null);
        // Cast automatico per valori booleani e numerici
        if (in_array($key, ['ALLOW_REGISTRATION', 'DEBUG_MODE'])) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if ($key === 'SESSION_TIMEOUT') {
            $value = (int)$value;
        }
        // ADMIN_URL fallback
        if ($key === 'ADMIN_URL' && empty($value)) {
            $siteUrl = self::get('SITE_URL');
            $value = $siteUrl ? rtrim($siteUrl, '/') . '/admin' : '/admin';
        }
        // site_title fallback to SITE_NAME
        if ($key === 'site_title' && empty($value)) {
            $value = self::get('SITE_NAME') ?: 'swCMS';
        }
        self::$cache[$key] = $value;
        return $value;
    }

    /**
     * Imposta una impostazione di sistema
     */
    public static function set($key, $value, $description = null, $autoload = 1)
    {
        $settings = new Settings();

        if ($key === 'COMMENTS_ENABLED' || $key === 'comments_enabled') {
            $val = (string)$value;
            self::$cache['COMMENTS_ENABLED'] = $val;
            self::$cache['comments_enabled'] = $val;
            self::$allCache = null;
            $desc = $description ?? 'Enable or disable comments globally';
            $r1 = $settings->set('COMMENTS_ENABLED', $val, $desc, $autoload);
            $r2 = $settings->set('comments_enabled', $val, $desc, $autoload);
            return $r1 && $r2;
        }

        self::$cache[$key] = $value;
        self::$allCache = null;
        return $settings->set($key, $value, $description, $autoload);
    }

    /**
     * Recupera tutte le impostazioni caricate (cached per request)
     */
    public static function all()
    {
        if (self::$allCache !== null) {
            return self::$allCache;
        }

        try {
            $settings = new Settings();
            $all = $settings->all();
        } catch (\Throwable $e) {
            $all = [];
        }
        $result = [];
        foreach ($all as $row) {
            $result[$row['key']] = $row['value'];
        }
        $merged = array_merge(self::$defaults, $result);
        // Apply ADMIN_URL fallback (same logic as get())
        if (empty($merged['ADMIN_URL'])) {
            $siteUrl = $merged['SITE_URL'] ?? '';
            $merged['ADMIN_URL'] = $siteUrl ? rtrim($siteUrl, '/') . '/admin' : '/admin';
        }
        // Frontend themes use site_title; fall back to SITE_NAME when unset
        if (empty($merged['site_title'])) {
            $merged['site_title'] = $merged['SITE_NAME'] ?? 'swCMS';
        }
        self::$allCache = $merged;
        return $merged;
    }
}

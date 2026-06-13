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
    protected static $defaults = [
        'SITE_NAME' => 'swCMS',
        'SITE_URL' => '',
        'ADMIN_URL' => '',
        'THEME_ACTIVE' => 'default',
        'ALLOW_REGISTRATION' => true,
        'SESSION_TIMEOUT' => 3600,
        'DEBUG_MODE' => true
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
        $settings = new Settings();
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
        self::$cache[$key] = $value;
        return $value;
    }

    /**
     * Imposta una impostazione di sistema
     */
    public static function set($key, $value, $description = null, $autoload = 1)
    {
        $settings = new Settings();
        self::$cache[$key] = $value;
        return $settings->set($key, $value, $description, $autoload);
    }

    /**
     * Recupera tutte le impostazioni caricate
     */
    public static function all()
    {
        $settings = new Settings();
        $all = $settings->all();
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
        return $merged;
    }
}

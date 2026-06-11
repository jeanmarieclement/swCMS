<?php

namespace App\Helpers;

/**
 * ConfigHelper
 *
 * Helper class for configuration access.
 */
class ConfigHelper
{
    /**
     * Get a configuration value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getConfig($key, $default = null)
    {
        $config = include __DIR__ . '/../Config/config.php';
        return $config[$key] ?? $default;
    }
}

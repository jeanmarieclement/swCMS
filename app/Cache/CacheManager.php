<?php

namespace App\Cache;

use App\Helpers\SystemSettingsHelper;

/**
 * Cache Manager
 * Factory for creating and managing cache instances
 *
 * @package App\Cache
 * @author swCMS Team
 */
class CacheManager
{
    /**
     * Singleton instance
     */
    private static ?CacheInterface $instance = null;

    /**
     * Get cache instance based on configuration
     *
     * @param string|null $driver Cache driver (file, database, or null for config default)
     * @return CacheInterface Cache instance
     */
    public static function getInstance(?string $driver = null): CacheInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        // Get driver from config if not specified
        if ($driver === null) {
            $driver = defined('CACHE_DRIVER') ? \CACHE_DRIVER : 'file';
        }

        self::$instance = self::createDriver($driver);

        return self::$instance;
    }

    /**
     * Create cache driver instance
     *
     * @param string $driver Driver name (file or database)
     * @return CacheInterface Cache instance
     * @throws \InvalidArgumentException If driver is invalid
     */
    private static function createDriver(string $driver): CacheInterface
    {
        $ttl = defined('CACHE_TTL') ? (int)\CACHE_TTL : 3600;

        return match(strtolower($driver)) {
            'file' => new FileCache(null, $ttl),
            default => new FileCache(null, $ttl) // Default to file cache
        };
    }

    /**
     * Clear all cache
     *
     * @return bool True on success
     */
    public static function clearAll(): bool
    {
        return self::getInstance()->clear();
    }

    /**
     * Remember value in cache with callback
     *
     * @param string $key Cache key
     * @param int|null $ttl Time to live
     * @param callable $callback Callback to generate value
     * @return mixed Cached or generated value
     */
    public static function remember(string $key, ?int $ttl, callable $callback)
    {
        return self::getInstance()->remember($key, $ttl, $callback);
    }

    /**
     * Run garbage collection on cache
     *
     * @return int Number of deleted entries
     */
    public static function gc(): int
    {
        $cache = self::getInstance();

        if (method_exists($cache, 'gc')) {
            return $cache->gc();
        }

        return 0;
    }

    /**
     * Reset singleton instance (for testing)
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}

<?php

namespace App\Cache;

/**
 * Cache Interface
 * PSR-16 Simple Cache inspired interface
 *
 * @package App\Cache
 * @author swCMS Team
 */
interface CacheInterface
{
    /**
     * Fetch a value from cache
     *
     * @param string $key Cache key
     * @param mixed $default Default value if key not found
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null);

    /**
     * Store a value in cache
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int|null $ttl Time to live in seconds (null = forever)
     * @return bool True on success
     */
    public function set(string $key, $value, ?int $ttl = null): bool;

    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool True if exists
     */
    public function has(string $key): bool;

    /**
     * Delete a value from cache
     *
     * @param string $key Cache key
     * @return bool True on success
     */
    public function delete(string $key): bool;

    /**
     * Clear all cache entries
     *
     * @return bool True on success
     */
    public function clear(): bool;

    /**
     * Get multiple values from cache
     *
     * @param array $keys Cache keys
     * @param mixed $default Default value for missing keys
     * @return array Key-value pairs
     */
    public function getMultiple(array $keys, $default = null): array;

    /**
     * Store multiple values in cache
     *
     * @param array $values Key-value pairs
     * @param int|null $ttl Time to live in seconds
     * @return bool True on success
     */
    public function setMultiple(array $values, ?int $ttl = null): bool;

    /**
     * Delete multiple values from cache
     *
     * @param array $keys Cache keys
     * @return bool True on success
     */
    public function deleteMultiple(array $keys): bool;

    /**
     * Store value with callback if key doesn't exist
     *
     * @param string $key Cache key
     * @param int|null $ttl Time to live
     * @param callable $callback Callback to generate value
     * @return mixed Cached or generated value
     */
    public function remember(string $key, ?int $ttl, callable $callback);
}

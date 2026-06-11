<?php

namespace App\Cache;

use App\Helpers\LogHelper;

/**
 * File-based Cache Implementation
 * Stores cache entries as serialized files
 *
 * @package App\Cache
 * @author swCMS Team
 */
class FileCache implements CacheInterface
{
    /**
     * Cache directory path
     */
    private string $cachePath;

    /**
     * Default TTL in seconds (1 hour)
     */
    private int $defaultTTL = 3600;

    /**
     * Constructor
     *
     * @param string|null $cachePath Path to cache directory
     * @param int $defaultTTL Default TTL in seconds
     */
    public function __construct(?string $cachePath = null, int $defaultTTL = 3600)
    {
        $this->cachePath = $cachePath ?? (defined('ROOT_PATH') ? \ROOT_PATH . '/storage/cache' : sys_get_temp_dir() . '/swcms_cache');
        $this->defaultTTL = $defaultTTL;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, $default = null)
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return $default;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            return $default;
        }

        $data = unserialize($content, ['allowed_classes' => [\stdClass::class]]);
        if (!is_array($data) || !isset($data['value'], $data['expires'])) {
            return $default;
        }

        // Check expiration
        if ($data['expires'] !== null && $data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $filePath = $this->getFilePath($key);
        $ttl = $ttl ?? $this->defaultTTL;

        $data = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
            'created' => time()
        ];

        $result = @file_put_contents($filePath, serialize($data), LOCK_EX);

        if ($result === false) {
            LogHelper::error('Failed to write cache file', ['key' => $key, 'path' => $filePath]);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return false;
        }

        // Check if expired
        $content = @file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        $data = unserialize($content, ['allowed_classes' => [\stdClass::class]]);
        if (!is_array($data) || !isset($data['expires'])) {
            return false;
        }

        if ($data['expires'] !== null && $data['expires'] < time()) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        $filePath = $this->getFilePath($key);

        if (!file_exists($filePath)) {
            return true;
        }

        return @unlink($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '/*.cache');

        if ($files === false) {
            return false;
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $default = null): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;

        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;

        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function remember(string $key, ?int $ttl, callable $callback)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);

        return $value;
    }

    /**
     * Get file path for cache key
     *
     * @param string $key Cache key
     * @return string File path
     */
    private function getFilePath(string $key): string
    {
        // Hash key to avoid filesystem issues with special characters
        $hash = md5($key);
        return $this->cachePath . '/' . $hash . '.cache';
    }

    /**
     * Garbage collection - remove expired entries
     *
     * @return int Number of deleted entries
     */
    public function gc(): int
    {
        $deleted = 0;
        $files = glob($this->cachePath . '/*.cache');

        if ($files === false) {
            return 0;
        }

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $data = unserialize($content, ['allowed_classes' => [\stdClass::class]]);
            if (!is_array($data) || !isset($data['expires'])) {
                continue;
            }

            if ($data['expires'] !== null && $data['expires'] < time()) {
                @unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}

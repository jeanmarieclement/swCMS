<?php

namespace App\Core;

/**
 * The version of the CMS itself
 *
 * The VERSION file at the repository root is the single source of truth: the
 * release workflow refuses to publish a tag that disagrees with it, and the
 * installer seeds the CMS_VERSION setting from it. Everything that needs to
 * know what version is running reads it through here rather than repeating a
 * literal that then drifts.
 *
 * @package App\Core
 */
class Version
{
    /**
     * Fallback for an installation whose VERSION file is missing or unreadable
     */
    public const FALLBACK = '1.0.0';

    /**
     * @var string|null Cached, since this is read on plugin activation paths
     */
    private static $current = null;

    /**
     * The version this installation is running
     *
     * @return string A dotted version, never an empty string
     */
    public static function current()
    {
        if (self::$current !== null) {
            return self::$current;
        }

        self::$current = self::readVersionFile() ?? self::FALLBACK;

        return self::$current;
    }

    /**
     * Forget the cached value, for tests that write a VERSION file
     *
     * @return void
     */
    public static function reset()
    {
        self::$current = null;
    }

    /**
     * @return string|null The file's contents, or null when unusable
     */
    private static function readVersionFile()
    {
        $path = (defined('ROOT_PATH') ? \ROOT_PATH : dirname(__DIR__, 2)) . '/VERSION';

        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $contents = trim((string) file_get_contents($path));

        // A truncated or hand-edited file must not become a version string that
        // versionCompare() then reads in surprising ways.
        return preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/', $contents) === 1 ? $contents : null;
    }
}

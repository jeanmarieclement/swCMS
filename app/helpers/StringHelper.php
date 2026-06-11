<?php

namespace App\Helpers;

/**
 * StringHelper
 *
 * Helper class for string manipulation.
 */
class StringHelper
{
    /**
     * Convert a string to a URL-friendly slug
     * @param string $string
     * @return string
     */
    public static function slugify($string)
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($string)));
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Check if a string starts with a given substring
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }

    /**
     * Check if a string ends with a given substring
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * Truncate a string to a given length
     * @param string $string
     * @param int $length
     * @param string $append
     * @return string
     */
    public static function truncate($string, $length = 100, $append = '...')
    {
        if (strlen($string) > $length) {
            return substr($string, 0, $length) . $append;
        }
        return $string;
    }

    /**
     * Generate a random string
     * @param int $length
     * @return string
     */
    public static function randomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }
}

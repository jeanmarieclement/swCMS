<?php

namespace App\Helpers;

/**
 * SeoHelper
 *
 * Builds the metadata frontend pages put in their <head>: a meta description
 * that reads as one line of prose, and a canonical URL.
 */
class SeoHelper
{
    /**
     * Build a meta description from HTML content
     *
     * strip_tags() removes the tags but keeps the newlines and indentation
     * between them, which would otherwise open the attribute with blank lines
     * and push the useful text past the cut.
     *
     * @param string|null $content HTML content
     * @param int $limit Maximum length in characters
     * @return string
     */
    public static function metaDescription($content, int $limit = 150): string
    {
        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $content)));

        if ($text === '' || mb_strlen($text) <= $limit) {
            return $text;
        }

        // mb_substr so a multi-byte character is never cut in half
        $truncated = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false && $lastSpace > 0) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return rtrim($truncated);
    }

    /**
     * Build a canonical URL from the site URL and a path
     *
     * @param string $siteUrl Site base URL, with or without a trailing slash
     * @param string $path Path below it, with or without a leading slash
     * @return string
     */
    public static function canonicalUrl($siteUrl, $path): string
    {
        return rtrim((string) $siteUrl, '/') . '/' . ltrim((string) $path, '/');
    }
}

<?php

namespace App\Helpers;

/**
 * CacheHeaderHelper
 *
 * Decides the Cache-Control policy for a response. PHP's session cache limiter
 * would otherwise stamp no-store, plus its 1981 Expires sentinel, on every
 * request that starts a session — which here means every request the CMS serves.
 */
class CacheHeaderHelper
{
    /**
     * Policy for authenticated areas: nothing may be written to disk or reused
     * from the back/forward cache once the user logs out.
     */
    public const PRIVATE_NO_STORE = 'no-store, no-cache, must-revalidate';

    /**
     * Policy for the public side: the browser may reuse the page (so the Back
     * button does not refetch), but shared caches may not hold it, because a
     * rendered page embeds the CSRF token and, when logged in, the user's name.
     */
    public const PRIVATE_REVALIDATE = 'private, max-age=0, must-revalidate';

    /**
     * Path prefixes that must never be cached
     *
     * @var string[]
     */
    private const UNCACHEABLE_PREFIXES = ['/admin', '/auth'];

    /**
     * Get the Cache-Control value for a request path
     *
     * @param string $path Request path, with or without a query string
     * @return string
     */
    public static function policyForPath($path)
    {
        $path = (string) $path;
        $parsedPath = parse_url($path, PHP_URL_PATH);

        if ($parsedPath === false || $parsedPath === null || $parsedPath === '') {
            $parsedPath = '/';
        }

        foreach (self::UNCACHEABLE_PREFIXES as $prefix) {
            if ($parsedPath === $prefix || strpos($parsedPath, $prefix . '/') === 0) {
                return self::PRIVATE_NO_STORE;
            }
        }

        return self::PRIVATE_REVALIDATE;
    }

    /**
     * Get the Cache-Control value for the current request
     *
     * @return string
     */
    public static function policyForCurrentRequest()
    {
        return self::policyForPath($_SERVER['REQUEST_URI'] ?? '/');
    }
}

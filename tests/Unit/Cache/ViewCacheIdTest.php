<?php

namespace Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use App\Core\View;

/**
 * View cache id Test
 * Smarty keys a cached page by template name alone, so without a cache id
 * every URL rendered through the same template shares one cached page.
 *
 * @package Tests\Unit\Cache
 */
class ViewCacheIdTest extends TestCase
{
    public function testDifferentPathsGetDifferentCacheIds()
    {
        $first = View::cacheIdForRequest('/blog/first-post');
        $second = View::cacheIdForRequest('/blog/second-post');

        $this->assertNotEquals($first, $second);
    }

    public function testSamePathGetsTheSameCacheId()
    {
        $this->assertEquals(
            View::cacheIdForRequest('/blog/first-post'),
            View::cacheIdForRequest('/blog/first-post')
        );
    }

    public function testQueryStringIsPartOfTheCacheId()
    {
        $this->assertNotEquals(
            View::cacheIdForRequest('/blog?page=1'),
            View::cacheIdForRequest('/blog?page=2')
        );
    }

    public function testFragmentIsIgnored()
    {
        $this->assertEquals(
            View::cacheIdForRequest('/blog/first-post'),
            View::cacheIdForRequest('/blog/first-post#comments')
        );
    }

    public function testCacheIdContainsNoSmartySeparator()
    {
        // Smarty treats '|' as the cache group separator
        $this->assertStringNotContainsString('|', View::cacheIdForRequest('/a|b/c'));
    }

    public function testCacheIdIsAlwaysProduced()
    {
        $this->assertNotEmpty(View::cacheIdForRequest('/'));
        $this->assertNotEmpty(View::cacheIdForRequest(''));
    }

    public function testIgnoredParametersDoNotMintNewCacheEntries()
    {
        // Tracking parameters do not change the rendered page, so they must not
        // each get a cache file of their own.
        $this->assertEquals(
            View::cacheIdForRequest('/blog'),
            View::cacheIdForRequest('/blog?utm_source=newsletter')
        );

        $this->assertEquals(
            View::cacheIdForRequest('/blog?utm_source=a'),
            View::cacheIdForRequest('/blog?utm_source=b')
        );
    }

    public function testAllowlistedParametersAreOrderIndependent()
    {
        $this->assertEquals(
            View::cacheIdForRequest('/blog?page=2&comment_page=3'),
            View::cacheIdForRequest('/blog?comment_page=3&page=2')
        );
    }

    public function testPaginationParametersAreAllowlistedByDefault()
    {
        $allowed = View::cacheableQueryParams();

        $this->assertArrayHasKey('page', $allowed);
        // The frontend comments partial paginates on its own parameter.
        $this->assertArrayHasKey('comment_page', $allowed);
    }

    public function testAllowlistedParametersOnlyAcceptTheirOwnShapeOfValue()
    {
        // 'page' is a dimension of the cache key, so accepting arbitrary values
        // would let a visitor mint a cache file per request — the very problem
        // the allowlist exists to close.
        $this->assertFalse(View::isRequestCacheable('/blog?page=abc', 'GET'));
        $this->assertFalse(View::isRequestCacheable('/blog?page=0', 'GET'));
        $this->assertFalse(View::isRequestCacheable('/blog?page=1234567', 'GET'));
        $this->assertFalse(View::isRequestCacheable('/blog?page[]=1', 'GET'));
        $this->assertTrue(View::isRequestCacheable('/blog?page=12', 'GET'));
    }

    public function testTrackingParametersStillHitTheCache()
    {
        // Campaign links must not disable caching for all inbound marketing
        // traffic; they carry no influence on what is rendered.
        $this->assertTrue(View::isRequestCacheable('/blog?utm_source=newsletter', 'GET'));
        $this->assertTrue(View::isRequestCacheable('/blog?page=2&fbclid=xyz', 'GET'));

        $this->assertEquals(
            View::cacheIdForRequest('/blog?page=2'),
            View::cacheIdForRequest('/blog?page=2&fbclid=xyz')
        );
    }

    public function testRequestsWithOnlyAllowlistedParametersAreCacheable()
    {
        $this->assertTrue(View::isRequestCacheable('/blog', 'GET'));
        $this->assertTrue(View::isRequestCacheable('/blog?page=2', 'GET'));
    }

    public function testRequestsCarryingUnknownParametersBypassTheCache()
    {
        // The cache id ignores them, so serving a cached page could answer with
        // output rendered for different input — a plugin routing on its own
        // parameter would get the wrong page. Bypassing also keeps a crawler
        // with random query strings from filling the disk.
        $this->assertFalse(View::isRequestCacheable('/blog?sort=price', 'GET'));
        $this->assertFalse(View::isRequestCacheable('/blog?page=2&sort=price', 'GET'));
    }

    public function testNonGetRequestsAreNeverCacheable()
    {
        $this->assertFalse(View::isRequestCacheable('/blog', 'POST'));
        $this->assertFalse(View::isRequestCacheable('/blog', 'post'));
        $this->assertFalse(View::isRequestCacheable('/blog', 'DELETE'));
    }

    public function testFallsBackToTheCurrentRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '/blog/from-server';

        $this->assertEquals(
            View::cacheIdForRequest('/blog/from-server'),
            View::cacheIdForRequest()
        );
    }
}

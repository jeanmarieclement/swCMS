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

    public function testFallsBackToTheCurrentRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '/blog/from-server';

        $this->assertEquals(
            View::cacheIdForRequest('/blog/from-server'),
            View::cacheIdForRequest()
        );
    }
}

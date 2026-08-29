<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\CacheHeaderHelper;

// Composer autoloads app/ through a classmap, so a helper added after the last
// `composer dump-autoload` is invisible to the test run. The application itself
// resolves it through App\Core\Autoloader's PSR-4 lookup.
require_once dirname(__DIR__, 3) . '/app/helpers/CacheHeaderHelper.php';

/**
 * CacheHeaderHelper Test
 * The application states its own caching policy instead of inheriting PHP's
 * session cache limiter, which stamped no-store on every public page.
 *
 * @package Tests\Unit\Helpers
 */
class CacheHeaderHelperTest extends TestCase
{
    public function testAdminPagesStayUncacheable()
    {
        $this->assertEquals(
            'no-store, no-cache, must-revalidate',
            CacheHeaderHelper::policyForPath('/admin/articles')
        );
    }

    public function testAdminRootStaysUncacheable()
    {
        $this->assertStringContainsString('no-store', CacheHeaderHelper::policyForPath('/admin'));
    }

    public function testAuthPagesStayUncacheable()
    {
        $this->assertStringContainsString('no-store', CacheHeaderHelper::policyForPath('/auth/login'));
    }

    public function testPublicPagesArePrivateButReusable()
    {
        $this->assertEquals(
            'private, max-age=0, must-revalidate',
            CacheHeaderHelper::policyForPath('/blog/first-post')
        );
    }

    public function testHomepageIsPrivateButReusable()
    {
        $this->assertStringContainsString('private', CacheHeaderHelper::policyForPath('/'));
    }

    public function testPublicPolicyNeverAllowsSharedCaches()
    {
        // Rendered pages embed the CSRF token, so no proxy or CDN may hold them
        $this->assertStringNotContainsString('public', CacheHeaderHelper::policyForPath('/blog'));
    }

    public function testAPathMerelyContainingAdminIsPublic()
    {
        $this->assertStringContainsString('private', CacheHeaderHelper::policyForPath('/blog/admin-tips'));
    }

    public function testQueryStringIsIgnored()
    {
        $this->assertStringContainsString('no-store', CacheHeaderHelper::policyForPath('/admin?page=2'));
    }

    public function testMissingPathFallsBackToThePublicPolicy()
    {
        $this->assertStringContainsString('private', CacheHeaderHelper::policyForPath(''));
    }
}

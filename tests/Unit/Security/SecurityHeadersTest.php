<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * Test that security headers would be set by public/index.php
     *
     * Note: This test verifies that the header() calls exist in the bootstrap file.
     * Integration tests would be needed to verify actual HTTP headers in responses.
     */
    public function testSecurityHeadersConfiguration()
    {
        // Read the public/index.php file to verify headers are configured
        $indexPhpPath = \ROOT_PATH . '/public/index.php';

        if (!file_exists($indexPhpPath)) {
            $this->markTestSkipped('public/index.php not found');
        }

        $content = file_get_contents($indexPhpPath);

        // Verify X-Frame-Options header (clickjacking protection)
        $this->assertStringContainsString(
            "X-Frame-Options: SAMEORIGIN",
            $content,
            'X-Frame-Options header should be set for clickjacking protection'
        );

        // Verify X-Content-Type-Options header (MIME sniffing protection)
        $this->assertStringContainsString(
            "X-Content-Type-Options: nosniff",
            $content,
            'X-Content-Type-Options header should be set to prevent MIME sniffing'
        );

        // Verify X-XSS-Protection header (legacy XSS protection)
        $this->assertStringContainsString(
            "X-XSS-Protection: 1; mode=block",
            $content,
            'X-XSS-Protection header should be set for legacy XSS protection'
        );

        // Verify Referrer-Policy header (privacy protection)
        $this->assertStringContainsString(
            "Referrer-Policy: strict-origin-when-cross-origin",
            $content,
            'Referrer-Policy header should be set for privacy protection'
        );

        // Verify Content-Security-Policy header exists
        $this->assertStringContainsString(
            "Content-Security-Policy:",
            $content,
            'Content-Security-Policy header should be configured'
        );

        // Verify HSTS header with HTTPS detection
        $this->assertStringContainsString(
            "Strict-Transport-Security:",
            $content,
            'Strict-Transport-Security header should be configured for HTTPS'
        );
    }

    public function testHstsOnlySetWithHttps()
    {
        // Verify HSTS is only set when HTTPS is detected
        $indexPhpPath = \ROOT_PATH . '/public/index.php';

        if (!file_exists($indexPhpPath)) {
            $this->markTestSkipped('public/index.php not found');
        }

        $content = file_get_contents($indexPhpPath);

        // Check that HSTS header is conditional on HTTPS detection
        // Should use the same detection logic as session security
        $this->assertMatchesRegularExpression(
            '/if\s*\(\s*\$isHttps\s*\).*Strict-Transport-Security/s',
            $content,
            'HSTS header should only be set when HTTPS is detected'
        );
    }

    public function testContentSecurityPolicyAllowsBootstrapCdn()
    {
        // Verify CSP allows Bootstrap CDN for error pages
        $indexPhpPath = \ROOT_PATH . '/public/index.php';

        if (!file_exists($indexPhpPath)) {
            $this->markTestSkipped('public/index.php not found');
        }

        $content = file_get_contents($indexPhpPath);

        // CSP should allow cdn.jsdelivr.net for Bootstrap used in error pages
        // This can be either in style-src or as a general CDN allowance
        $this->assertMatchesRegularExpression(
            '/style-src.*(?:cdn\.jsdelivr\.net|https:)/s',
            $content,
            'CSP should allow Bootstrap CDN (cdn.jsdelivr.net) for error pages'
        );
    }
}

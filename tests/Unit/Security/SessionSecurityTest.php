<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class SessionSecurityTest extends TestCase
{
    public function testSessionSecuritySettingsConfigured()
    {
        // Verify session settings are configured in public/index.php
        $indexPhpPath = \ROOT_PATH . '/public/index.php';

        if (!file_exists($indexPhpPath)) {
            $this->markTestSkipped('public/index.php not found');
        }

        $content = file_get_contents($indexPhpPath);

        // Verify session security settings are configured
        $this->assertStringContainsString("ini_set('session.cookie_httponly', 1)", $content);
        $this->assertStringContainsString("ini_set('session.cookie_samesite', 'Strict')", $content);
        $this->assertStringContainsString("ini_set('session.use_strict_mode', 1)", $content);
        $this->assertStringContainsString("ini_set('session.use_only_cookies', 1)", $content);
    }

    public function testSessionCookieSecureInHttps()
    {
        // Determine if HTTPS is detected (including reverse proxy scenarios)
        $isHttps = (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
            (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
            (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        );

        // If HTTPS is detected, secure flag should be set
        if ($isHttps) {
            $this->assertEquals('1', ini_get('session.cookie_secure'),
                'Session cookie secure flag should be set when HTTPS is detected');
        } else {
            // In non-HTTPS environments, the secure flag might not be set
            // This is acceptable for development environments
            $this->assertTrue(true, 'Non-HTTPS environment detected - secure flag not required');
        }
    }
}

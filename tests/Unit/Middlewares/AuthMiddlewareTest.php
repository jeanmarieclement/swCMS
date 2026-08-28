<?php

namespace Tests\Unit\Middlewares;

use PHPUnit\Framework\TestCase;
use App\Middlewares\AuthMiddleware;
use App\Helpers\SessionHelper;

/**
 * AuthMiddleware Test
 * Tests session handling for anonymous visitors
 *
 * @package Tests\Unit\Middlewares
 */
class AuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAnonymousVisitorIsNotAuthenticated()
    {
        $this->assertFalse(AuthMiddleware::isAuthenticated());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAnonymousVisitorKeepsCsrfToken()
    {
        SessionHelper::setValue('csrf_token', 'token-value');

        AuthMiddleware::isAuthenticated();

        $this->assertEquals('token-value', SessionHelper::getValue('csrf_token'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAnonymousVisitorKeepsFlashMessage()
    {
        SessionHelper::setFlashMessage('Please log in', 'info');

        AuthMiddleware::isAuthenticated();

        $this->assertTrue(SessionHelper::hasFlashMessage());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSessionStaysActiveForAnonymousVisitor()
    {
        AuthMiddleware::isAuthenticated();

        // The session must survive so that requireAuth() can store the
        // return URL the visitor is about to be redirected away from.
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status());

        SessionHelper::setValue('redirect_after_login', '/admin/articles');
        $this->assertEquals('/admin/articles', SessionHelper::getValue('redirect_after_login'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPartialSessionIsNotTreatedAsAuthenticated()
    {
        // user_id without user_role must not pass the check
        SessionHelper::setValue('user_id', 1);

        $this->assertFalse(AuthMiddleware::isAuthenticated());
    }
}

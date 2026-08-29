<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\AuthHelper;
use App\Helpers\SessionHelper;

/**
 * AuthHelper Test
 * Tests reading the current user's identity from the session
 *
 * @package Tests\Unit\Helpers
 */
class AuthHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // SessionHelper reads and writes $_SESSION directly, so no session
        // needs to be started for these tests.
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testGetCurrentUserDisplayNameReturnsDisplayName()
    {
        SessionHelper::setValue('user_display_name', 'Jean-Marie');
        SessionHelper::setValue('user_username', 'jm');

        $this->assertEquals('Jean-Marie', AuthHelper::getCurrentUserDisplayName());
    }

    public function testGetCurrentUserDisplayNameFallsBackToUsername()
    {
        SessionHelper::setValue('user_username', 'jm');

        $this->assertEquals('jm', AuthHelper::getCurrentUserDisplayName());
    }

    public function testGetCurrentUserDisplayNameReturnsNullWhenAnonymous()
    {
        $this->assertNull(AuthHelper::getCurrentUserDisplayName());
    }

    public function testGetCurrentUserEmailReturnsEmail()
    {
        SessionHelper::setValue('user_email', 'jm@example.com');

        $this->assertEquals('jm@example.com', AuthHelper::getCurrentUserEmail());
    }

    public function testGetCurrentUserEmailReturnsNullWhenAnonymous()
    {
        $this->assertNull(AuthHelper::getCurrentUserEmail());
    }

    public function testIdentityUsesTheKeysLoginWrites()
    {
        // Exactly the keys AuthController writes on successful login
        SessionHelper::setValue('user_id', 7);
        SessionHelper::setValue('user_email', 'jm@example.com');
        SessionHelper::setValue('user_username', 'jm');
        SessionHelper::setValue('user_display_name', 'Jean-Marie');
        SessionHelper::setValue('user_role', 'admin');

        $this->assertEquals(7, AuthHelper::getCurrentUserId());
        $this->assertEquals('Jean-Marie', AuthHelper::getCurrentUserDisplayName());
        $this->assertEquals('jm@example.com', AuthHelper::getCurrentUserEmail());
    }
}

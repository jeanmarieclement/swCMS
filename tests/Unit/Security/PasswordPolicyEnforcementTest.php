<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class PasswordPolicyEnforcementTest extends TestCase
{
    private $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userModel = new User();
    }

    public function testWeakPasswordRejectedInValidation()
    {
        $result = $this->userModel->validatePasswordStrength('weak');

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    public function testPasswordTooShort()
    {
        $result = $this->userModel->validatePasswordStrength('Pass1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Password must be at least 8 characters long', $result['errors']);
    }

    public function testPasswordMissingUppercase()
    {
        $result = $this->userModel->validatePasswordStrength('password1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one uppercase letter', $result['errors']);
    }

    public function testPasswordMissingLowercase()
    {
        $result = $this->userModel->validatePasswordStrength('PASSWORD1!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one lowercase letter', $result['errors']);
    }

    public function testPasswordMissingNumber()
    {
        $result = $this->userModel->validatePasswordStrength('Password!');

        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one number', $result['errors']);
    }

    public function testPasswordMissingSpecialCharacter()
    {
        $result = $this->userModel->validatePasswordStrength('Password1');

        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one special character', $result['errors']);
    }

    public function testStrongPasswordAccepted()
    {
        // Password meeting all requirements: 8+ chars, uppercase, lowercase, number, special
        $result = $this->userModel->validatePasswordStrength('SecureP@ss123');

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testAnotherStrongPassword()
    {
        $result = $this->userModel->validatePasswordStrength('MyP@ssw0rd!');

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testCreateUserEnforcesPasswordPolicy()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password does not meet security requirements');

        // Attempt to create user with weak password
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'weak',
            'role' => 'subscriber'
        ];

        $this->userModel->createUser($userData);
    }

    public function testUpdateUserEnforcesPasswordPolicy()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password does not meet security requirements');

        // Attempt to update user with weak password
        $userData = [
            'password' => 'weak'
        ];

        // We're testing the validation logic, not the actual database update
        // The exception should be thrown before any database operation
        $this->userModel->updateUser(1, $userData);
    }
}

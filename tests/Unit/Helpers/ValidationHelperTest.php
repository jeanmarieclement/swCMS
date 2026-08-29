<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\ValidationHelper;

/**
 * ValidationHelper Test
 * Tests for validation rules and complex input validation
 *
 * @package Tests\Unit\Helpers
 * @author swCMS Team
 */
class ValidationHelperTest extends TestCase
{
    public function testRequiredValidation()
    {
        $this->assertTrue(ValidationHelper::required('value'));
        $this->assertTrue(ValidationHelper::required('0'));
        $this->assertTrue(ValidationHelper::required(0));
        $this->assertTrue(ValidationHelper::required(['item']));

        $this->assertFalse(ValidationHelper::required(''));
        $this->assertFalse(ValidationHelper::required('   '));
        $this->assertFalse(ValidationHelper::required(null));
        $this->assertFalse(ValidationHelper::required([]));
    }

    public function testMinLengthValidation()
    {
        $this->assertTrue(ValidationHelper::minLength('hello', 3));
        $this->assertTrue(ValidationHelper::minLength('hello', 5));
        $this->assertFalse(ValidationHelper::minLength('hi', 3));
    }

    public function testMaxLengthValidation()
    {
        $this->assertTrue(ValidationHelper::maxLength('hi', 5));
        $this->assertTrue(ValidationHelper::maxLength('hello', 5));
        $this->assertFalse(ValidationHelper::maxLength('hello world', 5));
    }

    public function testInValidation()
    {
        $this->assertTrue(ValidationHelper::in('admin', ['admin', 'user', 'guest']));
        $this->assertTrue(ValidationHelper::in('user', ['admin', 'user', 'guest']));
        $this->assertFalse(ValidationHelper::in('superadmin', ['admin', 'user', 'guest']));
        $this->assertFalse(ValidationHelper::in('guest', ['admin', 'user']));
    }

    public function testSlugValidation()
    {
        $this->assertTrue(ValidationHelper::slug('my-article-slug'));
        $this->assertTrue(ValidationHelper::slug('article'));
        $this->assertTrue(ValidationHelper::slug('my-article-123'));

        $this->assertFalse(ValidationHelper::slug('My Article!'));
        $this->assertFalse(ValidationHelper::slug('article_name'));
        $this->assertFalse(ValidationHelper::slug('Article-Name'));
        $this->assertFalse(ValidationHelper::slug('my--article'));
    }

    public function testUsernameValidation()
    {
        $this->assertTrue(ValidationHelper::username('user_123'));
        $this->assertTrue(ValidationHelper::username('JohnDoe'));
        $this->assertTrue(ValidationHelper::username('user'));

        $this->assertFalse(ValidationHelper::username('ab')); // Too short
        $this->assertFalse(ValidationHelper::username('u')); // Too short
        $this->assertFalse(ValidationHelper::username('user@123')); // Invalid char
        $this->assertFalse(ValidationHelper::username('user-name')); // Invalid char
        $this->assertFalse(ValidationHelper::username('a_very_long_username_exceeding_limit')); // Too long
    }

    public function testPasswordValidationWeak()
    {
        $result = ValidationHelper::password('weak');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        // Password "weak" fails 3 requirements: too short, no uppercase, no number
        // It has lowercase letters, so it doesn't fail all 4
        $this->assertCount(3, $result['errors']);
    }

    public function testPasswordValidationNoUppercase()
    {
        $result = ValidationHelper::password('password123');
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one uppercase letter', $result['errors']);
    }

    public function testPasswordValidationNoLowercase()
    {
        $result = ValidationHelper::password('PASSWORD123');
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one lowercase letter', $result['errors']);
    }

    public function testPasswordValidationNoNumber()
    {
        $result = ValidationHelper::password('Password');
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must contain at least one number', $result['errors']);
    }

    public function testPasswordValidationTooShort()
    {
        $result = ValidationHelper::password('Pass1');
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must be at least 8 characters', $result['errors']);
    }

    public function testPasswordValidationStrong()
    {
        $result = ValidationHelper::password('StrongPass123');
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testPasswordValidationCustomMinLength()
    {
        $result = ValidationHelper::password('Pass123', 10);
        $this->assertFalse($result['valid']);
        $this->assertContains('Password must be at least 10 characters', $result['errors']);

        $result = ValidationHelper::password('Password123', 10);
        $this->assertTrue($result['valid']);
    }

    public function testComplexValidationSuccess()
    {
        $data = [
            'email' => 'test@example.com',
            'username' => 'testuser',
            'age' => 25
        ];

        $rules = [
            'email' => ['required', 'email'],
            'username' => ['required', ['min_length', 3]],
            'age' => ['required', 'int']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testComplexValidationFailure()
    {
        $data = [
            'email' => 'invalid-email',
            'username' => 'ab',
            'age' => 'not-a-number'
        ];

        $rules = [
            'email' => ['required', 'email'],
            'username' => ['required', ['min_length', 3]],
            'age' => ['required', 'int']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
        $this->assertArrayHasKey('username', $result['errors']);
        $this->assertArrayHasKey('age', $result['errors']);
    }

    public function testComplexValidationMissingRequiredField()
    {
        $data = [
            'username' => 'testuser'
        ];

        $rules = [
            'email' => ['required'],
            'username' => ['required']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('email', $result['errors']);
        $this->assertArrayNotHasKey('username', $result['errors']);
    }

    public function testValidateWithInRule()
    {
        $data = [
            'role' => 'admin'
        ];

        $rules = [
            'role' => [['in', ['admin', 'user', 'guest']]]
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);

        // Test invalid value
        $data['role'] = 'superadmin';
        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
    }

    public function testValidateWithSlugRule()
    {
        $data = [
            'slug' => 'my-article-slug'
        ];

        $rules = [
            'slug' => ['required', 'slug']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);

        // Test invalid slug
        $data['slug'] = 'My Article!';
        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
    }

    public function testValidateWithUsernameRule()
    {
        $data = [
            'username' => 'valid_user123'
        ];

        $rules = [
            'username' => ['required', 'username']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);

        // Test invalid username
        $data['username'] = 'ab';
        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
    }

    public function testValidateWithMaxLength()
    {
        $data = [
            'title' => 'Short title'
        ];

        $rules = [
            'title' => [['max_length', 50]]
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);

        // Test title too long
        $data['title'] = str_repeat('a', 51);
        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
    }

    public function testValidateWithUrlRule()
    {
        $data = [
            'website' => 'https://example.com'
        ];

        $rules = [
            'website' => ['url']
        ];

        $result = ValidationHelper::validate($data, $rules);
        $this->assertTrue($result['valid']);

        // Test invalid URL
        $data['website'] = 'not-a-url';
        $result = ValidationHelper::validate($data, $rules);
        $this->assertFalse($result['valid']);
    }

    public function testValidateSupportsLaravelStyleMinRule()
    {
        // AuthController's registration rules use 'min:8' for the password field
        $rules = ['password' => ['required', 'min:8']];

        $result = ValidationHelper::validate(['password' => 'short'], $rules);
        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('password', $result['errors']);

        $result = ValidationHelper::validate(['password' => 'longenough'], $rules);
        $this->assertTrue($result['valid']);
    }

    public function testValidateSupportsLaravelStyleMaxRule()
    {
        $rules = ['title' => ['max:5']];

        $result = ValidationHelper::validate(['title' => 'toolong'], $rules);
        $this->assertFalse($result['valid']);

        $result = ValidationHelper::validate(['title' => 'ok'], $rules);
        $this->assertTrue($result['valid']);
    }

    public function testValidateRejectsUnknownRuleInsteadOfSilentlyPassing()
    {
        $result = ValidationHelper::validate(['field' => 'anything'], [
            'field' => ['totally_bogus_rule']
        ]);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('field', $result['errors']);
    }
}

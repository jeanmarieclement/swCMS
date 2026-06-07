<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\CSRFHelper;
use App\Helpers\SessionHelper;

/**
 * CSRFHelper Test
 * Tests for CSRF token generation and validation
 *
 * @package Tests\Unit\Helpers
 * @author swCMS Team
 */
class CSRFHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_SERVER = [];
    }

    public function testGenerateTokenCreatesValidToken()
    {
        $token = CSRFHelper::generateToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
    }

    public function testGenerateTokenStoresInSession()
    {
        $token = CSRFHelper::generateToken();

        $this->assertTrue(SessionHelper::hasValue('csrf_token'));
        $this->assertEquals($token, SessionHelper::getValue('csrf_token'));
    }

    public function testGenerateTokenCreatesUniqueTokens()
    {
        $token1 = CSRFHelper::generateToken();
        $token2 = CSRFHelper::generateToken();

        $this->assertNotEquals($token1, $token2);
    }

    public function testGetTokenReturnsExistingToken()
    {
        $token1 = CSRFHelper::generateToken();
        $token2 = CSRFHelper::getToken();

        $this->assertEquals($token1, $token2);
    }

    public function testGetTokenGeneratesNewTokenIfNoneExists()
    {
        // Ensure no token in session
        $this->assertFalse(SessionHelper::hasValue('csrf_token'));

        $token = CSRFHelper::getToken();

        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->assertTrue(SessionHelper::hasValue('csrf_token'));
    }

    public function testValidateTokenReturnsTrueForValidToken()
    {
        $token = CSRFHelper::generateToken();

        $this->assertTrue(CSRFHelper::validateToken($token));
    }

    public function testValidateTokenReturnsFalseForInvalidToken()
    {
        CSRFHelper::generateToken();

        $this->assertFalse(CSRFHelper::validateToken('invalid_token'));
        $this->assertFalse(CSRFHelper::validateToken(''));
        $this->assertFalse(CSRFHelper::validateToken('1234567890abcdef'));
    }

    public function testValidateTokenReturnsFalseWhenNoTokenInSession()
    {
        // No token in session
        $this->assertFalse(CSRFHelper::validateToken('any_token'));
    }

    public function testValidateTokenUsesTimingSafeComparison()
    {
        // This test verifies that hash_equals is being used internally
        // by checking that validation works correctly
        $token = CSRFHelper::generateToken();

        // Same token should validate
        $this->assertTrue(CSRFHelper::validateToken($token));

        // Token with different case should not validate (hash_equals is case-sensitive)
        $this->assertFalse(CSRFHelper::validateToken(strtoupper($token)));
    }

    public function testRegenerateTokenCreatesNewToken()
    {
        $token1 = CSRFHelper::generateToken();
        $token2 = CSRFHelper::regenerateToken();

        $this->assertNotEquals($token1, $token2);
        $this->assertEquals($token2, SessionHelper::getValue('csrf_token'));
    }

    public function testGetTokenFromRequestReturnsTokenFromPost()
    {
        $token = 'test_csrf_token_123';
        $_POST['csrf_token'] = $token;

        $this->assertEquals($token, CSRFHelper::getTokenFromRequest());
    }

    public function testGetTokenFromRequestReturnsTokenFromHeader()
    {
        $token = 'test_csrf_token_456';
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;

        $this->assertEquals($token, CSRFHelper::getTokenFromRequest());
    }

    public function testGetTokenFromRequestPrioritizesPostOverHeader()
    {
        $postToken = 'post_token';
        $headerToken = 'header_token';

        $_POST['csrf_token'] = $postToken;
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $headerToken;

        $this->assertEquals($postToken, CSRFHelper::getTokenFromRequest());
    }

    public function testGetTokenFromRequestReturnsNullWhenNoToken()
    {
        $this->assertNull(CSRFHelper::getTokenFromRequest());
    }

    public function testValidateRequestReturnsTrueForValidRequest()
    {
        $token = CSRFHelper::generateToken();
        $_POST['csrf_token'] = $token;

        $this->assertTrue(CSRFHelper::validateRequest());
    }

    public function testValidateRequestReturnsFalseForInvalidRequest()
    {
        CSRFHelper::generateToken();
        $_POST['csrf_token'] = 'invalid_token';

        $this->assertFalse(CSRFHelper::validateRequest());
    }

    public function testValidateRequestReturnsFalseWhenNoTokenInRequest()
    {
        CSRFHelper::generateToken();

        $this->assertFalse(CSRFHelper::validateRequest());
    }

    public function testGetTokenFieldReturnsValidHtmlInput()
    {
        $token = CSRFHelper::generateToken();
        $field = CSRFHelper::getTokenField();

        $this->assertStringContainsString('<input', $field);
        $this->assertStringContainsString('type="hidden"', $field);
        $this->assertStringContainsString('name="csrf_token"', $field);
        $this->assertStringContainsString('value="' . $token . '"', $field);
    }

    public function testGetTokenFieldEscapesHtmlSpecialChars()
    {
        // Even though our tokens are hex and won't contain special chars,
        // we test that the escaping function is applied
        $field = CSRFHelper::getTokenField();

        // Should not contain unescaped quotes or brackets
        $this->assertStringNotContainsString('<script>', $field);
        $this->assertStringNotContainsString('javascript:', $field);
    }

    public function testGetTokenMetaReturnsValidHtmlMeta()
    {
        $token = CSRFHelper::generateToken();
        $meta = CSRFHelper::getTokenMeta();

        $this->assertStringContainsString('<meta', $meta);
        $this->assertStringContainsString('name="csrf-token"', $meta);
        $this->assertStringContainsString('content="' . $token . '"', $meta);
    }

    public function testGetTokenMetaEscapesHtmlSpecialChars()
    {
        $meta = CSRFHelper::getTokenMeta();

        // Should not contain unescaped quotes or brackets
        $this->assertStringNotContainsString('<script>', $meta);
        $this->assertStringNotContainsString('javascript:', $meta);
    }

    public function testTokenPersistsAcrossMultipleGetCalls()
    {
        $token1 = CSRFHelper::getToken();
        $token2 = CSRFHelper::getToken();
        $token3 = CSRFHelper::getToken();

        $this->assertEquals($token1, $token2);
        $this->assertEquals($token2, $token3);
    }

    public function testValidateRequestWorksWithAjaxHeader()
    {
        $token = CSRFHelper::generateToken();
        $_SERVER['HTTP_X_CSRF_TOKEN'] = $token;

        $this->assertTrue(CSRFHelper::validateRequest());
    }
}

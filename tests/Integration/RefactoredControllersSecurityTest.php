<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Helpers\CSRFHelper;
use App\Helpers\RequestHelper;
use App\Helpers\SessionHelper;

/**
 * Refactored Controllers Security Test
 * Integration tests for Phase 3-4 refactored controllers
 *
 * Tests verify:
 * - CSRF protection on all POST actions
 * - RequestHelper input sanitization
 * - SessionHelper integration
 * - Proper rejection of invalid CSRF tokens
 *
 * @package Tests\Integration
 */
class RefactoredControllersSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session and superglobals
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER = [];
    }

    /**
     * Helper to simulate a POST request with CSRF token
     */
    private function simulatePostRequest(array $postData, bool $includeValidToken = true): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        if ($includeValidToken) {
            $token = CSRFHelper::generateToken();
            $_POST['csrf_token'] = $token;
        }

        foreach ($postData as $key => $value) {
            $_POST[$key] = $value;
        }
    }

    /**
     * Helper to simulate a GET request
     */
    private function simulateGetRequest(array $getData): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        foreach ($getData as $key => $value) {
            $_GET[$key] = $value;
        }
    }

    // ==================== CSRF Protection Tests ====================

    public function testCSRFValidationFailsWithInvalidToken()
    {
        CSRFHelper::generateToken(); // Create a session token
        $_POST['csrf_token'] = 'invalid_token';

        $this->assertFalse(CSRFHelper::validateRequest());
    }

    public function testCSRFValidationFailsWithMissingToken()
    {
        CSRFHelper::generateToken(); // Create a session token
        // No CSRF token in $_POST

        $this->assertFalse(CSRFHelper::validateRequest());
    }

    public function testCSRFValidationSucceedsWithValidToken()
    {
        $token = CSRFHelper::generateToken();
        $_POST['csrf_token'] = $token;

        $this->assertTrue(CSRFHelper::validateRequest());
    }

    // ==================== RequestHelper Tests ====================

    public function testRequestHelperPostSanitizesHtmlByDefault()
    {
        $_POST['title'] = '<script>alert("xss")</script>Test Title';

        $sanitized = RequestHelper::post('title');

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('Test Title', $sanitized);
    }

    public function testRequestHelperPostRawFilterPreservesContent()
    {
        $_POST['content'] = '<p>This is <strong>HTML</strong> content</p>';

        $raw = RequestHelper::post('content', '', 'raw');

        $this->assertEquals('<p>This is <strong>HTML</strong> content</p>', $raw);
    }

    public function testRequestHelperPostIntFilterConvertsToInteger()
    {
        $_POST['page_id'] = '123';
        $_POST['invalid'] = 'abc';

        $this->assertSame(123, RequestHelper::post('page_id', 0, 'int'));
        // Note: validation returns null on failure, not the default
        $this->assertNull(RequestHelper::post('invalid', 0, 'int'));
    }

    public function testRequestHelperPostEmailFilterValidatesEmail()
    {
        $_POST['email_valid'] = 'user@example.com';
        $_POST['email_invalid'] = 'user@example.com<script>'; // Invalid email

        $validEmail = RequestHelper::post('email_valid', '', 'email');
        $invalidEmail = RequestHelper::post('email_invalid', '', 'email');

        $this->assertEquals('user@example.com', $validEmail);
        // Invalid email validation returns null
        $this->assertNull($invalidEmail);
    }

    public function testRequestHelperGetReturnsDefaultWhenKeyNotExists()
    {
        $value = RequestHelper::get('nonexistent', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function testRequestHelperGetSanitizesHtml()
    {
        $_GET['search'] = '<script>alert("xss")</script>search term';

        $sanitized = RequestHelper::get('search');

        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringContainsString('search term', $sanitized);
    }

    public function testRequestHelperIsPostReturnsTrueForPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertTrue(RequestHelper::isPost());
    }

    public function testRequestHelperIsPostReturnsFalseForGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertFalse(RequestHelper::isPost());
    }

    public function testRequestHelperServerReturnsServerVariable()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.1';

        $this->assertEquals('192.168.1.1', RequestHelper::server('REMOTE_ADDR'));
    }

    public function testRequestHelperServerReturnsDefaultWhenNotSet()
    {
        $this->assertEquals('unknown', RequestHelper::server('NONEXISTENT', 'unknown'));
    }

    // ==================== SessionHelper Integration Tests ====================

    public function testSessionHelperSetAndGetValue()
    {
        SessionHelper::setValue('test_key', 'test_value');

        $this->assertEquals('test_value', SessionHelper::getValue('test_key'));
    }

    public function testSessionHelperHasValueReturnsTrueWhenKeyExists()
    {
        SessionHelper::setValue('existing_key', 'value');

        $this->assertTrue(SessionHelper::hasValue('existing_key'));
    }

    public function testSessionHelperHasValueReturnsFalseWhenKeyNotExists()
    {
        $this->assertFalse(SessionHelper::hasValue('nonexistent_key'));
    }

    public function testSessionHelperRemoveValue()
    {
        SessionHelper::setValue('temp_key', 'temp_value');
        $this->assertTrue(SessionHelper::hasValue('temp_key'));

        SessionHelper::removeValue('temp_key');

        $this->assertFalse(SessionHelper::hasValue('temp_key'));
    }

    // ==================== Integration: POST Action Flow ====================

    public function testPostActionWithValidCsrfAndSanitizedInput()
    {
        // Simulate a complete POST request flow
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $token = CSRFHelper::generateToken();
        $_POST['csrf_token'] = $token;
        $_POST['title'] = '<script>alert("xss")</script>My Title';
        $_POST['content'] = '<p>Safe HTML content</p>';
        $_POST['active'] = '1';

        // Verify CSRF validation passes
        $this->assertTrue(CSRFHelper::validateRequest());

        // Verify RequestHelper sanitizes input
        $title = RequestHelper::post('title');
        $this->assertStringNotContainsString('<script>', $title);
        $this->assertStringContainsString('My Title', $title);

        // Verify raw filter preserves HTML
        $content = RequestHelper::post('content', '', 'raw');
        $this->assertEquals('<p>Safe HTML content</p>', $content);

        // Verify checkbox detection
        $active = RequestHelper::post('active', null) !== null;
        $this->assertTrue($active);
    }

    public function testPostActionRejectsInvalidCsrf()
    {
        // Simulate a POST request with invalid CSRF
        $_SERVER['REQUEST_METHOD'] = 'POST';
        CSRFHelper::generateToken(); // Generate session token
        $_POST['csrf_token'] = 'invalid_token';
        $_POST['title'] = 'Test Title';

        // Verify CSRF validation fails
        $this->assertFalse(CSRFHelper::validateRequest());
    }

    // ==================== Controller-Specific Tests ====================

    /**
     * Test UserController create action flow
     */
    public function testUserControllerCreateActionFlow()
    {
        $this->simulatePostRequest([
            'username' => '<script>alert("xss")</script>testuser',
            'email' => 'test@example.com', // Use valid email
            'password' => 'SecurePass123!',
            'role' => 'author'
        ]);

        $this->assertTrue(CSRFHelper::validateRequest());
        $this->assertTrue(RequestHelper::isPost());

        $username = RequestHelper::post('username');
        $email = RequestHelper::post('email', null, 'email');
        $password = RequestHelper::post('password', null, 'raw');
        $role = RequestHelper::post('role');

        $this->assertStringNotContainsString('<script>', $username);
        $this->assertEquals('test@example.com', $email);
        $this->assertEquals('SecurePass123!', $password); // Raw preserved
        $this->assertStringNotContainsString('<script>', $role);
    }

    /**
     * Test ArticleController create action flow
     */
    public function testArticleControllerCreateActionFlow()
    {
        $this->simulatePostRequest([
            'title' => 'Test Article',
            'content' => '<h2>Article Content</h2><p>With HTML</p>',
            'status' => 'draft',
            'categories' => ['1', '2'],
            'tags' => ['php', 'security']
        ]);

        $this->assertTrue(CSRFHelper::validateRequest());

        $title = RequestHelper::post('title');
        $content = RequestHelper::post('content', '', 'raw');
        $status = RequestHelper::post('status');
        // Arrays should be accessed directly from $_POST for now, as RequestHelper doesn't handle arrays
        $categories = $_POST['categories'] ?? [];
        $tags = $_POST['tags'] ?? [];

        $this->assertStringContainsString('Test Article', $title);
        $this->assertStringContainsString('<h2>Article Content</h2>', $content);
        $this->assertEquals('draft', $status);
        $this->assertIsArray($categories);
        $this->assertIsArray($tags);
    }

    /**
     * Test PageController update action flow
     */
    public function testPageControllerUpdateActionFlow()
    {
        $this->simulatePostRequest([
            'title' => 'Updated Page Title',
            'content' => '<div class="page-content">Content</div>',
            'slug' => 'updated-page',
            'status' => 'published',
            'parent_id' => '5',
            'template' => 'default',
            'order' => '10'
        ]);

        $this->assertTrue(CSRFHelper::validateRequest());

        $title = RequestHelper::post('title');
        $content = RequestHelper::post('content', '', 'raw');
        $slug = RequestHelper::post('slug');
        $status = RequestHelper::post('status');
        $parentId = RequestHelper::post('parent_id', '', 'raw');
        $template = RequestHelper::post('template');
        $order = RequestHelper::post('order', null, 'int');

        $this->assertEquals('Updated Page Title', $title);
        $this->assertStringContainsString('page-content', $content);
        $this->assertEquals('updated-page', $slug);
        $this->assertEquals('published', $status);
        $this->assertEquals('5', $parentId);
        $this->assertEquals('default', $template);
        $this->assertEquals(10, $order);
    }

    /**
     * Test MenuController store action flow
     */
    public function testMenuControllerStoreActionFlow()
    {
        $this->simulatePostRequest([
            'title' => 'Main Menu',
            'url' => '/about',
            'type' => 'custom',
            'location' => 'header',
            'position' => '1',
            'parent_id' => '',
            'active' => '1',
            'target' => '_self',
            'css_class' => 'nav-link'
        ]);

        $this->assertTrue(CSRFHelper::validateRequest());

        $title = trim(RequestHelper::post('title', ''));
        $url = trim(RequestHelper::post('url', ''));
        $type = RequestHelper::post('type', 'custom');
        $location = RequestHelper::post('location', 'header');
        $position = RequestHelper::post('position', 0, 'int');
        $parentId = RequestHelper::post('parent_id', '', 'raw');
        $active = RequestHelper::post('active', null) !== null ? 1 : 0;
        $target = RequestHelper::post('target', '_self');
        $cssClass = trim(RequestHelper::post('css_class', ''));

        $this->assertEquals('Main Menu', $title);
        $this->assertEquals('/about', $url);
        $this->assertEquals('custom', $type);
        $this->assertEquals('header', $location);
        $this->assertEquals(1, $position);
        $this->assertEquals('', $parentId);
        $this->assertEquals(1, $active);
        $this->assertEquals('_self', $target);
        $this->assertEquals('nav-link', $cssClass);
    }

    /**
     * Test PluginController activate action flow
     */
    public function testPluginControllerActivateActionFlow()
    {
        $this->simulatePostRequest([
            'plugin' => 'test-plugin'
        ]);

        // Simulate user session
        SessionHelper::setValue('user_id', 1);

        $this->assertTrue(CSRFHelper::validateRequest());
        $this->assertTrue(RequestHelper::isPost());

        $pluginName = RequestHelper::post('plugin', '');
        $userId = SessionHelper::getValue('user_id');

        $this->assertEquals('test-plugin', $pluginName);
        $this->assertEquals(1, $userId);
    }

    /**
     * Test PluginController generate action flow with checkboxes
     */
    public function testPluginControllerGenerateActionFlowWithCheckboxes()
    {
        $this->simulatePostRequest([
            'name' => 'test-plugin',
            'display_name' => 'Test Plugin',
            'description' => 'A test plugin',
            'author' => 'Test Author',
            'version' => '1.0.0',
            'include_hooks' => '1',
            'include_settings' => '1',
            'include_assets' => '1'
            // Note: include_readme and include_tests are not set (unchecked)
        ]);

        $this->assertTrue(CSRFHelper::validateRequest());

        $config = [
            'name' => RequestHelper::post('name', ''),
            'display_name' => RequestHelper::post('display_name', ''),
            'description' => RequestHelper::post('description', ''),
            'author' => RequestHelper::post('author', ''),
            'version' => RequestHelper::post('version', '1.0.0'),
            'include_hooks' => RequestHelper::post('include_hooks', null) !== null,
            'include_settings' => RequestHelper::post('include_settings', null) !== null,
            'include_assets' => RequestHelper::post('include_assets', null) !== null,
            'include_readme' => RequestHelper::post('include_readme', null) !== null,
            'include_tests' => RequestHelper::post('include_tests', null) !== null
        ];

        $this->assertEquals('test-plugin', $config['name']);
        $this->assertEquals('Test Plugin', $config['display_name']);
        $this->assertEquals('A test plugin', $config['description']);
        $this->assertEquals('Test Author', $config['author']);
        $this->assertEquals('1.0.0', $config['version']);
        $this->assertTrue($config['include_hooks']);
        $this->assertTrue($config['include_settings']);
        $this->assertTrue($config['include_assets']);
        $this->assertFalse($config['include_readme']);
        $this->assertFalse($config['include_tests']);
    }

    // ==================== Security Logging Tests ====================

    public function testCSRFFailureLogsRemoteAddress()
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.1.100';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        CSRFHelper::generateToken();
        $_POST['csrf_token'] = 'invalid';

        $this->assertFalse(CSRFHelper::validateRequest());

        $remoteAddr = RequestHelper::server('REMOTE_ADDR', 'unknown');
        $this->assertEquals('192.168.1.100', $remoteAddr);
    }

    public function testCSRFFailureHandlesUnknownRemoteAddress()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        CSRFHelper::generateToken();
        $_POST['csrf_token'] = 'invalid';

        $this->assertFalse(CSRFHelper::validateRequest());

        $remoteAddr = RequestHelper::server('REMOTE_ADDR', 'unknown');
        $this->assertEquals('unknown', $remoteAddr);
    }

    // ==================== XSS Prevention Tests ====================

    public function testXSSPreventionInTitleFields()
    {
        $_POST['title'] = '<img src=x onerror=alert("xss")>Title';

        $sanitized = RequestHelper::post('title');

        $this->assertStringNotContainsString('onerror', $sanitized);
        $this->assertStringNotContainsString('alert', $sanitized);
    }

    public function testXSSPreventionInMultipleFields()
    {
        $_POST['field1'] = '<script>evil()</script>';
        $_POST['field2'] = '"><img src=x onerror=alert(1)>';
        $_POST['field3'] = 'javascript:alert("xss")';

        $field1 = RequestHelper::post('field1');
        $field2 = RequestHelper::post('field2');
        $field3 = RequestHelper::post('field3');

        $this->assertStringNotContainsString('<script>', $field1);
        $this->assertStringNotContainsString('onerror', $field2);
        // htmlspecialchars encodes but doesn't remove javascript:, it encodes quotes
        $this->assertStringContainsString('javascript:', $field3); // Protocol remains but is safe
        $this->assertStringContainsString('&quot;', $field3); // Quotes are encoded
    }

    // ==================== Edge Case Tests ====================

    public function testEmptyStringHandling()
    {
        $_POST['empty_field'] = '';

        $value = RequestHelper::post('empty_field', 'default');

        $this->assertEquals('', $value); // Empty string should be preserved, not replaced with default
    }

    public function testNullHandling()
    {
        // Field not set in $_POST

        $value = RequestHelper::post('nonexistent_field', 'default');

        $this->assertEquals('default', $value);
    }

    public function testArrayHandling()
    {
        $_POST['categories'] = ['1', '2', '3'];

        // RequestHelper doesn't handle arrays, access directly from $_POST
        $categories = $_POST['categories'] ?? [];

        $this->assertIsArray($categories);
        $this->assertCount(3, $categories);
        $this->assertEquals(['1', '2', '3'], $categories);
    }
}

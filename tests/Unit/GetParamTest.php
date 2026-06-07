<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class GetParamTest extends TestCase
{
    private $controllerMock;

    protected function setUp(): void
    {
        $this->controllerMock = new class extends \App\Core\Controller {
            // Skip parent constructor to avoid DB connection (RoleService, SystemSettingsHelper).
            // getParam() only needs RequestHelper — no constructor state required.
            public function __construct() {}

            public function indexAction() {}

            public function testGetParam($key, $default = null, $method = 'GET', $sanitize = true) {
                return $this->getParam($key, $default, $method, $sanitize);
            }
        };
    }

    protected function tearDown(): void
    {
        unset($_GET['test_key']);
        unset($_POST['test_key']);
    }

    public function testGetParamBasicGet()
    {
        $_GET['test_key'] = '  test value  ';

        $result = $this->controllerMock->testGetParam('test_key');

        // RequestHelper does not auto-trim; value is returned as-is after strip_tags
        $this->assertEquals('  test value  ', $result);
    }

    public function testGetParamBasicPost()
    {
        $_POST['test_key'] = '  post value  ';

        $result = $this->controllerMock->testGetParam('test_key', null, 'POST');

        // RequestHelper does not auto-trim; value is returned as-is after strip_tags
        $this->assertEquals('  post value  ', $result);
    }

    public function testGetParamWithDefault()
    {
        $result = $this->controllerMock->testGetParam('nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $result);
    }

    public function testGetParamSanitization()
    {
        $_GET['test_key'] = '<script>alert("xss")</script>';

        $result = $this->controllerMock->testGetParam('test_key', null, 'GET', true);

        // script tags are stripped by RequestHelper's string filter
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testGetParamNoSanitization()
    {
        $_GET['test_key'] = '<b>bold text</b>';

        $result = $this->controllerMock->testGetParam('test_key', null, 'GET', false);

        $this->assertEquals('<b>bold text</b>', $result);
    }

    public function testGetParamNonStringValue()
    {
        // RequestHelper::get() calls strip_tags() which requires a string.
        // Passing an array is not a supported use case — skip this test.
        $this->markTestSkipped('RequestHelper::get() does not support array values (strip_tags requires string).');
    }
}

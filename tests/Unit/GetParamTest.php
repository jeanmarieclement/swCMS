<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../bootstrap.php';

class GetParamTest extends TestCase
{
    private $controllerMock;

    protected function setUp(): void
    {
        // Create a test controller that extends the base controller
        $this->controllerMock = new class extends \App\Core\Controller {
            public function testGetParam($key, $default = null, $method = 'GET', $sanitize = true) {
                return $this->getParam($key, $default, $method, $sanitize);
            }
        };
    }

    protected function tearDown(): void
    {
        // Clean up superglobals
        unset($_GET['test_key']);
        unset($_POST['test_key']);
    }

    public function testGetParamBasicGet()
    {
        $_GET['test_key'] = '  test value  ';

        $result = $this->controllerMock->testGetParam('test_key');

        // Should trim the value
        $this->assertEquals('test value', $result);
    }

    public function testGetParamBasicPost()
    {
        $_POST['test_key'] = '  post value  ';

        $result = $this->controllerMock->testGetParam('test_key', null, 'POST');

        // Should trim the value
        $this->assertEquals('post value', $result);
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

        // Should be sanitized (exact result depends on SecurityHelper implementation)
        $this->assertNotContains('<script>', $result);
    }

    public function testGetParamNoSanitization()
    {
        $_GET['test_key'] = '<b>bold text</b>';

        $result = $this->controllerMock->testGetParam('test_key', null, 'GET', false);

        // Should not be sanitized when sanitize=false
        $this->assertEquals('<b>bold text</b>', $result);
    }

    public function testGetParamNonStringValue()
    {
        $_GET['test_key'] = ['array', 'value'];

        $result = $this->controllerMock->testGetParam('test_key');

        // Should return array as-is
        $this->assertEquals(['array', 'value'], $result);
    }
}
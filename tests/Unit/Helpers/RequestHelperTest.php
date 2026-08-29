<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\RequestHelper;

/**
 * RequestHelper Test
 * Tests for secure input handling and sanitization
 *
 * @package Tests\Unit\Helpers
 * @author swCMS Team
 */
class RequestHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
        $_SERVER = [];
    }

    public function testGetReturnsDefaultWhenKeyNotExists()
    {
        $result = RequestHelper::get('nonexistent', 'default');
        $this->assertEquals('default', $result);
    }

    public function testGetSanitizesString()
    {
        $_GET['test'] = '<script>alert("xss")</script>';
        $result = RequestHelper::get('test');
        // htmlspecialchars converts quotes to &quot; for XSS protection
        $this->assertEquals('alert(&quot;xss&quot;)', $result);
    }

    public function testGetValidatesInteger()
    {
        $_GET['id'] = '123';
        $result = RequestHelper::get('id', null, 'int');
        $this->assertEquals(123, $result);
    }

    public function testGetRejectsInvalidInteger()
    {
        $_GET['id'] = 'abc';
        $result = RequestHelper::get('id', null, 'int');
        $this->assertNull($result);
    }

    public function testGetRejectsInvalidIntegerReturningTheCallerDefault()
    {
        // Every rejection should signal the same way as array input already
        // does: with the caller's default, not always null regardless of it.
        $_GET['id'] = 'abc';
        $result = RequestHelper::get('id', 42, 'int');
        $this->assertEquals(42, $result);
    }

    public function testBoolFilterAcceptsExplicitFalse()
    {
        // FILTER_VALIDATE_BOOLEAN returns false for a valid falsy input, which
        // must not be indistinguishable from "not set" (the default).
        $_GET['active'] = '0';
        $result = RequestHelper::get('active', true, 'bool');
        $this->assertFalse($result);
    }

    public function testBoolFilterAcceptsTheStringOff()
    {
        $_GET['active'] = 'off';
        $result = RequestHelper::get('active', true, 'bool');
        $this->assertFalse($result);
    }

    public function testBoolFilterRejectsGarbageReturningTheDefault()
    {
        $_GET['active'] = 'bogus';
        $result = RequestHelper::get('active', true, 'bool');
        $this->assertTrue($result);
    }

    public function testGetValidatesEmail()
    {
        $_GET['email'] = 'test@example.com';
        $result = RequestHelper::get('email', null, 'email');
        $this->assertEquals('test@example.com', $result);
    }

    public function testGetRejectsInvalidEmail()
    {
        $_GET['email'] = 'invalid-email';
        $result = RequestHelper::get('email', null, 'email');
        $this->assertNull($result);
    }

    public function testPostSanitizesString()
    {
        $_POST['name'] = '<b>Test</b>';
        $result = RequestHelper::post('name');
        $this->assertEquals('Test', $result);
    }

    public function testPostValidatesEmail()
    {
        $_POST['email'] = 'test@example.com';
        $result = RequestHelper::post('email', null, 'email');
        $this->assertEquals('test@example.com', $result);
    }

    public function testInputFromRequest()
    {
        $_REQUEST['test'] = 'value';
        $result = RequestHelper::input('test');
        $this->assertEquals('value', $result);
    }

    public function testServerReturnsValue()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $result = RequestHelper::server('REQUEST_METHOD');
        $this->assertEquals('POST', $result);
    }

    public function testServerReturnsDefault()
    {
        $result = RequestHelper::server('NONEXISTENT', 'default');
        $this->assertEquals('default', $result);
    }

    public function testAllReturnsAllGetParameters()
    {
        $_GET = ['a' => '1', 'b' => '2'];
        $result = RequestHelper::all('get');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('a', $result);
        $this->assertArrayHasKey('b', $result);
    }

    public function testAllSanitizesArrayValues()
    {
        $_POST = ['name' => '<script>xss</script>', 'age' => '25'];
        $result = RequestHelper::all('post');
        // htmlspecialchars removes script tags
        $this->assertEquals('xss', $result['name']);
        $this->assertEquals('25', $result['age']);
    }

    public function testHasReturnsTrueWhenKeyExists()
    {
        $_GET['test'] = 'value';
        $this->assertTrue(RequestHelper::has('test', 'get'));
    }

    public function testHasReturnsFalseWhenKeyNotExists()
    {
        $this->assertFalse(RequestHelper::has('nonexistent', 'get'));
    }

    public function testMethodReturnsRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', RequestHelper::method());
    }

    public function testMethodReturnsGetByDefault()
    {
        $this->assertEquals('GET', RequestHelper::method());
    }

    public function testIsPostReturnsTrueForPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue(RequestHelper::isPost());
    }

    public function testIsPostReturnsFalseForGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertFalse(RequestHelper::isPost());
    }

    public function testIsGetReturnsTrueForGetRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(RequestHelper::isGet());
    }

    public function testIsAjaxReturnsTrueForAjaxRequest()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(RequestHelper::isAjax());
    }

    public function testIsAjaxReturnsFalseForNormalRequest()
    {
        $this->assertFalse(RequestHelper::isAjax());
    }

    public function testSanitizesNestedArrays()
    {
        $_POST = [
            'user' => [
                'name' => '<script>alert("xss")</script>',
                'email' => 'test@example.com'
            ]
        ];
        $result = RequestHelper::all('post');
        // htmlspecialchars converts quotes to &quot; for XSS protection
        $this->assertEquals('alert(&quot;xss&quot;)', $result['user']['name']);
        $this->assertEquals('test@example.com', $result['user']['email']);
    }

    public function testAllDropsKeysCarryingMarkup()
    {
        // Array keys are user input just like values: settings[<img src=x
        // onerror=alert(1)>]=y must not carry attacker markup through.
        $_POST = ['<img src=x onerror=alert(1)>' => 'value'];

        $result = RequestHelper::all('post');

        $this->assertSame([], $result);
    }

    public function testArrayFilterDropsKeysCarryingMarkup()
    {
        $_POST['settings'] = ['<script>xss</script>' => 'value', 'safe' => 'kept'];

        $result = RequestHelper::post('settings', [], 'array');

        $this->assertSame(['safe' => 'kept'], $result);
    }

    public function testASanitizedKeyNeverOverwritesALegitimateOne()
    {
        // Sanitizing a key is not injective: 'SITE_NAME<x' strips down to
        // 'SITE_NAME'. Rewriting it would let an unexpected key impersonate an
        // expected one and win, defeating allowlists that test the key name.
        $_POST = ['SITE_NAME' => 'legitimate', 'SITE_NAME<x' => 'injected'];

        $result = RequestHelper::all('post');

        $this->assertEquals('legitimate', $result['SITE_NAME']);
    }

    public function testKeysThatCannotSurviveSanitizationAreDropped()
    {
        $_POST = ['<img src=x onerror=alert(1)>' => 'value', 'clean_key' => 'kept'];

        $result = RequestHelper::all('post');

        $this->assertEquals(['clean_key' => 'kept'], $result);
    }

    public function testNumericKeysArePreserved()
    {
        // A list like tags[]=a&tags[]=b has integer keys, which must survive
        $_POST['tags'] = ['a', 'b'];

        $this->assertEquals(['a', 'b'], RequestHelper::post('tags', [], 'array'));
    }

    public function testRawFilterReturnsUnmodifiedValue()
    {
        $_GET['raw'] = '<script>test</script>';
        $result = RequestHelper::get('raw', null, 'raw');
        $this->assertEquals('<script>test</script>', $result);
    }

    public function testValidatesUrl()
    {
        $_GET['website'] = 'https://example.com';
        $result = RequestHelper::get('website', null, 'url');
        $this->assertEquals('https://example.com', $result);
    }

    public function testRejectsInvalidUrl()
    {
        $_GET['website'] = 'not-a-url';
        $result = RequestHelper::get('website', null, 'url');
        $this->assertNull($result);
    }

    public function testValidatesFloat()
    {
        $_GET['price'] = '19.99';
        $result = RequestHelper::get('price', null, 'float');
        $this->assertEquals(19.99, $result);
    }

    public function testRejectsInvalidFloat()
    {
        $_GET['price'] = 'abc';
        $result = RequestHelper::get('price', null, 'float');
        $this->assertNull($result);
    }

    public function testGetRejectsArrayInputForStringFilter()
    {
        $_GET['name'] = ['x'];
        $result = RequestHelper::get('name', 'default');
        $this->assertEquals('default', $result);
    }

    public function testPostRejectsArrayInputForStringFilter()
    {
        $_POST['name'] = ['<script>xss</script>'];
        $result = RequestHelper::post('name');
        $this->assertNull($result);
    }

    public function testGetRejectsArrayInputForValidationFilter()
    {
        $_GET['id'] = ['1', '2'];
        $result = RequestHelper::get('id', null, 'int');
        $this->assertNull($result);
    }

    public function testInputRejectsArrayInput()
    {
        $_REQUEST['test'] = ['a' => 'b'];
        $result = RequestHelper::input('test', 'default');
        $this->assertEquals('default', $result);
    }

    public function testRawFilterStillReturnsArray()
    {
        $_POST['settings'] = ['key' => 'value'];
        $result = RequestHelper::post('settings', [], 'raw');
        $this->assertEquals(['key' => 'value'], $result);
    }

    public function testArrayFilterSanitizesValues()
    {
        $_POST['tags'] = ['<script>xss</script>', 'plain'];
        $result = RequestHelper::post('tags', [], 'array');
        $this->assertEquals(['xss', 'plain'], $result);
    }

    public function testArrayFilterSanitizesNestedValues()
    {
        $_POST['user'] = ['profile' => ['bio' => '<b>hi</b>']];
        $result = RequestHelper::post('user', [], 'array');
        $this->assertEquals(['profile' => ['bio' => 'hi']], $result);
    }

    public function testArrayFilterRejectsScalar()
    {
        $_POST['tags'] = 'not-an-array';
        $result = RequestHelper::post('tags', [], 'array');
        $this->assertEquals([], $result);
    }

    public function testArrayFilterKeepsMultiValueFormFields()
    {
        // How the article form posts its checkboxes and its multi-select:
        // categories[]=3&categories[]=5&tags[]=php
        $_POST['categories'] = ['3', '5'];
        $_POST['tags'] = ['php'];

        $this->assertEquals(['3', '5'], RequestHelper::post('categories', [], 'array'));
        $this->assertEquals(['php'], RequestHelper::post('tags', [], 'array'));
    }

    public function testArrayFilterReturnsTheDefaultWhenTheFieldIsAbsent()
    {
        // No checkbox ticked: the field is not submitted at all
        $this->assertEquals([], RequestHelper::post('categories', [], 'array'));
    }

    public function testPasswordFilterReturnsValueUnmodified()
    {
        // Passwords must never be mangled by htmlspecialchars/strip_tags: a
        // password containing '<' or '&' has to survive verbatim so it still
        // matches the hash it was registered with.
        $_POST['password'] = 'a<b>&"c';
        $result = RequestHelper::post('password', null, 'password');
        $this->assertEquals('a<b>&"c', $result);
    }

    public function testPasswordFilterRejectsArrayInput()
    {
        // password[]=x must not reach password_verify()/password_hash() as an
        // array: unlike 'raw' (which PluginController::configure() genuinely
        // needs for array input), 'password' rejects arrays like every other
        // scalar filter.
        $_POST['password'] = ['x'];
        $result = RequestHelper::post('password', null, 'password');
        $this->assertNull($result);
    }
}

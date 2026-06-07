<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Router Test Class
 * Tests the functionality of the Router class
 */
class RouterTest extends TestCase
{
    private $router;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Include the Router class if it's not autoloaded
        if (!class_exists('App\\Core\\Router')) {
            require_once APP_PATH . '/core/Router.php';
        }

        $this->router = new \App\Core\Router();
    }
    
    /**
     * Test that the router can add routes
     */
    public function testAddRoute()
    {
        // Use reflection to access private property
        $reflection = new \ReflectionClass($this->router);
        $routesProperty = $reflection->getProperty('routes');
        $routesProperty->setAccessible(true);
        
        // Get initial routes count
        $initialRoutes = $routesProperty->getValue($this->router);
        $initialCount = count($initialRoutes);
        
        // Add a new test route
        $this->router->addRoute('test/route', ['controller' => 'Test', 'action' => 'index']);
        
        // Get updated routes
        $updatedRoutes = $routesProperty->getValue($this->router);
        
        // Assert that a new route was added
        $this->assertEquals($initialCount + 1, count($updatedRoutes));
        
        // Check if the route pattern was properly formatted
        $routeFound = false;
        foreach ($updatedRoutes as $pattern => $params) {
            if (strpos($pattern, 'test\\/route') !== false) {
                $routeFound = true;
                $this->assertEquals('Test', $params['controller']);
                $this->assertEquals('index', $params['action']);
                break;
            }
        }
        
        $this->assertTrue($routeFound, 'The test route was not found in the routes array');
    }
    
    /**
     * Test that the router can match routes
     */
    public function testMatch()
    {
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->router);
        $matchMethod = $reflection->getMethod('match');
        $matchMethod->setAccessible(true);
        
        // Add a test route
        $this->router->addRoute('test/match', ['controller' => 'Test', 'action' => 'match']);
        
        // Test matching
        $result = $matchMethod->invoke($this->router, 'test/match');
        $this->assertTrue($result, 'Route should match');
        
        // Use reflection to access private property
        $paramsProperty = $reflection->getProperty('params');
        $paramsProperty->setAccessible(true);
        
        // Get the params after matching
        $params = $paramsProperty->getValue($this->router);
        
        // Assert that the params were set correctly
        $this->assertEquals('Test', $params['controller']);
        $this->assertEquals('match', $params['action']);
    }
    
    /**
     * Test that the router correctly handles route parameters
     */
    public function testRouteWithParameters()
    {
        // Use reflection to access private method and property
        $reflection = new \ReflectionClass($this->router);
        $matchMethod = $reflection->getMethod('match');
        $matchMethod->setAccessible(true);
        $paramsProperty = $reflection->getProperty('params');
        $paramsProperty->setAccessible(true);
        
        // Add a route with parameters
        $this->router->addRoute('articles/{id}', ['controller' => 'Article', 'action' => 'show']);
        
        // Test matching with a parameter
        $result = $matchMethod->invoke($this->router, 'articles/123');
        $this->assertTrue($result, 'Route with parameter should match');
        
        // Get the params after matching
        $params = $paramsProperty->getValue($this->router);
        
        // Assert that the params were set correctly
        $this->assertEquals('Article', $params['controller']);
        $this->assertEquals('show', $params['action']);
        $this->assertEquals('123', $params['id']);
    }
    
    /**
     * Test the URL parsing functionality
     */
    public function testGetUrl()
    {
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->router);
        $getUrlMethod = $reflection->getMethod('getUrl');
        $getUrlMethod->setAccessible(true);

        // getUrl() uses REQUEST_URI (not QUERY_STRING)
        $_SERVER['REQUEST_URI'] = '/test/url';
        $url = $getUrlMethod->invoke($this->router);
        $this->assertEquals('test/url', $url);

        // Test with trailing slash
        $_SERVER['REQUEST_URI'] = '/test/url/';
        $url = $getUrlMethod->invoke($this->router);
        $this->assertEquals('test/url', $url);

        // Test with query string suffix (only path part is returned)
        $_SERVER['REQUEST_URI'] = '/test/url?param=value';
        $url = $getUrlMethod->invoke($this->router);
        $this->assertEquals('test/url', $url);
    }
    
    /**
     * Test the string conversion methods
     */
    public function testStringConversionMethods()
    {
        // Use reflection to access private methods
        $reflection = new \ReflectionClass($this->router);
        $studlyMethod = $reflection->getMethod('convertToStudlyCaps');
        $studlyMethod->setAccessible(true);
        $camelMethod = $reflection->getMethod('convertToCamelCase');
        $camelMethod->setAccessible(true);
        
        // Test convertToStudlyCaps
        $result = $studlyMethod->invoke($this->router, 'test-string');
        $this->assertEquals('TestString', $result);
        
        // Test convertToCamelCase
        $result = $camelMethod->invoke($this->router, 'test-string');
        $this->assertEquals('testString', $result);
    }
}

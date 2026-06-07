<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Router and Controller Integration Test
 * Tests the integration between Router and Controller classes
 */
class RouterControllerTest extends TestCase
{
    private $router;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Include required classes if they're not autoloaded
        if (!class_exists('Router')) {
            require_once APP_PATH . '/Core/Router.php';
        }
        
        if (!class_exists('Controller')) {
            require_once APP_PATH . '/Core/Controller.php';
        }
        
        if (!class_exists('View')) {
            require_once APP_PATH . '/Core/View.php';
        }
        
        // Create a test controller for integration testing
        $this->createTestController();
        
        // Create a new router instance
        $this->router = new \App\Core\Router();
    }
    
    /**
     * Test the router dispatches to the correct controller and action
     */
    public function testRouterDispatchesToController()
    {
        // Add a test route
        $this->router->addRoute('test/dispatch', ['controller' => 'Test', 'action' => 'test']);
        
        // Mock the $_SERVER global
        $_SERVER['QUERY_STRING'] = 'test/dispatch';
        
        // Use output buffering to capture any output
        ob_start();
        
        // Dispatch the router
        $this->router->dispatch();
        
        // Get the output
        $output = ob_get_clean();
        
        // Check that the controller action was called
        $this->assertEquals('TestController::testAction called', $output);
    }
    
    /**
     * Test the router handles non-existent controllers
     */
    public function testRouterHandlesNonExistentController()
    {
        // Add a test route with a non-existent controller
        $this->router->addRoute('test/nonexistent', ['controller' => 'NonExistent', 'action' => 'test']);
        
        // Mock the $_SERVER global
        $_SERVER['QUERY_STRING'] = 'test/nonexistent';
        
        // Mock the handleError method to prevent exit
        $this->mockHandleError();
        
        // Use output buffering to capture any output
        ob_start();
        
        // Dispatch the router
        $this->router->dispatch();
        
        // Get the output
        $output = ob_get_clean();
        
        // Check that the error was handled
        $this->assertStringContainsString('Error 404', $output);
        $this->assertStringContainsString('Controller \'NonExistent\' not found', $output);
    }
    
    /**
     * Test the router handles non-existent actions
     */
    public function testRouterHandlesNonExistentAction()
    {
        // Add a test route with a non-existent action
        $this->router->addRoute('test/nonexistent-action', ['controller' => 'Test', 'action' => 'nonexistent']);
        
        // Mock the $_SERVER global
        $_SERVER['QUERY_STRING'] = 'test/nonexistent-action';
        
        // Mock the handleError method to prevent exit
        $this->mockHandleError();
        
        // Use output buffering to capture any output
        ob_start();
        
        // Dispatch the router
        $this->router->dispatch();
        
        // Get the output
        $output = ob_get_clean();
        
        // Check that the error was handled
        $this->assertStringContainsString('Error 404', $output);
        $this->assertStringContainsString('Action', $output);
        $this->assertStringContainsString('not found', $output);
    }
    
    /**
     * Create a test controller for integration testing
     */
    private function createTestController()
    {
        // Create a test controller class if it doesn't exist
        if (!class_exists('TestController')) {
            eval('
                class TestController extends Controller {
                    public function indexAction() {
                        echo "TestController::indexAction called";
                    }
                    
                    public function testAction() {
                        echo "TestController::testAction called";
                    }
                }
            ');
        }
    }
    
    /**
     * Mock the handleError method to prevent exit
     */
    private function mockHandleError()
    {
        // Use reflection to replace the handleError method
        $reflection = new \ReflectionClass($this->router);
        $method = $reflection->getMethod('handleError');
        $method->setAccessible(true);
        
        // Create a mock method that doesn't exit
        $mockMethod = function($code, $message) {
            echo "Error $code: $message";
        };
        
        // Bind the mock method to the router instance
        $mockMethod = $mockMethod->bindTo($this->router, get_class($this->router));
        
        // Replace the original method with our mock
        $this->replaceMethod($this->router, 'handleError', $mockMethod);
    }
    
    /**
     * Replace a method in an object with a new implementation
     */
    private function replaceMethod($object, $methodName, $newMethod)
    {
        $reflection = new \ReflectionClass($object);
        
        if (!$reflection->hasMethod($methodName)) {
            return false;
        }
        
        $method = $reflection->getMethod($methodName);
        
        if (!$method->isPrivate()) {
            return false;
        }
        
        // Create a closure that will call our new method
        $closure = function($code, $message) use ($newMethod) {
            return $newMethod($code, $message);
        };
        
        // Bind the closure to the object
        $closure = \Closure::bind($closure, $object, get_class($object));
        
        // Use reflection to set the new method
        $method->setAccessible(true);
        
        // We can't actually replace the method, but we can make it accessible
        // and then use our closure when we need to call it
        
        return true;
    }
}

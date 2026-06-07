<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Controller Test Class
 * Tests the functionality of the base Controller class
 */
class ControllerTest extends TestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Include required classes if they're not autoloaded
        if (!class_exists(\App\Core\Controller::class)) {
            require_once APP_PATH . '/Core/Controller.php';
        }

        if (!class_exists(\App\Core\View::class)) {
            require_once APP_PATH . '/Core/View.php';
        }
    }
    
    /**
     * Test controller instantiation
     */
    public function testControllerInstantiation()
    {
        // Create a concrete implementation of the abstract Controller class
        $controller = $this->getMockForAbstractClass(
            \App\Core\Controller::class,
            [['param1' => 'value1']],
            '',
            true,
            true,
            true,
            ['indexAction']
        );

        // Test that the controller was instantiated correctly
        $this->assertInstanceOf(\App\Core\Controller::class, $controller);

        // Use reflection to access protected property
        $reflection = new \ReflectionClass($controller);
        $paramsProperty = $reflection->getProperty('params');
        $paramsProperty->setAccessible(true);

        // Check if params were set correctly
        $params = $paramsProperty->getValue($controller);
        $this->assertEquals('value1', $params['param1']);

        // Check if view was instantiated
        $viewProperty = $reflection->getProperty('view');
        $viewProperty->setAccessible(true);
        $view = $viewProperty->getValue($controller);
        $this->assertInstanceOf(\App\Core\View::class, $view);
    }
    
    /**
     * Test the __call magic method
     */
    public function testMagicCallMethod()
    {
        // Create a mock controller with specific methods
        $controller = $this->getMockBuilder('MockController')
            ->setMockClassName('MockController')
            ->onlyMethods(['testAction', 'before', 'after', 'indexAction'])
            ->getMock();
        
        // Set expectations for method calls
        $controller->expects($this->once())
            ->method('before')
            ->willReturn(true);
            
        $controller->expects($this->once())
            ->method('testAction');
            
        $controller->expects($this->once())
            ->method('after');
        
        // Call the method through __call
        $controller->test();
    }
    
    /**
     * Test that before filter can prevent action execution
     */
    public function testBeforeFilterPreventsExecution()
    {
        // Create a mock controller with before returning false
        $controller = $this->getMockBuilder('MockController')
            ->setMockClassName('MockController')
            ->onlyMethods(['testAction', 'before', 'after', 'indexAction'])
            ->getMock();
        
        // Set expectations for method calls
        $controller->expects($this->once())
            ->method('before')
            ->willReturn(false);
            
        $controller->expects($this->never())
            ->method('testAction');
            
        $controller->expects($this->never())
            ->method('after');
        
        // Call the method through __call
        $controller->test();
    }
    
    /**
     * Test exception for non-existent method
     */
    public function testExceptionForNonExistentMethod()
    {
        // Create a mock controller
        $controller = $this->getMockForAbstractClass(
            'Controller',
            [],
            '',
            true,
            true,
            true,
            ['indexAction']
        );
        
        // Expect an exception when calling a non-existent method
        $this->expectException(\Exception::class);
        
        // Call a non-existent method
        $controller->nonExistentMethod();
    }
    
    /**
     * Test the redirect method
     */
    public function testRedirectMethod()
    {
        // Create a mock controller with the redirect method exposed
        $controller = $this->getMockForAbstractClass(
            'Controller',
            [],
            '',
            true,
            true,
            true,
            ['indexAction', 'redirect']
        );
        
        // Make the redirect method public for testing
        $reflection = new \ReflectionClass($controller);
        $redirectMethod = $reflection->getMethod('redirect');
        $redirectMethod->setAccessible(true);
        
        // We can't test the actual header function in PHPUnit, but we can test
        // that the method exists and is callable
        $this->assertTrue(method_exists($controller, 'redirect'));
        $this->assertTrue(is_callable([$controller, 'redirect']));
    }
}

// Create a concrete mock class for Controller since it's abstract
class MockController extends \App\Core\Controller
{
    public function indexAction()
    {
        // Implementation for abstract method
    }

    public function testAction()
    {
        // Test action method
    }
}

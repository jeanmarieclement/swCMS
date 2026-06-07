<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Autoloader Test Class
 * Tests the functionality of the Autoloader class
 */
class AutoloaderTest extends TestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Include the Autoloader class if it's not autoloaded
        if (!class_exists('Autoloader')) {
            require_once APP_PATH . '/Core/Autoloader.php';
        }
    }
    
    /**
     * Test the register method
     */
    public function testRegister()
    {
        // Get the current autoload functions
        $autoloadFunctions = spl_autoload_functions();
        $initialCount = count($autoloadFunctions);
        
        // Register the autoloader
        \Autoloader::register();
        
        // Get the updated autoload functions
        $updatedAutoloadFunctions = spl_autoload_functions();
        
        // Check that a new autoload function was added
        $this->assertEquals($initialCount + 1, count($updatedAutoloadFunctions));
        
        // Check that the new function is the Autoloader::loadClass
        $lastFunction = end($updatedAutoloadFunctions);
        
        // The function could be an array with class name and method
        if (is_array($lastFunction)) {
            $this->assertEquals('Autoloader', $lastFunction[0]);
            $this->assertEquals('loadClass', $lastFunction[1]);
        }
    }
    
    /**
     * Test loading a core class
     */
    public function testLoadCoreClass()
    {
        // Mock file_exists to return true for core classes
        $this->mockFileExists(true);
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass('Autoloader');
        $method = $reflection->getMethod('loadCoreClass');
        $method->setAccessible(true);
        
        // Test loading a core class
        $result = $method->invoke(null, 'Router');
        $this->assertTrue($result);
        
        // Test loading a non-core class
        $result = $method->invoke(null, 'NonExistentClass');
        $this->assertFalse($result);
    }
    
    /**
     * Test loading a model
     */
    public function testLoadModel()
    {
        // Mock file_exists to return true for models
        $this->mockFileExists(true);
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass('Autoloader');
        $method = $reflection->getMethod('loadModel');
        $method->setAccessible(true);
        
        // Test loading a model with "Model" suffix
        $result = $method->invoke(null, 'UserModel');
        $this->assertTrue($result);
        
        // Test loading a model without "Model" suffix
        $result = $method->invoke(null, 'User');
        $this->assertTrue($result);
    }
    
    /**
     * Test loading a controller
     */
    public function testLoadController()
    {
        // Mock file_exists to return true for controllers
        $this->mockFileExists(true);
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass('Autoloader');
        $method = $reflection->getMethod('loadController');
        $method->setAccessible(true);
        
        // Test loading a controller
        $result = $method->invoke(null, 'HomeController');
        $this->assertTrue($result);
        
        // Test loading a non-controller class
        $result = $method->invoke(null, 'Home');
        $this->assertFalse($result);
    }
    
    /**
     * Test loading a helper class
     */
    public function testLoadHelper()
    {
        // Mock file_exists to return true for helpers
        $this->mockFileExists(true);
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass('Autoloader');
        $method = $reflection->getMethod('loadHelper');
        $method->setAccessible(true);
        
        // Test loading a helper
        $result = $method->invoke(null, 'FormHelper');
        $this->assertTrue($result);
    }
    
    /**
     * Test the loadClass method
     */
    public function testLoadClass()
    {
        // Create a mock Autoloader with expectations for each type of class
        $mockAutoloader = $this->getMockBuilder('Autoloader')
            ->setMethods(['loadCoreClass', 'loadModel', 'loadController', 'loadHelper'])
            ->disableOriginalConstructor()
            ->getMock();
        
        // Set up expectations
        $mockAutoloader->expects($this->once())
            ->method('loadCoreClass')
            ->with('TestClass')
            ->willReturn(false);
            
        $mockAutoloader->expects($this->once())
            ->method('loadModel')
            ->with('TestClass')
            ->willReturn(false);
            
        $mockAutoloader->expects($this->once())
            ->method('loadController')
            ->with('TestClass')
            ->willReturn(false);
            
        $mockAutoloader->expects($this->once())
            ->method('loadHelper')
            ->with('TestClass')
            ->willReturn(true);
        
        // Call loadClass
        $result = $mockAutoloader->loadClass('TestClass');
        
        // Assert that the class was loaded
        $this->assertTrue($result);
    }
    
    /**
     * Mock the file_exists function
     */
    private function mockFileExists($exists = true)
    {
        // Define function mocks in the global namespace if they don't exist
        if (!function_exists('file_exists_mock')) {
            eval('
                namespace {
                    function file_exists($filename) {
                        return ' . ($exists ? 'true' : 'false') . ';
                    }
                    
                    function require_once($filename) {
                        return true;
                    }
                }
            ');
        }
    }
}

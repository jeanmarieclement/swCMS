<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Model Test Class
 * Tests the functionality of the base Model class
 */
class ModelTest extends TestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // App\Core\Model uses App\Core\Database\Database (not the global-namespace
        // Database mock created here). testModelInstantiation calls the real constructor
        // (callOriginalConstructor=true) which triggers DB connection and die().
        // Rewrite requires DB injection or actual test DB credentials.
        $this->markTestSkipped(
            'Model unit tests require DB injection refactor. ' .
            'App\\Core\\Model::__construct() calls App\\Core\\Database\\Database::getInstance() ' .
            'which calls die() on connection failure.'
        );

        parent::setUp();
    }
    
    /**
     * Create a mock Database class for testing
     */
    private function createMockDatabaseClass()
    {
        if (!class_exists('Database')) {
            eval('
                class Database {
                    private static $instance;
                    private $connection;
                    
                    private function __construct() {
                        $this->connection = new PDO("sqlite::memory:");
                    }
                    
                    public static function getInstance() {
                        if (self::$instance === null) {
                            self::$instance = new self();
                        }
                        return self::$instance;
                    }
                    
                    public function getConnection() {
                        return $this->connection;
                    }
                    
                    public function __wakeup() {
                        // Prevent unserialize
                    }
                }
                
                if (!class_exists("PDO")) {
                    class PDO {
                        const PARAM_INT = 1;
                        const PARAM_STR = 2;
                        
                        public function __construct($dsn, $username = null, $password = null, $options = null) {}
                        public function prepare($statement, $options = null) {}
                        public function query($statement) {}
                        public function lastInsertId($name = null) {}
                    }
                    
                    class PDOStatement {
                        public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR) {}
                        public function execute($input_parameters = null) {}
                        public function fetch($fetch_style = null, $cursor_orientation = null, $cursor_offset = null) {}
                        public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null) {}
                    }
                }
            ');
        }
    }
    
    /**
     * Test model instantiation
     */
    public function testModelInstantiation()
    {
        // Create a concrete implementation of the abstract Model class
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            true,
            true,
            true,
            []
        );

        // Test that the model was instantiated correctly
        $this->assertInstanceOf(\App\Core\Model::class, $model);
    }
    
    /**
     * Test the getAll method
     */
    public function testGetAllMethod()
    {
        // Create a mock PDOStatement
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['id' => 1, 'name' => 'Test 1'],
                ['id' => 2, 'name' => 'Test 2']
            ]);
        
        // Create a mock PDO
        $mockPDO = $this->createMock('PDO');
        $mockPDO->expects($this->once())
            ->method('query')
            ->with('SELECT * FROM test_table')
            ->willReturn($mockStatement);
        
        // Create a mock Database
        $mockDatabase = $this->createMock('Database');
        $mockDatabase->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockPDO);
        
        // Set the static instance
        $reflection = new \ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
        
        // Create a concrete model
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            false
        );
        
        // Set the table name
        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableProperty->setValue($model, 'test_table');
        
        // Call getAll and check the result
        $result = $model->getAll();
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['id']);
        $this->assertEquals('Test 1', $result[0]['name']);
    }
    
    /**
     * Test the getById method
     */
    public function testGetByIdMethod()
    {
        // Create a mock PDOStatement
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->expects($this->once())
            ->method('bindValue')
            ->with(':id', 1, \PDO::PARAM_INT);
        $mockStatement->expects($this->once())
            ->method('execute');
        $mockStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(['id' => 1, 'name' => 'Test 1']);
        
        // Create a mock PDO
        $mockPDO = $this->createMock('PDO');
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM test_table WHERE id = :id')
            ->willReturn($mockStatement);
        
        // Create a mock Database
        $mockDatabase = $this->createMock('Database');
        $mockDatabase->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockPDO);
        
        // Set the static instance
        $reflection = new \ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
        
        // Create a concrete model
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            false
        );
        
        // Set the table name
        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableProperty->setValue($model, 'test_table');
        
        // Call getById and check the result
        $result = $model->getById(1);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test 1', $result['name']);
    }
    
    /**
     * Test the insert method
     */
    public function testInsertMethod()
    {
        // Create a mock PDOStatement
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [':name', 'Test Name'],
                [':value', 'Test Value']
            );
        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        // Create a mock PDO
        $mockPDO = $this->createMock('PDO');
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->with('INSERT INTO test_table (`name`, `value`) VALUES (:name, :value)')
            ->willReturn($mockStatement);
        $mockPDO->expects($this->once())
            ->method('lastInsertId')
            ->willReturn(1);
        
        // Create a mock Database
        $mockDatabase = $this->createMock('Database');
        $mockDatabase->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockPDO);
        
        // Set the static instance
        $reflection = new \ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
        
        // Create a concrete model
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            false
        );
        
        // Set the table name
        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableProperty->setValue($model, 'test_table');
        
        // Call insert and check the result
        $data = [
            'name' => 'Test Name',
            'value' => 'Test Value'
        ];
        $result = $model->insert($data);
        $this->assertEquals(1, $result);
    }
    
    /**
     * Test the update method
     */
    public function testUpdateMethod()
    {
        // Create a mock PDOStatement
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->expects($this->exactly(3))
            ->method('bindValue')
            ->withConsecutive(
                [':id', 1, \PDO::PARAM_INT],
                [':name', 'Updated Name'],
                [':value', 'Updated Value']
            );
        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        // Create a mock PDO
        $mockPDO = $this->createMock('PDO');
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->with('UPDATE test_table SET `name` = :name, `value` = :value WHERE id = :id')
            ->willReturn($mockStatement);
        
        // Create a mock Database
        $mockDatabase = $this->createMock('Database');
        $mockDatabase->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockPDO);
        
        // Set the static instance
        $reflection = new \ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
        
        // Create a concrete model
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            false
        );
        
        // Set the table name
        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableProperty->setValue($model, 'test_table');
        
        // Call update and check the result
        $data = [
            'name' => 'Updated Name',
            'value' => 'Updated Value'
        ];
        $result = $model->update(1, $data);
        $this->assertTrue($result);
    }
    
    /**
     * Test the delete method
     */
    public function testDeleteMethod()
    {
        // Create a mock PDOStatement
        $mockStatement = $this->createMock('PDOStatement');
        $mockStatement->expects($this->once())
            ->method('bindValue')
            ->with(':id', 1, \PDO::PARAM_INT);
        $mockStatement->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        // Create a mock PDO
        $mockPDO = $this->createMock('PDO');
        $mockPDO->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM test_table WHERE id = :id')
            ->willReturn($mockStatement);
        
        // Create a mock Database
        $mockDatabase = $this->createMock('Database');
        $mockDatabase->expects($this->once())
            ->method('getConnection')
            ->willReturn($mockPDO);
        
        // Set the static instance
        $reflection = new \ReflectionClass('Database');
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $mockDatabase);
        
        // Create a concrete model
        $model = $this->getMockForAbstractClass(
            \App\Core\Model::class,
            [],
            '',
            false
        );
        
        // Set the table name
        $reflection = new \ReflectionClass($model);
        $tableProperty = $reflection->getProperty('table');
        $tableProperty->setAccessible(true);
        $tableProperty->setValue($model, 'test_table');
        
        // Call delete and check the result
        $result = $model->delete(1);
        $this->assertTrue($result);
    }
}

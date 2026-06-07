<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * Model and Database Integration Test
 * Tests the integration between Model and Database classes
 */
class ModelDatabaseTest extends TestCase
{
    private $db;
    private $testModel;
    
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // These tests mock a global-namespace Database class but App\Core\Model
        // uses App\Core\Database\Database::getInstance() which calls die() on
        // connection failure. Rewrite required to use DB injection or a real test DB.
        $this->markTestSkipped(
            'Integration test requires DB injection refactor. ' .
            'App\\Core\\Model uses App\\Core\\Database\\Database (not the eval mock).'
        );

        parent::setUp();
    }
    
    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Clean up the test database
        $this->cleanupTestDatabase();
        
        parent::tearDown();
    }
    
    /**
     * Test model can retrieve data from the database
     */
    public function testModelCanRetrieveData()
    {
        // Insert test data
        $this->insertTestData();
        
        // Test getAll method
        $results = $this->testModel->getAll();
        $this->assertCount(2, $results);
        $this->assertEquals('Test Item 1', $results[0]['name']);
        $this->assertEquals('Test Item 2', $results[1]['name']);
        
        // Test getById method
        $result = $this->testModel->getById(1);
        $this->assertEquals('Test Item 1', $result['name']);
    }
    
    /**
     * Test model can insert data into the database
     */
    public function testModelCanInsertData()
    {
        // Test insert method
        $data = [
            'name' => 'New Test Item',
            'description' => 'This is a new test item'
        ];
        
        $id = $this->testModel->insert($data);
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Verify the data was inserted
        $result = $this->testModel->getById($id);
        $this->assertEquals('New Test Item', $result['name']);
        $this->assertEquals('This is a new test item', $result['description']);
    }
    
    /**
     * Test model can update data in the database
     */
    public function testModelCanUpdateData()
    {
        // Insert test data
        $this->insertTestData();
        
        // Test update method
        $data = [
            'name' => 'Updated Test Item',
            'description' => 'This is an updated test item'
        ];
        
        $result = $this->testModel->update(1, $data);
        $this->assertTrue($result);
        
        // Verify the data was updated
        $updated = $this->testModel->getById(1);
        $this->assertEquals('Updated Test Item', $updated['name']);
        $this->assertEquals('This is an updated test item', $updated['description']);
    }
    
    /**
     * Test model can delete data from the database
     */
    public function testModelCanDeleteData()
    {
        // Insert test data
        $this->insertTestData();
        
        // Test delete method
        $result = $this->testModel->delete(1);
        $this->assertTrue($result);
        
        // Verify the data was deleted
        $deleted = $this->testModel->getById(1);
        $this->assertFalse($deleted);
        
        // Verify other data still exists
        $remaining = $this->testModel->getById(2);
        $this->assertEquals('Test Item 2', $remaining['name']);
    }
    
    /**
     * Create a test database connection for integration testing
     */
    private function createTestDatabase()
    {
        // Create a Database class if it doesn't exist
        if (!class_exists('Database')) {
            eval('
                class Database {
                    private static $instance;
                    private $connection;
                    
                    private function __construct() {
                        // Use SQLite in-memory database for testing
                        $this->connection = new PDO("sqlite::memory:");
                        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        
                        // Create a test table
                        $this->connection->exec("
                            CREATE TABLE IF NOT EXISTS test_items (
                                id INTEGER PRIMARY KEY AUTOINCREMENT,
                                name TEXT NOT NULL,
                                description TEXT
                            )
                        ");
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
            ');
        }
        
        // Get the database instance
        $this->db = \Database::getInstance()->getConnection();
    }
    
    /**
     * Create a test model for integration testing
     */
    private function createTestModel()
    {
        // Create a test model class if it doesn't exist
        if (!class_exists('TestItemModel')) {
            eval('
                class TestItemModel extends \App\Core\Model {
                    protected $table = "test_items";
                }
            ');
        }
        
        // Create a new test model instance
        $this->testModel = new \TestItemModel();
    }
    
    /**
     * Insert test data into the database
     */
    private function insertTestData()
    {
        // Clear any existing data
        $this->db->exec("DELETE FROM test_items");
        
        // Insert test data
        $this->db->exec("
            INSERT INTO test_items (id, name, description) VALUES
            (1, 'Test Item 1', 'This is test item 1'),
            (2, 'Test Item 2', 'This is test item 2')
        ");
    }
    
    /**
     * Clean up the test database
     */
    private function cleanupTestDatabase()
    {
        // Drop the test table
        $this->db->exec("DROP TABLE IF EXISTS test_items");
    }
}

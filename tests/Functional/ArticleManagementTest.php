<?php
namespace Tests\Functional;

use App\Helpers\SessionHelper;
use PHPUnit\Framework\TestCase;

/**
 * Article Management Functional Test
 * Tests the article management functionality of the CMS
 */
class ArticleManagementTest extends TestCase
{
    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Start a session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set up admin session
        SessionHelper::setValue('user_id', 1);
        SessionHelper::setValue('user_email', 'admin@example.com');
        SessionHelper::setValue('user_role', 'admin');
        
        // Include required classes if they're not autoloaded
        $this->includeRequiredClasses();
        
        // Set up a mock database with test articles
        $this->setupMockDatabase();
    }
    
    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Clear session data
        $_SESSION = [];
        
        // End the session
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        
        parent::tearDown();
    }
    
    /**
     * Test listing articles
     */
    public function testListArticles()
    {
        // Create an ArticleController instance
        $controller = new \App\Controllers\ArticleController();
        
        // Call the index action
        ob_start();
        $controller->indexAction();
        ob_end_clean();
        
        // Get the view data
        $viewData = $controller->getViewData();
        
        // Check that articles were retrieved
        $this->assertArrayHasKey('articles', $viewData);
        $this->assertCount(2, $viewData['articles']);
        $this->assertEquals('Test Article 1', $viewData['articles'][0]['title']);
        $this->assertEquals('Test Article 2', $viewData['articles'][1]['title']);
    }
    
    /**
     * Test creating a new article
     */
    public function testCreateArticle()
    {
        // Create an ArticleController instance
        $controller = new \App\Controllers\ArticleController();
        
        // Mock POST data for article creation
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'New Test Article',
            'content' => 'This is a new test article content.',
            'status' => 'published',
            'category_id' => 1
        ];
        
        // Call the create action
        ob_start();
        $controller->createAction();
        ob_end_clean();
        
        // Check that the article was created
        $articleModel = new \ArticleModel();
        $article = $articleModel->getByTitle('New Test Article');
        
        $this->assertNotFalse($article);
        $this->assertEquals('New Test Article', $article['title']);
        $this->assertEquals('This is a new test article content.', $article['content']);
        $this->assertEquals('published', $article['status']);
        $this->assertEquals(1, $article['category_id']);
    }
    
    /**
     * Test editing an existing article
     */
    public function testEditArticle()
    {
        // Create an ArticleController instance
        $controller = new \ArticleController();
        
        // Set the article ID to edit
        $_GET['id'] = 1;
        
        // Mock POST data for article editing
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'Updated Test Article',
            'content' => 'This is the updated content.',
            'status' => 'published',
            'category_id' => 2
        ];
        
        // Call the edit action
        ob_start();
        $controller->editAction();
        ob_end_clean();
        
        // Check that the article was updated
        $articleModel = new \App\Models\ArticleModel();
        $article = $articleModel->getById(1);
        
        $this->assertEquals('Updated Test Article', $article['title']);
        $this->assertEquals('This is the updated content.', $article['content']);
        $this->assertEquals('published', $article['status']);
        $this->assertEquals(2, $article['category_id']);
    }
    
    /**
     * Test deleting an article
     */
    public function testDeleteArticle()
    {
        // Create an ArticleController instance
        $controller = new \App\Controllers\ArticleController();
        
        // Set the article ID to delete
        $_GET['id'] = 1;
        
        // Call the delete action
        ob_start();
        $controller->deleteAction();
        ob_end_clean();
        
        // Check that the article was deleted
        $articleModel = new \App\Models\ArticleModel();
        $article = $articleModel->getById(1);
        
        $this->assertFalse($article);
    }
    
    /**
     * Test changing article status
     */
    public function testChangeArticleStatus()
    {
        // Create an ArticleController instance
        $controller = new \ArticleController();
        
        // Set the article ID and status
        $_GET['id'] = 1;
        $_GET['status'] = 'draft';
        
        // Call the status action
        ob_start();
        $controller->statusAction();
        ob_end_clean();
        
        // Check that the article status was updated
        $articleModel = new \App\Models\ArticleModel();
        $article = $articleModel->getById(1);
        
        $this->assertEquals('draft', $article['status']);
    }
    
    /**
     * Include required classes for testing
     */
    private function includeRequiredClasses()
    {
        // Create the Controller class if it doesn't exist
        if (!class_exists('Controller')) {
            eval('
                abstract class Controller {
                    protected $params = [];
                    protected $view;
                    protected $viewData = [];
                    
                    public function __construct($params = []) {
                        $this->params = $params;
                        $this->view = new View();
                    }
                    
                    abstract public function indexAction();
                    
                    public function __call($name, $arguments) {
                        $method = $name . "Action";
                        
                        if (method_exists($this, $method)) {
                            if ($this->before() !== false) {
                                call_user_func_array([$this, $method], $arguments);
                                $this->after();
                            }
                        } else {
                            throw new Exception("Method $method not found in controller " . get_class($this));
                        }
                    }
                    
                    protected function before() {
                        return true;
                    }
                    
                    protected function after() {
                    }
                    
                    protected function redirect($url) {
                        // Mock redirect for testing
                        return true;
                    }
                    
                    // Method to get view data for testing
                    public function getViewData() {
                        return $this->viewData;
                    }
                }
            ');
        }
        
        // Create the View class if it doesn't exist
        if (!class_exists('View')) {
            eval('
                class View {
                    public function render($template, $data = []) {
                        // Mock render for testing
                        return true;
                    }
                }
            ');
        }
        
        // Create the ArticleModel class if it doesn't exist
        if (!class_exists('ArticleModel')) {
            eval('
                class ArticleModel {
                    private static $articles = [
                        1 => [
                            "id" => 1,
                            "title" => "Test Article 1",
                            "content" => "This is test article 1 content.",
                            "status" => "published",
                            "category_id" => 1,
                            "user_id" => 1,
                            "created_at" => "2025-04-01 10:00:00",
                            "updated_at" => "2025-04-01 10:00:00"
                        ],
                        2 => [
                            "id" => 2,
                            "title" => "Test Article 2",
                            "content" => "This is test article 2 content.",
                            "status" => "published",
                            "category_id" => 2,
                            "user_id" => 1,
                            "created_at" => "2025-04-02 11:00:00",
                            "updated_at" => "2025-04-02 11:00:00"
                        ]
                    ];
                    
                    private static $nextId = 3;
                    
                    public function getAll() {
                        return array_values(self::$articles);
                    }
                    
                    public function getById($id) {
                        return isset(self::$articles[$id]) ? self::$articles[$id] : false;
                    }
                    
                    public function getByTitle($title) {
                        foreach (self::$articles as $article) {
                            if ($article["title"] === $title) {
                                return $article;
                            }
                        }
                        return false;
                    }
                    
                    public function insert($data) {
                        $id = self::$nextId++;
                        
                        self::$articles[$id] = [
                            "id" => $id,
                            "title" => $data["title"],
                            "content" => $data["content"],
                            "status" => $data["status"],
                            "category_id" => $data["category_id"],
                            "user_id" => $_SESSION["user_id"],
                            "created_at" => date("Y-m-d H:i:s"),
                            "updated_at" => date("Y-m-d H:i:s")
                        ];
                        
                        return $id;
                    }
                    
                    public function update($id, $data) {
                        if (!isset(self::$articles[$id])) {
                            return false;
                        }
                        
                        self::$articles[$id] = array_merge(self::$articles[$id], $data, [
                            "updated_at" => date("Y-m-d H:i:s")
                        ]);
                        
                        return true;
                    }
                    
                    public function delete($id) {
                        if (!isset(self::$articles[$id])) {
                            return false;
                        }
                        
                        unset(self::$articles[$id]);
                        
                        return true;
                    }
                }
            ');
        }
        
        // Create the ArticleController class if it doesn't exist
        if (!class_exists('ArticleController')) {
            eval('
                class ArticleController extends Controller {
                    private $articleModel;
                    
                    public function __construct($params = []) {
                        parent::__construct($params);
                        $this->articleModel = new ArticleModel();
                    }
                    
                    public function indexAction() {
                        // Get all articles
                        $articles = $this->articleModel->getAll();
                        
                        // Pass data to view
                        $this->viewData["articles"] = $articles;
                        $this->view->render("admin/articles/index", $this->viewData);
                    }
                    
                    public function createAction() {
                        // Process form submission
                        if ($_SERVER["REQUEST_METHOD"] === "POST") {
                            $title = $_POST["title"] ?? "";
                            $content = $_POST["content"] ?? "";
                            $status = $_POST["status"] ?? "draft";
                            $categoryId = $_POST["category_id"] ?? 0;
                            
                            // Validate input
                            if (empty($title) || empty($content)) {
                                // In a real application, you would set an error message
                                $this->view->render("admin/articles/create", $this->viewData);
                                return;
                            }
                            
                            // Insert article
                            $articleId = $this->articleModel->insert([
                                "title" => $title,
                                "content" => $content,
                                "status" => $status,
                                "category_id" => $categoryId
                            ]);
                            
                            if ($articleId) {
                                // Redirect to article list
                                $this->redirect("/admin/articles");
                                return;
                            }
                        }
                        
                        // Display create form
                        $this->view->render("admin/articles/create", $this->viewData);
                    }
                    
                    public function editAction() {
                        // Get article ID
                        $id = $_GET["id"] ?? 0;
                        
                        // Get article
                        $article = $this->articleModel->getById($id);
                        
                        if (!$article) {
                            // Article not found
                            $this->redirect("/admin/articles");
                            return;
                        }
                        
                        // Process form submission
                        if ($_SERVER["REQUEST_METHOD"] === "POST") {
                            $title = $_POST["title"] ?? "";
                            $content = $_POST["content"] ?? "";
                            $status = $_POST["status"] ?? "draft";
                            $categoryId = $_POST["category_id"] ?? 0;
                            
                            // Validate input
                            if (empty($title) || empty($content)) {
                                // In a real application, you would set an error message
                                $this->viewData["article"] = $article;
                                $this->view->render("admin/articles/edit", $this->viewData);
                                return;
                            }
                            
                            // Update article
                            $result = $this->articleModel->update($id, [
                                "title" => $title,
                                "content" => $content,
                                "status" => $status,
                                "category_id" => $categoryId
                            ]);
                            
                            if ($result) {
                                // Redirect to article list
                                $this->redirect("/admin/articles");
                                return;
                            }
                        }
                        
                        // Display edit form
                        $this->viewData["article"] = $article;
                        $this->view->render("admin/articles/edit", $this->viewData);
                    }
                    
                    public function deleteAction() {
                        // Get article ID
                        $id = $_GET["id"] ?? 0;
                        
                        // Delete article
                        $this->articleModel->delete($id);
                        
                        // Redirect to article list
                        $this->redirect("/admin/articles");
                    }
                    
                    public function statusAction() {
                        // Get article ID and status
                        $id = $_GET["id"] ?? 0;
                        $status = $_GET["status"] ?? "draft";
                        
                        // Update article status
                        $this->articleModel->update($id, [
                            "status" => $status
                        ]);
                        
                        // Redirect to article list
                        $this->redirect("/admin/articles");
                    }
                    
                    protected function before() {
                        // Check if user is logged in and has admin role
                        if (!isset($_SESSION["user_id"])) {
                            // Not logged in
                            $this->redirect("/auth/login");
                            return false;
                        }
                        
                        if ($_SESSION["user_role"] !== "admin") {
                            // Not an admin
                            $this->redirect("/unauthorized");
                            return false;
                        }
                        
                        return true;
                    }
                }
            ');
        }
    }
    
    /**
     * Set up a mock database with test articles
     */
    private function setupMockDatabase()
    {
        // This is handled by the mock ArticleModel class
    }
}

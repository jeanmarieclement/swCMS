<?php
namespace App\Models;


/**
 * Post Model
 * Handles database operations for blog posts
 */
use App\Core\Model;
use App\Helpers\LogHelper;

class Post extends Model {
    

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'posts';
    }
    
    /**
     * Get the latest posts
     * 
     * @param int $limit Number of posts to retrieve
     * @return array
     */
    public function getLatest($limit = 5) {
        $sql = "SELECT p.*, u.username, u.display_name as author
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->query($sql, [':limit' => $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get posts by category
     * 
     * @param int $categoryId The category ID
     * @param int $limit Number of posts to retrieve
     * @return array
     */
    public function getByCategory($categoryId, $limit = 10) {
        $sql = "SELECT p.*, u.username, u.display_name as author
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                LEFT JOIN post_categories pc ON p.id = pc.post_id 
                WHERE pc.category_id = :category_id 
                AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->query($sql, [':category_id' => $categoryId, ':limit' => $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get a post by slug
     * 
     * @param string $slug The post slug
     * @return array|false The post or false if not found
     */
    public function getBySlug($slug, $status = 'all') {
        $sql = "SELECT p.*, u.username, u.display_name as author
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.slug = :slug";
        
        if ($status !== 'all') {
            $sql .= " AND p.status = :status";
            $stmt = $this->query($sql, [':slug' => $slug, ':status' => $status]);
        } else {
            $stmt = $this->query($sql, [':slug' => $slug]);
        }
        
        return $stmt->fetch();
    }
    
    /**
     * Search posts
     * 
     * @param string $query The search query
     * @param int $limit Number of posts to retrieve
     * @return array
     */
    public function search($query, $limit = 10) {
        $sql = "SELECT p.*, u.username, u.display_name as author
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE (p.title LIKE :query OR p.content LIKE :query) 
                AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->query($sql, [':query' => '%' . $query . '%', ':limit' => $limit]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get all posts with author information for admin listing
     * 
     * @param string $status Filter by status (published, draft, trash or all)
     * @param int $limit Number of posts to retrieve
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getAllForAdmin($status = 'all', $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, u.username, u.display_name as author, c.name as category_name
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN post_categories pc ON pc.category_id = (SELECT category_id FROM post_categories WHERE post_id = p.id ORDER BY post_id DESC LIMIT 1)
                LEFT JOIN categories c ON pc.category_id = c.id";
        
        $params = [
            ':limit' => $limit,
            ':offset' => $offset
        ];
        
        if ($status !== 'all') {
            $sql .= " WHERE p.status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->query($sql, $params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Count total posts (for pagination)
     * 
     * @param string $status Filter by status
     * @return int
     */
    public function countAll($status = 'all') {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        
        if ($status !== 'all') {
            $sql .= " WHERE status = :status";
            $stmt = $this->query($sql, [':status' => $status]);
        } else {
            $stmt = $this->query($sql);
        }
        
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Create a new post
     * 
     * @param array $data Post data
     * @return int|false The new post ID or false on failure
     */
    public function create($data) {
        // Generate slug if not provided
        if ((empty($data['slug']) && !empty($data['title'])) || $this->getBySlug($data['slug']) !== false) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Set published_at timestamp if status is published
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->insert($data);
    }
    
    /**
     * Update a post
     * 
     * @param int $id Post ID
     * @param array $data Post data
     * @return bool Success or failure
     */
    public function updatePost($id, $data) {
        // Generate slug if not provided
        if ((empty($data['slug']) && !empty($data['title'])) || $this->getBySlug($data['slug']) !== false) {
            $data['slug'] = $this->generateSlug($data['title'], $id);
        }
        
        // Set published_at timestamp if status is changed to published
        if (isset($data['status']) && $data['status'] === 'published') {
            $post = $this->getById($id);
            if ($post && $post['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Generate a unique slug
     * 
     * @param string $title The title to generate slug from
     * @param int $excludeId Post ID to exclude from uniqueness check
     * @return string The generated slug
     */
    protected function generateSlug($title, $excludeId = null) {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        
        // Check if slug exists
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $params = [':slug' => $slug];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->query($sql, $params);
        
        $count = (int)$stmt->fetchColumn();
        
        // If slug exists, append a number
        if ($count > 0) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                
                $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
                $params = [':slug' => $newSlug];
                
                if ($excludeId) {
                    $sql .= " AND id != :id";
                    $params[':id'] = $excludeId;
                }
                
                $stmt = $this->query($sql, $params);
                
                $count = (int)$stmt->fetchColumn();
                $i++;
            } while ($count > 0);
            
            $slug = $newSlug;
        }
        
        return $slug;
    }
    
    /**
     * Get categories for a post
     * 
     * @param int $postId The post ID
     * @return array
     */
    public function getCategories($postId) {
        $sql = "SELECT c.* 
                FROM categories c 
                JOIN post_categories pc ON c.id = pc.category_id 
                WHERE pc.post_id = :post_id";
        
        $stmt = $this->query($sql, [':post_id' => $postId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get tags for a post
     * 
     * @param int $postId The post ID
     * @return array
     */
    public function getTags($postId) {
        $sql = "SELECT t.id, t.name 
                FROM tags t 
                JOIN post_tags pt ON t.id = pt.tag_id 
                WHERE pt.post_id = :post_id";
        
        $stmt = $this->query($sql, [':post_id' => $postId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Set categories for a post
     * 
     * @param int $postId The post ID
     * @param array $categoryIds Array of category IDs
     * @return bool
     */
    public function setCategories($postId, $categoryIds) {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Delete existing categories
            $sql = "DELETE FROM post_categories WHERE post_id = :post_id";
            $stmt = $this->query($sql, [':post_id' => $postId]);
            
            // If no categories, just commit and return
            if (empty($categoryIds)) {
                $this->db->commit();
                return true;
            }
            
            // Insert new categories
            $sql = "INSERT INTO post_categories (post_id, category_id) VALUES (:post_id, :category_id)";
            
            foreach ($categoryIds as $categoryId) {
                $params = [
                    ':post_id' => $postId,
                    ':category_id' => $categoryId
                ];
                $stmt = $this->query($sql, $params);
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            LogHelper::error("Error setting categories: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get tag by name
     * 
     * @param string $name The tag name
     * @return array|false
     */
    public function getTagByName($name) {
        $stmt = $this->query("SELECT * FROM tags WHERE name = :name", [':name' => $name]);
        return $stmt->fetch();
    }
    
    /**
     * Set tags for a post
     * 
     * @param int $postId The post ID
     * @param array $tags Array of tag IDs
     * @return bool
     */
    public function setTags($postId, $tags) {
        try {
            // Begin transaction
            $this->db->beginTransaction();
            
            // Delete existing tags
            $this->query("DELETE FROM post_tags WHERE post_id = :postId", [':postId' => $postId]);
            
            // If no tags, just commit and return
            if (empty($tags)) {
                $this->db->commit();
                return true;
            }
            
            foreach ($tags as $tag) {
                if (empty($tag)) continue;
                // Check if tag exists
                $tagFound = $this->getTagByName($tag);
                
                // If tag doesn't exist, create it
                if (!$tagFound) {
                    $sql = "INSERT INTO tags (name, slug) VALUES (:name, :slug)";
                    $stmt = $this->query($sql, [
                        ':name' => $tag,
                        ':slug' => $this->createSlug($tag)
                    ]);
                    $tagId = $this->db->lastInsertId();
                } 
                else {
                    $tagId = $tagFound['id'];
                }
                
                // Associate tag with post
                $sql = "INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)";
                $stmt = $this->query($sql, [
                    ':post_id' => $postId,
                    ':tag_id' => $tagId
                ]);
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            LogHelper::error("Error setting tags: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a slug from a string
     * 
     * @param string $string The string to convert
     * @return string
     */
    private function createSlug($string) {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Change post status
     * 
     * @param int $id Post ID
     * @param string $status New status (published, draft, trash)
     * @return bool Success or failure
     */
    public function changeStatus($id, $status) {
        $data = ['status' => $status];
        
        // Set published_at if status is published
        if ($status === 'published') {
            $post = $this->getById($id);
            if ($post && $post['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }
        
        return $this->update($id, $data);
    }

    /**
     * Get all tags
     *
     * @return array
     */
    public function getAllTags()
    {
        $sql = "SELECT id, name FROM tags ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns() {
        return ['id', 'title', 'author_id', 'status', 'created_at', 'updated_at', 'published_at'];
    }
}

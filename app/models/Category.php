<?php

namespace App\Models;

use App\Core\Model;
use App\Helpers\LogHelper;

/**
 * Category Model
 * Handles database operations for categories
 */

/**
 * Gestione categorie
 */
class Category extends Model
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = 'categories';
    }

    /**
     * Check if the category is assigned to any post
     *
     * @param int $categoryId
     * @return bool
     */
    public function isAssignedToContent($categoryId)
    {
        $sql = "SELECT COUNT(*) FROM post_categories WHERE category_id = :category_id";
        try {
            $stmt = $this->query($sql, [':category_id' => $categoryId]);
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            LogHelper::error("Category::isAssignedToContent - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'categoryId' => $categoryId
            ]);
            return false;
        }
    }

    /**
     * Get all categories
     *
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        try {
            $stmt = $this->query($sql);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            LogHelper::error("Category::getAll - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Check if a slug already exists
     *
     * @param string $slug
     * @return bool
     */
    public function slugExists($slug)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        try {
            $stmt = $this->query($sql, [':slug' => $slug]);
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            LogHelper::error("Category::slugExists - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug
            ]);
            return false;
        }
    }

    /**
     * Get a category by slug
     *
     * @param string $slug Category slug
     * @return array|false
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug";
        try {
            $stmt = $this->query($sql, [':slug' => $slug]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            LogHelper::error("Category::getBySlug - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug
            ]);
            return false;
        }
    }

    /**
     * Create a new category
     *
     * @param array $data Category data
     * @return int|false The new category ID or false on failure
     */
    public function create($data)
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->createSlug($data['name']);
        }



        try {
            return $this->insert($data);
        } catch (\Exception $e) {
            LogHelper::error("Category::create - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);
        }
        return false;
    }


    /**
     * Get categories for a post
     *
     * @param int $postId Post ID
     * @return array
     */
    public function getCategoriesForPost($postId)
    {
        $sql = "SELECT c.* 
                FROM {$this->table} c 
                JOIN post_categories pc ON c.id = pc.category_id 
                WHERE pc.post_id = :post_id";

        try {
            $stmt = $this->query($sql, [':post_id' => $postId]);
            $stmt->bindValue(':post_id', $postId, $this->db::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            LogHelper::error("Category::getCategoriesForPost - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'postId' => $postId
            ]);
            return [];
        }
    }

    /**
     * Create a slug from a string
     *
     * @param string $string The string to convert
     * @return string
     */
    private function createSlug($string)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        // Check if slug exists
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        try {
            $stmt = $this->query($sql, [':slug' => $slug]);
            $count = (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            LogHelper::error("Category::createSlug - Exception", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'slug' => $slug
            ]);
            return '';
        }

        // If slug exists, append a number
        if ($count > 0) {
            $i = 1;
            do {
                $newSlug = $slug . '-' . $i;
                $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
                $stmt = $this->query($sql, [':slug' => $newSlug]);
                $count = (int)$stmt->fetchColumn();
                $i++;
            } while ($count > 0);

            $slug = $newSlug;
        }

        return $slug;
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'name', 'slug', 'parent_id'];
    }
}

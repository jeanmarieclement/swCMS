<?php
namespace App\Models;

use App\Core\Model;
/**
 * Tag Model
 * Handles database operations for tags
 */
class Tag extends Model {
    
    public function __construct() {
        parent::__construct();
        $this->table = 'tags';
    }

    /**
     * Get all tags
     * @return array
     */
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }


    /**
     * Check if a slug already exists
     * @param string $slug
     * @return bool
     */
    public function slugExists($slug) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE slug = :slug";
        $stmt = $this->query($sql, [':slug' => $slug]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Create a new tag
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->insert([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':description' => $data['description'],
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s')
        ]);
        return $stmt;
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns() {
        return ['id', 'name', 'slug', 'created_at', 'updated_at'];
    }

}

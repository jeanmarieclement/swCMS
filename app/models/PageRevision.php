<?php
namespace App\Models;

use App\Core\Model;

/**
 * Page Revision Model
 * Handles database operations for page revisions
 */
class PageRevision extends Model {
    

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->table = 'page_revisions';
    }
    
    /**
     * Create a new page revision
     * 
     * @param array $data Revision data
     * @return int|bool The ID of the new revision or false on failure
     */
    public function create($data) {
        return $this->insert($data);
    }
    
    /**
     * Get all revisions for a page
     * 
     * @param int $pageId The page ID
     * @param int $limit Maximum number of revisions to return
     * @param int $offset Offset for pagination
     * @return array List of revisions
     */
    public function getRevisionsByPageId($pageId, $limit = 10, $offset = 0) {
        $sql = "SELECT r.*, u.username, u.display_name 
                FROM {$this->table} r
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.page_id = :page_id
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->query($sql, [
            ':page_id' => $pageId,
            ':limit' => $limit,
            ':offset' => $offset
        ]);
        
        return $stmt->fetchAll($this->db::FETCH_ASSOC);
    }
    
    /**
     * Count all revisions for a page
     * 
     * @param int $pageId The page ID
     * @return int Number of revisions
     */
    public function countRevisionsByPageId($pageId) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE page_id = :page_id";
        
        $stmt = $this->query($sql, [':page_id' => $pageId]);
        
        return $stmt->fetchColumn();
    }
    
    /**
     * Get a specific revision
     * 
     * @param int $id Revision ID
     * @return array|bool Revision data or false if not found
     */
    public function getById($id) {
        $sql = "SELECT r.*, u.username, u.display_name 
                FROM {$this->table} r
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.id = :id";
        
        $stmt = $this->query($sql, [':id' => $id]);
        
        return $stmt->fetch($this->db::FETCH_ASSOC);
    }
    
    /**
     * Delete all revisions for a page
     * 
     * @param int $pageId The page ID
     * @return bool Success or failure
     */
    public function deleteAllForPage($pageId) {
        $sql = "DELETE FROM {$this->table} WHERE page_id = :page_id";
        
        $stmt = $this->query($sql, [':page_id' => $pageId]);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a specific revision
     * 
     * @param int $id Revision ID
     * @return bool Success or failure
     */
    public function deleteRevision($id) {
        return $this->delete($id);
    }
    
    /**
     * Get the latest revision for a page
     * 
     * @param int $pageId The page ID
     * @return array|bool Latest revision data or false if none found
     */
    public function getLatestRevision($pageId) {
        $sql = "SELECT r.*, u.username, u.display_name 
                FROM {$this->table} r
                LEFT JOIN users u ON r.created_by = u.id
                WHERE r.page_id = :page_id
                ORDER BY r.created_at DESC
                LIMIT 1";
        
        $stmt = $this->query($sql, [':page_id' => $pageId]);

        return $stmt->fetch($this->db::FETCH_ASSOC);
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns() {
        return ['id', 'page_id', 'created_by', 'created_at'];
    }
}

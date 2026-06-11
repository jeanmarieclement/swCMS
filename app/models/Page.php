<?php

namespace App\Models;

use App\Helpers\SessionHelper;
use App\Core\Model;

/**
 * Page Model
 * Handles database operations for pages
 */
class Page extends Model
{
    protected $pageRevisionModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->table = 'pages';
        $this->pageRevisionModel = new PageRevision();
    }

    /**
     * Get all pages with author information for admin listing
     *
     * @param string $status Filter by status (published, draft, trash or all)
     * @param int $limit Number of pages to retrieve
     * @param int $offset Offset for pagination
     * @return array
     */
    public function getAllForAdmin($status = 'all', $limit = 20, $offset = 0)
    {
        $sql = "SELECT p.*, u.username, u.display_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id";

        $params = [];

        if ($status !== 'all') {
            $sql .= " WHERE p.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->query($sql, $params);

        return $stmt->fetchAll();
    }

    /**
     * Count total pages (for pagination)
     *
     * @param string $status Filter by status
     * @return int
     */
    public function countAll($status = 'all')
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        if ($status !== 'all') {
            $sql .= " WHERE status = :status";
            $stmt = $this->query($sql, [':status' => $status]);
        } else {
            $stmt = $this->query($sql);
        }

        return (int)$stmt->fetchColumn();
    }

    /**
     * Get a page by slug
     *
     * @param string $slug The page slug
     * @return array|false The page or false if not found
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT p.*, u.username, u.display_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.slug = :slug";

        $stmt = $this->query($sql, [':slug' => $slug]);

        return $stmt->fetch();
    }

    /**
     * Get a page by ID
     *
     * @param int $id The page ID
     * @return array|false The page or false if not found
     */
    public function findById($id)
    {
        $sql = "SELECT p.*, u.username, u.display_name 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.id = :id";
        $stmt = $this->query($sql, [':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create a new page
     *
     * @param array $data Page data
     * @return int|false The new page ID or false on failure
     */
    public function create($data)
    {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Set published_at timestamp if status is published
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        $pageId = $this->insert($data);

        // Create initial revision if page was created successfully
        if ($pageId) {
            $revisionData = [
                'page_id' => $pageId,
                'title' => $data['title'],
                'content' => $data['content'] ?? '',
                'status' => $data['status'] ?? 'draft',
                'revision_note' => 'Initial version',
                'created_by' => $data['author_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->pageRevisionModel->create($revisionData);
        }

        return $pageId;
    }

    /**
     * Update a page
     *
     * @param int $id Page ID
     * @param array $data Page data
     * @param string $revisionNote Optional note about this revision
     * @return bool Success or failure
     */
    public function updatePage($id, $data, $revisionNote = '')
    {
        // Generate slug if not provided
        if (empty($data['slug']) && !empty($data['title'])) {
            $data['slug'] = $this->generateSlug($data['title'], $id);
        }

        // Set published_at timestamp if status is changed to published
        if (isset($data['status']) && $data['status'] === 'published') {
            $page = $this->getById($id);
            if ($page && $page['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }

        $success = $this->update($id, $data);

        // Create revision if page was updated successfully
        if ($success) {
            $revisionData = [
                'page_id' => $id,
                'title' => $data['title'],
                'content' => $data['content'] ?? '',
                'status' => $data['status'] ?? 'draft',
                'revision_note' => $revisionNote ?: 'Updated page',
                'created_by' => SessionHelper::getValue('user_id') ?? 1, // Default to admin if not set
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->pageRevisionModel->create($revisionData);
        }

        return $success;
    }

    /**
     * Generate a unique slug
     *
     * @param string $title The title to generate slug from
     * @param int $excludeId Page ID to exclude from uniqueness check
     * @return string The generated slug
     */
    /**
     * Change page status
     *
     * @param int $id Page ID
     * @param string $status New status (published, draft, trash)
     * @return bool Success or failure
     */
    public function changeStatus($id, $status)
    {
        $data = ['status' => $status];

        // Set published_at timestamp if status is changed to published
        if ($status === 'published') {
            $page = $this->getById($id);
            if ($page && $page['status'] !== 'published') {
                $data['published_at'] = date('Y-m-d H:i:s');
            }
        }

        $success = $this->update($id, $data);

        // Create revision for status change
        if ($success) {
            $page = $this->getById($id);
            $revisionData = [
                'page_id' => $id,
                'title' => $page['title'],
                'content' => $page['content'],
                'status' => $status,
                'revision_note' => 'Changed status to ' . $status,
                'created_by' => SessionHelper::getValue('user_id') ?? 1, // Default to admin if not set
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->pageRevisionModel->create($revisionData);
        }

        return $success;
    }

    /**
     * Get pages for a hierarchical dropdown
     *
     * @param int $excludeId Page ID to exclude (for edit page to prevent self-reference)
     * @return array
     */
    public function getPagesForDropdown($excludeId = null)
    {
        $sql = "SELECT id, title, parent_id FROM {$this->table}";

        if ($excludeId) {
            $sql .= " WHERE id != :exclude_id";
            $stmt = $this->query($sql, [':exclude_id' => $excludeId]);
        } else {
            $stmt = $this->query($sql);
        }

        return $stmt->fetchAll();
    }

    /**
     * Get child pages
     *
     * @param int $parentId Parent page ID
     * @return array
     */
    public function getChildPages($parentId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE parent_id = :parent_id ORDER BY `order` ASC";
        $stmt = $this->query($sql, [':parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Update page order
     *
     * @param int $id Page ID
     * @param int $order New order value
     * @return bool Success or failure
     */
    public function updateOrder($id, $order)
    {
        return $this->update($id, ['order' => $order]);
    }

    /**
     * Get all revisions for a page
     *
     * @param int $pageId The page ID
     * @param int $limit Maximum number of revisions to return
     * @param int $offset Offset for pagination
     * @return array List of revisions
     */
    public function getRevisions($pageId, $limit = 10, $offset = 0)
    {
        return $this->pageRevisionModel->getRevisionsByPageId($pageId, $limit, $offset);
    }

    /**
     * Count revisions for a page
     *
     * @param int $pageId The page ID
     * @return int Number of revisions
     */
    public function countRevisions($pageId)
    {
        return $this->pageRevisionModel->countRevisionsByPageId($pageId);
    }

    /**
     * Get a specific revision
     *
     * @param int $revisionId The revision ID
     * @return array|bool Revision data or false if not found
     */
    public function getRevision($revisionId)
    {
        return $this->pageRevisionModel->getById($revisionId);
    }

    /**
     * Restore a page to a previous revision
     *
     * @param int $pageId The page ID
     * @param int $revisionId The revision ID to restore
     * @return bool Success or failure
     */
    public function restoreRevision($pageId, $revisionId)
    {
        // Get the revision
        $revision = $this->pageRevisionModel->getById($revisionId);

        if (!$revision || $revision['page_id'] != $pageId) {
            return false;
        }

        // Update the page with revision data
        $data = [
            'title' => $revision['title'],
            'content' => $revision['content'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $success = $this->update($pageId, $data);

        // Create a new revision to record the restoration
        if ($success) {
            $revisionData = [
                'page_id' => $pageId,
                'title' => $revision['title'],
                'content' => $revision['content'],
                'status' => $this->getById($pageId)['status'],
                'revision_note' => 'Restored from revision #' . $revisionId,
                'created_by' => SessionHelper::getValue('user_id') ?? 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->pageRevisionModel->create($revisionData);
        }

        return $success;
    }

    /**
     * Delete a specific revision
     *
     * @param int $revisionId The revision ID
     * @return bool Success or failure
     */
    public function deleteRevision($revisionId)
    {
        return $this->pageRevisionModel->deleteRevision($revisionId);
    }

    /**
     * Delete all revisions for a page
     *
     * @param int $pageId The page ID
     * @return bool Success or failure
     */
    public function deleteAllRevisions($pageId)
    {
        return $this->pageRevisionModel->deleteAllForPage($pageId);
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'title', 'author_id', 'status', 'created_at', 'updated_at', 'published_at', 'order'];
    }
}

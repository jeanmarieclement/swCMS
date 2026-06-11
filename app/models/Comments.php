<?php

namespace App\Models;

use App\Core\Model;

class Comments extends Model
{
    protected $table = 'comments';

    public function __construct($params = [])
    {
        parent::__construct($params);
    }

    /**
     * Get all comments (with optional filter by status)
     */
    public function getAll($status = null)
    {
        $sql = "SELECT c.*, 
                       p.title as post_title,
                       u.username as user_username,
                       u.display_name as user_display_name,
                       u.email as user_email
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id";
        if ($status !== null) {
            $sql .= " WHERE c.status = :status";
        }
        $sql .= " ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll($this->db::FETCH_ASSOC);
    }

    /**
     * Get all comments in hierarchical format for admin
     *
     * @param string|null $status Filter by status
     * @return array Hierarchical list of comments with replies
     */
    public function getAllHierarchical($status = null)
    {
        $sql = "SELECT c.*, 
                       p.title as post_title,
                       u.username as user_username,
                       u.display_name as user_display_name,
                       u.email as user_email,
                       parent_c.content as parent_content,
                       parent_c.author_name as parent_author_name
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id
                LEFT JOIN users u ON c.user_id = u.id
                LEFT JOIN comments parent_c ON c.parent_id = parent_c.id";
        if ($status !== null) {
            $sql .= " WHERE c.status = :status";
        }
        $sql .= " ORDER BY c.parent_id IS NULL DESC, c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();

        $allComments = $stmt->fetchAll($this->db::FETCH_ASSOC);

        // For admin, show all comments in hierarchy but don't limit
        return $this->buildAdminCommentTree($allComments);
    }

    /**
     * Build admin comment tree (shows all levels)
     *
     * @param array $comments Flat array of comments
     * @param int|null $parentId Parent comment ID
     * @return array Tree structure of comments
     */
    private function buildAdminCommentTree($comments, $parentId = null)
    {
        $result = [];

        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parentId) {
                // Add nesting level for display purposes
                $comment['nesting_level'] = $parentId === null ? 0 : $this->getCommentNestingLevel($comment['id'], $comments);

                // Add replies to this comment
                $comment['replies'] = $this->buildAdminCommentTree($comments, $comment['id']);
                $comment['reply_count'] = $this->countRepliesRecursive($comment['replies']);

                $result[] = $comment;
            }
        }

        return $result;
    }

    /**
     * Get nesting level of a comment
     *
     * @param int $commentId Comment ID
     * @param array $allComments All comments array
     * @return int Nesting level
     */
    private function getCommentNestingLevel($commentId, $allComments)
    {
        foreach ($allComments as $comment) {
            if ($comment['id'] == $commentId) {
                if ($comment['parent_id'] === null) {
                    return 0;
                } else {
                    return 1 + $this->getCommentNestingLevel($comment['parent_id'], $allComments);
                }
            }
        }
        return 0;
    }

    /**
     * Count replies recursively
     *
     * @param array $replies Replies array
     * @return int Total count of replies
     */
    private function countRepliesRecursive($replies)
    {
        $count = count($replies);
        foreach ($replies as $reply) {
            $count += $this->countRepliesRecursive($reply['replies']);
        }
        return $count;
    }

    /**
     * Get approved comments for a post
     *
     * @param int $postId The post ID
     * @param int $limit Maximum number of comments to return
     * @param int $offset Offset for pagination
     * @return array List of comments
     */
    public function getApprovedForPost($postId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT c.*, u.display_name as user_display_name, u.username
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :post_id AND c.status = 'approved'
                ORDER BY c.created_at ASC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get approved comments for a post in hierarchical format
     *
     * @param int $postId The post ID
     * @param int $limit Maximum number of top-level comments
     * @param int $offset Offset for pagination
     * @return array Hierarchical list of comments with replies
     */
    public function getApprovedHierarchicalForPost($postId, $limit = 20, $offset = 0)
    {
        // Get all approved comments for the post
        $sql = "SELECT c.*, u.display_name as user_display_name, u.username
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.post_id = :post_id AND c.status = 'approved'
                ORDER BY c.parent_id IS NULL DESC, c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId, \PDO::PARAM_INT);
        $stmt->execute();

        $allComments = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Organize into hierarchical structure
        return $this->buildCommentTree($allComments, null, $limit, $offset);
    }

    /**
     * Build a tree structure from flat comments array
     *
     * @param array $comments Flat array of comments
     * @param int|null $parentId Parent comment ID (null for top-level)
     * @param int $limit Limit for top-level comments only
     * @param int $offset Offset for top-level comments only
     * @return array Tree structure of comments
     */
    private function buildCommentTree($comments, $parentId = null, $limit = null, $offset = 0)
    {
        $result = [];
        $topLevelCount = 0;
        $skipCount = 0;

        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parentId) {
                // Handle pagination only for top-level comments
                if ($parentId === null) {
                    if ($skipCount < $offset) {
                        $skipCount++;
                        continue;
                    }
                    if ($limit !== null && $topLevelCount >= $limit) {
                        break;
                    }
                    $topLevelCount++;
                }

                // Add replies to this comment
                $comment['replies'] = $this->buildCommentTree($comments, $comment['id']);
                $comment['reply_count'] = count($comment['replies']);

                $result[] = $comment;
            }
        }

        return $result;
    }

    /**
     * Get replies for a specific comment
     *
     * @param int $parentId Parent comment ID
     * @return array List of reply comments
     */
    public function getReplies($parentId)
    {
        $sql = "SELECT c.*, u.display_name as user_display_name, u.username
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.parent_id = :parent_id AND c.status = 'approved'
                ORDER BY c.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':parent_id', $parentId, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a comment by ID with user data for admin
     *
     * @param int $id The comment ID
     * @return array|false The comment with user data or false if not found
     */
    public function getByIdWithUserData($id)
    {
        $sql = "SELECT c.*, 
                       u.username as user_username,
                       u.display_name as user_display_name,
                       u.email as user_email
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create a reply to a comment
     *
     * @param array $data Reply data including parent_id
     * @return int|false The new reply ID or false on failure
     */
    public function createReply($data)
    {
        // Ensure parent_id is set
        if (!isset($data['parent_id']) || empty($data['parent_id'])) {
            return false;
        }

        // Verify parent comment exists and is approved
        $parentComment = $this->getById($data['parent_id']);
        if (!$parentComment || $parentComment['status'] !== 'approved') {
            return false;
        }

        // Use the same post_id as parent comment
        $data['post_id'] = $parentComment['post_id'];

        return $this->createComment($data);
    }

    /**
     * Count approved comments for a post
     *
     * @param int $postId The post ID
     * @return int Number of approved comments
     */
    public function countApprovedForPost($postId)
    {
        $sql = "SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND status = 'approved'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId, \PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    /**
     * Create a new comment
     *
     * @param array $data Comment data
     * @return int|false The new comment ID or false on failure
     */
    public function createComment($data)
    {
        // Sanitize content - remove HTML tags
        $data['content'] = strip_tags($data['content']);

        // Set default status
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }

        // Set IP address if not provided
        if (!isset($data['author_ip'])) {
            $data['author_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        }

        return $this->insert($data);
    }

    /**
     * Update comment status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE comments SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    /**
     * Check if comments are enabled for a post
     *
     * @param int $postId The post ID
     * @return bool True if comments are enabled
     */
    public function areCommentsEnabledForPost($postId)
    {
        // Check global setting
        $globalSetting = \App\Helpers\SystemSettingsHelper::get('COMMENTS_ENABLED');
        if ($globalSetting !== '1') {
            return false;
        }

        // Check post-specific setting
        $sql = "SELECT comments_enabled FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $postId, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchColumn();
        return $result === 1 || $result === '1';
    }

    /**
     * Check if comments are enabled for a page
     *
     * @param int $pageId The page ID
     * @return bool True if comments are enabled
     */
    public function areCommentsEnabledForPage($pageId)
    {
        // Check global setting
        $globalSetting = \App\Helpers\SystemSettingsHelper::get('COMMENTS_ENABLED');
        if ($globalSetting !== '1') {
            return false;
        }

        // Check page-specific setting
        $sql = "SELECT comments_enabled FROM pages WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pageId, \PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetchColumn();
        return $result === 1 || $result === '1';
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'post_id', 'author_name', 'author_email', 'status', 'parent_id', 'user_id', 'created_at'];
    }
}

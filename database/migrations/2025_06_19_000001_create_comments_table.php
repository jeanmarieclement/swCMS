<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

/**
 * Migration for creating comments table
 * @property \PDO $db
 */
class CreateCommentsTable extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Create the comments table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS comments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    post_id INTEGER NOT NULL,
                    author_name VARCHAR(50) DEFAULT NULL,
                    author_email VARCHAR(100) DEFAULT NULL,
                    author_url VARCHAR(100) DEFAULT NULL,
                    author_ip VARCHAR(100) DEFAULT NULL,
                    content TEXT NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'pending',
                    parent_id INTEGER DEFAULT NULL,
                    user_id INTEGER DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_comments_post_id ON comments(post_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_comments_parent_id ON comments(parent_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_comments_user_id ON comments(user_id);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS comments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    post_id INT NOT NULL,
                    author_name VARCHAR(50) DEFAULT NULL,
                    author_email VARCHAR(100) DEFAULT NULL,
                    author_url VARCHAR(100) DEFAULT NULL,
                    author_ip VARCHAR(100) DEFAULT NULL,
                    content TEXT NOT NULL,
                    status ENUM('approved','pending','spam','trash') NOT NULL DEFAULT 'pending',
                    parent_id INT DEFAULT NULL,
                    user_id INT DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY post_id (post_id),
                    KEY parent_id (parent_id),
                    KEY user_id (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
            }
            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Drop the comments table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS comments;");
            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

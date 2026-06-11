<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating posts table
 * @property \PDO $db
 */
class CreatePostsTable extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    /**
     * Create the posts table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS posts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL,
                    content TEXT,
                    excerpt TEXT,
                    status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    comment_status VARCHAR(10) NOT NULL DEFAULT 'open',
                    author_id INTEGER NOT NULL,
                    featured_image VARCHAR(255) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT NULL,
                    published_at TIMESTAMP DEFAULT NULL
);");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_posts_author_id ON posts(author_id);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS posts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL,
                    content TEXT,
                    excerpt TEXT,
                    status ENUM('published','draft','trash') NOT NULL DEFAULT 'draft',
                    comment_status ENUM('open','closed') NOT NULL DEFAULT 'open',
                    author_id INT NOT NULL,
                    featured_image VARCHAR(255) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    published_at TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY slug (slug)
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
     * Drop the posts table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS posts;");
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

<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class CreatePagesTable extends Migration
{
    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS pages (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL,
                    content TEXT,
                    status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    author_id INTEGER NOT NULL,
                    parent_id INTEGER DEFAULT NULL,
                    template VARCHAR(100) NOT NULL DEFAULT 'default',
                    \"order\" INTEGER NOT NULL DEFAULT 0,
                    meta_title VARCHAR(255) DEFAULT NULL,
                    meta_description TEXT DEFAULT NULL,
                    comments_enabled INTEGER NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT NULL,
                    published_at TIMESTAMP DEFAULT NULL
                )");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_pages_slug ON pages(slug)");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pages_author_id ON pages(author_id)");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pages_parent_id ON pages(parent_id)");

                $this->db->exec("CREATE TABLE IF NOT EXISTS page_revisions (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    page_id INTEGER NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    content TEXT,
                    status VARCHAR(20) NOT NULL DEFAULT 'draft',
                    revision_note TEXT DEFAULT NULL,
                    created_by INTEGER NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_page_revisions_page_id ON page_revisions(page_id)");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS pages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255) NOT NULL,
                    slug VARCHAR(255) NOT NULL,
                    content TEXT,
                    status ENUM('published','draft','trash') NOT NULL DEFAULT 'draft',
                    author_id INT NOT NULL,
                    parent_id INT DEFAULT NULL,
                    template VARCHAR(100) NOT NULL DEFAULT 'default',
                    `order` INT NOT NULL DEFAULT 0,
                    meta_title VARCHAR(255) DEFAULT NULL,
                    meta_description TEXT DEFAULT NULL,
                    comments_enabled TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    published_at TIMESTAMP NULL DEFAULT NULL,
                    UNIQUE KEY slug (slug)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

                $this->db->exec("CREATE TABLE IF NOT EXISTS page_revisions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    page_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    content TEXT,
                    status ENUM('published','draft','trash') NOT NULL DEFAULT 'draft',
                    revision_note TEXT DEFAULT NULL,
                    created_by INT NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
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

    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS page_revisions");
            $this->db->exec("DROP TABLE IF EXISTS pages");
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

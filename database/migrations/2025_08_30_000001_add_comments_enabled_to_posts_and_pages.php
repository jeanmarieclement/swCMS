<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

/**
 * Migration for adding comments_enabled column to posts and pages tables
 */
class AddCommentsEnabledToPostsAndPages extends Migration
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
     * Add comments_enabled column to posts and pages tables
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            
            // Add comments_enabled column to posts table
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("ALTER TABLE posts ADD COLUMN comments_enabled INTEGER DEFAULT 1");
                $this->db->exec("ALTER TABLE pages ADD COLUMN comments_enabled INTEGER DEFAULT 1");
            } else {
                $this->db->exec("ALTER TABLE posts ADD COLUMN comments_enabled TINYINT(1) DEFAULT 1");
                $this->db->exec("ALTER TABLE pages ADD COLUMN comments_enabled TINYINT(1) DEFAULT 1");
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
     * Remove comments_enabled column from posts and pages tables
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            
            // For SQLite, we need to recreate the tables without the column
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                // Get current posts structure
                $postsResult = $this->db->query("PRAGMA table_info(posts)");
                $postsColumns = $postsResult->fetchAll(\PDO::FETCH_ASSOC);
                
                $postsColumnsList = [];
                foreach ($postsColumns as $column) {
                    if ($column['name'] !== 'comments_enabled') {
                        $postsColumnsList[] = $column['name'];
                    }
                }
                $postsColumnsStr = implode(', ', $postsColumnsList);
                
                // Recreate posts table
                $this->db->exec("BEGIN TRANSACTION");
                $this->db->exec("CREATE TABLE posts_backup AS SELECT $postsColumnsStr FROM posts");
                $this->db->exec("DROP TABLE posts");
                $this->db->exec("ALTER TABLE posts_backup RENAME TO posts");
                $this->db->exec("COMMIT");
                
                // Get current pages structure
                $pagesResult = $this->db->query("PRAGMA table_info(pages)");
                $pagesColumns = $pagesResult->fetchAll(\PDO::FETCH_ASSOC);
                
                $pagesColumnsList = [];
                foreach ($pagesColumns as $column) {
                    if ($column['name'] !== 'comments_enabled') {
                        $pagesColumnsList[] = $column['name'];
                    }
                }
                $pagesColumnsStr = implode(', ', $pagesColumnsList);
                
                // Recreate pages table
                $this->db->exec("BEGIN TRANSACTION");
                $this->db->exec("CREATE TABLE pages_backup AS SELECT $pagesColumnsStr FROM pages");
                $this->db->exec("DROP TABLE pages");
                $this->db->exec("ALTER TABLE pages_backup RENAME TO pages");
                $this->db->exec("COMMIT");
            } else {
                // MySQL can drop columns directly
                $this->db->exec("ALTER TABLE posts DROP COLUMN comments_enabled");
                $this->db->exec("ALTER TABLE pages DROP COLUMN comments_enabled");
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
}
<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating post_tags table
 * @property \PDO $db
 */
class CreatePostTagsTable extends Migration
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
     * Create the post_tags table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS post_tags (
                    post_id INTEGER NOT NULL,
                    tag_id INTEGER NOT NULL,
                    PRIMARY KEY (post_id, tag_id)
                );");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS post_tags (
                    post_id INT NOT NULL,
                    tag_id INT NOT NULL,
                    PRIMARY KEY (post_id, tag_id)
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
     * Drop the post_tags table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS post_tags;");
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

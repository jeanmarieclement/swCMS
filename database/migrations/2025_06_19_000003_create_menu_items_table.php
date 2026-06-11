<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating menu_items table
 * @property \PDO $db
 */
class CreateMenuItemsTable extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    /**
     * Create the menu_items table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS menu_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    block_id INTEGER NOT NULL,
                    parent_id INTEGER DEFAULT NULL,
                    label VARCHAR(100) NOT NULL,
                    url VARCHAR(255) NOT NULL,
                    icon VARCHAR(100) DEFAULT NULL,
                    permission_key VARCHAR(100) DEFAULT NULL,
                    position INTEGER NOT NULL DEFAULT 0,
                    plugin VARCHAR(100) DEFAULT NULL,
                    active INTEGER NOT NULL DEFAULT 1
                );");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_menu_items_block_id ON menu_items(block_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_menu_items_parent_id ON menu_items(parent_id);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS menu_items (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    block_id INT NOT NULL,
                    parent_id INT DEFAULT NULL,
                    label VARCHAR(100) NOT NULL,
                    url VARCHAR(255) NOT NULL,
                    icon VARCHAR(100) DEFAULT NULL,
                    permission_key VARCHAR(100) DEFAULT NULL,
                    position INT NOT NULL DEFAULT 0,
                    plugin VARCHAR(100) DEFAULT NULL,
                    active TINYINT(1) NOT NULL DEFAULT 1,
                    KEY block_id (block_id),
                    KEY parent_id (parent_id)
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
     * Drop the menu_items table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS menu_items;");
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

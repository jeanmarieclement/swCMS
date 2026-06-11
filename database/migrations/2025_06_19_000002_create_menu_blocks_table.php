<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating menu_blocks table
 * @property \PDO $db
 */
class CreateMenuBlocksTable extends Migration
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
     * Create the menu_blocks table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS menu_blocks (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(100) NOT NULL,
                    `key` VARCHAR(100) NOT NULL,
                    position INTEGER NOT NULL DEFAULT 0,
                    active INTEGER NOT NULL DEFAULT 1
                );");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_menu_blocks_key ON menu_blocks(`key`);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS menu_blocks (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    `key` VARCHAR(100) NOT NULL,
                    position INT NOT NULL DEFAULT 0,
                    active TINYINT(1) NOT NULL DEFAULT 1,
                    KEY `key` (`key`)
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
     * Drop the menu_blocks table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS menu_blocks;");
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

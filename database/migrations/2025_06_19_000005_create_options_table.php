<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating options table
 * @property \PDO $db
 */
class CreateOptionsTable extends Migration
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
     * Create the options table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS options (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    option_name VARCHAR(191) NOT NULL,
                    option_value TEXT NOT NULL,
                    autoload VARCHAR(3) NOT NULL DEFAULT 'yes'
                );");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_options_option_name ON options(option_name);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS options (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    option_name VARCHAR(191) NOT NULL,
                    option_value LONGTEXT NOT NULL,
                    autoload ENUM('yes','no') NOT NULL DEFAULT 'yes',
                    UNIQUE KEY option_name (option_name)
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
     * Drop the options table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS options;");
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

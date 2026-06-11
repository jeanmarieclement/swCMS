<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating user_login_attempts table
 * This table tracks failed login attempts for security purposes
 * @property \PDO $db
 */
class CreateUserLoginAttemptsTable extends Migration
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
     * Create the user_login_attempts table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS user_login_attempts (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email VARCHAR(100) NOT NULL,
                    failed_attempts INTEGER NOT NULL DEFAULT 0,
                    last_attempt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT NULL
                );");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_login_attempts_email ON user_login_attempts(email);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_login_attempts_last_attempt ON user_login_attempts(last_attempt);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS user_login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(100) NOT NULL,
                    failed_attempts INT NOT NULL DEFAULT 0,
                    last_attempt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY email (email),
                    KEY idx_last_attempt (last_attempt)
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
     * Drop the user_login_attempts table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS user_login_attempts;");
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
<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for creating password_resets table
 * Reset tokens have to outlive the session that requested them, so they are
 * persisted here instead of in the requesting browser's session.
 * @property \PDO $db
 */
class CreatePasswordResetsTable extends Migration
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
     * Create the password_resets table compatible with MySQL and SQLite
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $this->db->exec("CREATE TABLE IF NOT EXISTS password_resets (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    token_hash VARCHAR(255) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    used_at TIMESTAMP DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_password_resets_user ON password_resets(user_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_password_resets_expires ON password_resets(expires_at);");
            } else {
                $this->db->exec("CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token_hash VARCHAR(255) NOT NULL,
                    expires_at TIMESTAMP NOT NULL,
                    used_at TIMESTAMP NULL DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    KEY idx_password_resets_user (user_id),
                    KEY idx_password_resets_expires (expires_at)
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
     * Drop the password_resets table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS password_resets;");
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

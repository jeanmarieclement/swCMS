<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration for adding last_login column to users table
 * This column tracks when users last successfully logged in
 * @property \PDO $db
 */
class AddLastLoginToUsersTable extends Migration
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
     * Add last_login column to users table
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();
            
            // Check if column already exists
            $columnExists = false;
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                $stmt = $this->db->query("PRAGMA table_info(users)");
                $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($columns as $column) {
                    if ($column['name'] === 'last_login') {
                        $columnExists = true;
                        break;
                    }
                }
                
                if (!$columnExists) {
                    $this->db->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP DEFAULT NULL;");
                }
            } else {
                // MySQL
                $stmt = $this->db->query("SHOW COLUMNS FROM users LIKE 'last_login'");
                $columnExists = $stmt->rowCount() > 0;
                
                if (!$columnExists) {
                    $this->db->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;");
                }
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
     * Remove last_login column from users table
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                // SQLite doesn't support DROP COLUMN directly, so we need to recreate the table
                $this->db->exec("CREATE TABLE users_backup AS SELECT 
                    id, username, password, email, display_name, role, status, created_at, updated_at 
                    FROM users;");
                $this->db->exec("DROP TABLE users;");
                $this->db->exec("CREATE TABLE users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    username VARCHAR(50) NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    display_name VARCHAR(100) DEFAULT NULL,
                    role VARCHAR(50) NOT NULL DEFAULT 'subscriber',
                    status VARCHAR(10) NOT NULL DEFAULT 'active',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT NULL
                );");
                $this->db->exec("INSERT INTO users SELECT * FROM users_backup;");
                $this->db->exec("DROP TABLE users_backup;");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_username ON users(username);");
                $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(email);");
            } else {
                // MySQL
                $this->db->exec("ALTER TABLE users DROP COLUMN last_login;");
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
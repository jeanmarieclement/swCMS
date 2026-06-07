<?php
require_once __DIR__ . '/../../App/Core/Database/Migration.php';

use App\Core\Database\Migration;

/**
 * Migration for creating settings table
 * @property \PDO $db
 */
class CreateSettingsTable extends Migration
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
     * Crea la tabella settings per le impostazioni globali del sito
     */
    public function up()
    {
        try {
            $this->db->beginTransaction();

            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                // SQLite compatible CREATE TABLE
                $this->execute("CREATE TABLE IF NOT EXISTS settings (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    `key` VARCHAR(100) NOT NULL UNIQUE,
                    `value` TEXT NULL,
                    `description` VARCHAR(255) NULL,
                    `autoload` INTEGER DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );");
                // Optionally, add triggers for updated_at if you want auto-update on row change
            } else {
                // MySQL compatible CREATE TABLE
                $this->db->exec("CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    `key` VARCHAR(100) NOT NULL UNIQUE,
                    `value` TEXT NULL,
                    `description` VARCHAR(255) NULL,
                    `autoload` TINYINT(1) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            }

            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Reverts the migration by dropping the settings table.
     */
    public function down()
    {
        try {
            $this->db->beginTransaction();
            $this->db->exec("DROP TABLE IF EXISTS settings;");

            if ($this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}

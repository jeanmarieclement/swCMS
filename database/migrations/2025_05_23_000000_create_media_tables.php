<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';
use App\Core\Database\Migration;
/**
 * Migration for creating media tables
 * @property \PDO $db
 */
class CreateMediaTables extends Migration
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * Esegue la migrazione
     */
    public function up()
    {
        try {
            
            $this->db->beginTransaction();

            // Create media and media_relationships tables with compatibility for both MySQL and SQLite
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                // SQLite compatible CREATE TABLE for media
                $this->db->exec("CREATE TABLE IF NOT EXISTS media (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT DEFAULT NULL,
                    alt_text VARCHAR(255) DEFAULT NULL,
                    filename VARCHAR(255) NOT NULL,
                    filepath VARCHAR(255) NOT NULL,
                    filetype VARCHAR(100) NOT NULL,
                    filesize INTEGER NOT NULL,
                    width INTEGER DEFAULT NULL,
                    height INTEGER DEFAULT NULL,
                    metadata TEXT DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );");
                // SQLite compatible CREATE TABLE for media_relationships
                $this->db->exec("CREATE TABLE IF NOT EXISTS media_relationships (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    media_id INTEGER NOT NULL,
                    related_id INTEGER NOT NULL,
                    related_type VARCHAR(50) NOT NULL,
                    field_name VARCHAR(100) DEFAULT NULL,
                    `order` INTEGER NOT NULL DEFAULT 0,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );");
                // Optionally, create indexes separately if needed
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_media_user_id ON media(user_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_media_filetype ON media(filetype);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_media_relationships_media_id ON media_relationships(media_id);");
                $this->db->exec("CREATE INDEX IF NOT EXISTS idx_media_relationships_related ON media_relationships(related_id, related_type);");
            } else {
                // MySQL compatible CREATE TABLE for media
                $this->db->exec("CREATE TABLE IF NOT EXISTS `media` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `user_id` int(11) NOT NULL,
                    `title` varchar(255) NOT NULL,
                    `description` text DEFAULT NULL,
                    `alt_text` varchar(255) DEFAULT NULL,
                    `filename` varchar(255) NOT NULL,
                    `filepath` varchar(255) NOT NULL,
                    `filetype` varchar(100) NOT NULL,
                    `filesize` int(11) NOT NULL,
                    `width` int(11) DEFAULT NULL,
                    `height` int(11) DEFAULT NULL,
                    `metadata` text DEFAULT NULL,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`),
                    KEY `filetype` (`filetype`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
                // MySQL compatible CREATE TABLE for media_relationships
                $this->db->exec("CREATE TABLE IF NOT EXISTS `media_relationships` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `media_id` int(11) NOT NULL,
                    `related_id` int(11) NOT NULL,
                    `related_type` varchar(50) NOT NULL,
                    `field_name` varchar(100) DEFAULT NULL,
                    `order` int(11) NOT NULL DEFAULT 0,
                    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `media_id` (`media_id`),
                    KEY `related` (`related_id`, `related_type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
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
     * Annulla la migrazione
     */
    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS `media_relationships`;");
        $this->db->exec("DROP TABLE IF EXISTS `media`;");
    }
}

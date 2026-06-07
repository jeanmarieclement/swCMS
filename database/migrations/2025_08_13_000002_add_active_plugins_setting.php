<?php
require_once __DIR__ . '/../../App/Core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration to add ACTIVE_PLUGINS setting
 */
class AddActivePluginsSetting extends Migration
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function up() {
        try {
            $this->db->beginTransaction();
            
            // Insert ACTIVE_PLUGINS setting if it doesn't exist
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO settings (`key`, `value`, description, autoload, created_at, updated_at) 
                VALUES ('ACTIVE_PLUGINS', '[]', 'JSON array of active plugin names', 1, NOW(), NOW())
            ");
            $stmt->execute();
            
            $this->db->commit();
            echo "Added ACTIVE_PLUGINS setting to database\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function down() {
        try {
            $this->db->beginTransaction();
            
            // Remove ACTIVE_PLUGINS setting
            $stmt = $this->db->prepare("DELETE FROM settings WHERE key = 'ACTIVE_PLUGINS'");
            $stmt->execute();
            
            $this->db->commit();
            echo "Removed ACTIVE_PLUGINS setting from database\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
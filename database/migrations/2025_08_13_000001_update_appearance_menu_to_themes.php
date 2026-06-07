<?php
require_once __DIR__ . '/../../App/Core/Database/Migration.php';

use App\Core\Database\Database;
use App\Core\Database\Migration;

/**
 * Migration to update Appearance menu item to Themes
 */
class UpdateAppearanceMenuToThemes extends Migration
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
            
            // Update the existing Appearance menu item to point to themes
            $stmt = $this->db->prepare("
                UPDATE menu_items 
                SET label = 'Themes', 
                    url = '/admin/themes',
                    permission_key = 'themes'
                WHERE label = 'Appearance' AND url = '/admin/appearance'
            ");
            $stmt->execute();
            
            $this->db->commit();
            echo "Updated Appearance menu item to point to Themes management\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function down() {
        try {
            $this->db->beginTransaction();
            
            // Revert the changes
            $stmt = $this->db->prepare("
                UPDATE menu_items 
                SET label = 'Appearance', 
                    url = '/admin/appearance',
                    permission_key = 'appearance'
                WHERE label = 'Themes' AND url = '/admin/themes'
            ");
            $stmt->execute();
            
            $this->db->commit();
            echo "Reverted Themes menu item back to Appearance\n";
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
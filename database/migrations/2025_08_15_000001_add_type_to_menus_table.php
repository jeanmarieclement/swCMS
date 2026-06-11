<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class AddTypeToMenusTable extends Migration {
    
    public function up() {
        $pdo = $this->db;

        // SQLite does not support AFTER in ADD COLUMN; column is appended
        if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
            $pdo->exec("ALTER TABLE menus ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'custom'");
            $pdo->exec("ALTER TABLE menus ADD COLUMN content_id INTEGER NULL");
        } else {
            $pdo->exec("ALTER TABLE menus ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'custom' AFTER url");
            $pdo->exec("ALTER TABLE menus ADD COLUMN content_id INT NULL AFTER type");
        }
        
        // Aggiorna i menu esistenti come tipo 'custom'
        $sql = "UPDATE menus SET type = 'custom' WHERE type = ''";
        $pdo->exec($sql);
        
        echo "Campo 'type' e 'content_id' aggiunti alla tabella menus.\n";
    }
    
    public function down() {
        $pdo = $this->db;
        
        // Rimuovi i campi aggiunti
        $pdo->exec("ALTER TABLE menus DROP COLUMN content_id");
        $pdo->exec("ALTER TABLE menus DROP COLUMN type");
        
        echo "Campi 'type' e 'content_id' rimossi dalla tabella menus.\n";
    }
}
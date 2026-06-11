<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class AddTypeToMenusTable extends Migration {
    
    public function up() {
        $pdo = $this->db;
        
        // Aggiungi il campo type alla tabella menus
        $sql = "ALTER TABLE menus ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'custom' AFTER url";
        $pdo->exec($sql);
        
        // Aggiungi anche un campo content_id per riferimenti a post/pagine
        $sql = "ALTER TABLE menus ADD COLUMN content_id INT NULL AFTER type";
        $pdo->exec($sql);
        
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
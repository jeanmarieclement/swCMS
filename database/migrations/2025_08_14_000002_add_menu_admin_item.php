<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class AddMenuAdminItem extends Migration {
    
    public function up() {
        $pdo = $this->db;
        // Trova il block_id per "Content"
        $stmt = $pdo->prepare("SELECT id FROM menu_blocks WHERE `key` = 'content' LIMIT 1");
        $stmt->execute();
        $contentBlock = $stmt->fetch();
        
        if (!$contentBlock) {
            echo "Block 'content' non trovato, creazione del block...\n";
            
            // Crea il block Content se non esiste
            $stmt = $pdo->prepare("INSERT INTO menu_blocks (name, `key`, position, active) VALUES ('Content', 'content', 2, 1)");
            $stmt->execute();
            $contentBlockId = $pdo->lastInsertId();
        } else {
            $contentBlockId = $contentBlock['id'];
        }
        
        // Controlla se il menu item esiste già
        $stmt = $pdo->prepare("SELECT id FROM menu_items WHERE url = '/admin/menus' LIMIT 1");
        $stmt->execute();
        $existingItem = $stmt->fetch();
        
        if (!$existingItem) {
            // Trova la posizione massima nel block Content
            $stmt = $pdo->prepare("SELECT MAX(position) as max_pos FROM menu_items WHERE block_id = :block_id AND parent_id IS NULL");
            $stmt->bindValue(':block_id', $contentBlockId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $nextPosition = ($result['max_pos'] ?? 0) + 1;
            
            // Inserisci il menu item per la gestione menu
            $stmt = $pdo->prepare("INSERT INTO menu_items (block_id, parent_id, label, url, icon, permission_key, position, plugin, active) VALUES (:block_id, NULL, 'Menu', '/admin/menus', 'fas fa-bars', 'admin.menus', :position, NULL, 1)");
            $stmt->bindValue(':block_id', $contentBlockId, PDO::PARAM_INT);
            $stmt->bindValue(':position', $nextPosition, PDO::PARAM_INT);
            $stmt->execute();
            
            echo "Menu item 'Menu' aggiunto con successo al pannello admin.\n";
        } else {
            echo "Menu item 'Menu' già esistente.\n";
        }
    }
    
    public function down() {
        $pdo = $this->db;
        // Rimuovi il menu item
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE url = '/admin/menus'");
        $stmt->execute();
        
        echo "Menu item 'Menu' rimosso dal pannello admin.\n";
    }
}
<?php

namespace App\Services;

use App\Core\Database\Database;
use App\Helpers\LogHelper;

/**
 * Plugin Menu Manager
 * Gestisce automaticamente i menu dei plugin durante attivazione/disattivazione
 */
class PluginMenuManager {
    
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Registra menu per un plugin durante l'attivazione
     * @param string $pluginName Nome del plugin
     * @param array $menuConfig Configurazione menu del plugin
     * @return bool Success status
     */
    public function registerPluginMenus(string $pluginName, array $menuConfig): bool {
        try {
            $this->db->beginTransaction();
            
            // Registra blocchi menu se specificati
            if (isset($menuConfig['blocks'])) {
                foreach ($menuConfig['blocks'] as $blockConfig) {
                    $this->registerMenuBlock($pluginName, $blockConfig);
                }
            }
            
            // Registra elementi menu
            if (isset($menuConfig['items'])) {
                foreach ($menuConfig['items'] as $itemConfig) {
                    $this->registerMenuItem($pluginName, $itemConfig);
                }
            }
            
            $this->db->commit();
            LogHelper::info('Plugin menus registered successfully', ['plugin' => $pluginName]);
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            LogHelper::error('Failed to register plugin menus', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Rimuove tutti i menu di un plugin durante la disattivazione
     * @param string $pluginName Nome del plugin
     * @return bool Success status
     */
    public function unregisterPluginMenus(string $pluginName): bool {
        try {
            $this->db->beginTransaction();
            
            // Rimuovi tutti gli elementi menu del plugin
            $stmt = $this->db->prepare("DELETE FROM menu_items WHERE plugin = :plugin");
            $stmt->bindParam(':plugin', $pluginName);
            $stmt->execute();
            
            // Rimuovi blocchi menu creati dal plugin (solo se vuoti)
            $this->cleanupEmptyPluginBlocks($pluginName);
            
            $this->db->commit();
            LogHelper::info('Plugin menus unregistered successfully', ['plugin' => $pluginName]);
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            LogHelper::error('Failed to unregister plugin menus', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Registra un blocco menu per un plugin
     * @param string $pluginName Nome del plugin
     * @param array $blockConfig Configurazione blocco
     * @return int|false ID del blocco o false
     */
    private function registerMenuBlock(string $pluginName, array $blockConfig) {
        // Controlla se il blocco esiste già
        $stmt = $this->db->prepare("SELECT id FROM menu_blocks WHERE `key` = :key");
        $stmt->bindParam(':key', $blockConfig['key']);
        $stmt->execute();
        $existingBlock = $stmt->fetch();
        
        if ($existingBlock) {
            return $existingBlock['id'];
        }
        
        // Crea nuovo blocco
        $stmt = $this->db->prepare("
            INSERT INTO menu_blocks (name, `key`, position, active, plugin) 
            VALUES (:name, :key, :position, :active, :plugin)
        ");
        
        // Prepara variabili per bindParam
        $name = $blockConfig['name'];
        $key = $blockConfig['key'];
        $position = $blockConfig['position'] ?? 100;
        $active = $blockConfig['active'] ?? 1;
        
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':key', $key);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':active', $active);
        $stmt->bindParam(':plugin', $pluginName);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Registra un elemento menu per un plugin
     * @param string $pluginName Nome del plugin
     * @param array $itemConfig Configurazione elemento
     * @return int|false ID dell'elemento o false
     */
    private function registerMenuItem(string $pluginName, array $itemConfig) {
        // Risolvi l'ID del blocco se specificato per key
        $blockId = $itemConfig['block_id'] ?? null;
        if (!$blockId && isset($itemConfig['block_key'])) {
            $blockId = $this->getBlockIdByKey($itemConfig['block_key']);
            
            // Se il blocco non esiste, crealo automaticamente
            if (!$blockId) {
                $blockId = $this->createDefaultBlock($itemConfig['block_key'], $pluginName);
            }
        }
        
        // Controlla se l'elemento esiste già per questo plugin
        $stmt = $this->db->prepare("
            SELECT id FROM menu_items 
            WHERE url = :url AND plugin = :plugin
        ");
        $stmt->bindParam(':url', $itemConfig['url']);
        $stmt->bindParam(':plugin', $pluginName);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            LogHelper::warning('Menu item already exists', [
                'plugin' => $pluginName,
                'url' => $itemConfig['url']
            ]);
            return false;
        }
        
        // Crea nuovo elemento menu
        $stmt = $this->db->prepare("
            INSERT INTO menu_items 
            (block_id, parent_id, label, url, icon, permission_key, position, plugin, active) 
            VALUES (:block_id, :parent_id, :label, :url, :icon, :permission_key, :position, :plugin, :active)
        ");
        
        // Prepara variabili per bindParam
        $parentId = $itemConfig['parent_id'] ?? null;
        $label = $itemConfig['label'];
        $url = $itemConfig['url'];
        $icon = $itemConfig['icon'] ?? 'fas fa-puzzle-piece';
        $permissionKey = $itemConfig['permission_key'] ?? null;
        $position = $itemConfig['position'] ?? 100;
        $active = $itemConfig['active'] ?? 1;
        
        $stmt->bindParam(':block_id', $blockId);
        $stmt->bindParam(':parent_id', $parentId);
        $stmt->bindParam(':label', $label);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':icon', $icon);
        $stmt->bindParam(':permission_key', $permissionKey);
        $stmt->bindParam(':position', $position);
        $stmt->bindParam(':plugin', $pluginName);
        $stmt->bindParam(':active', $active);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Ottiene l'ID di un blocco dalla sua chiave
     * @param string $key Chiave del blocco
     * @return int|false ID del blocco o false
     */
    private function getBlockIdByKey(string $key) {
        $stmt = $this->db->prepare("SELECT id FROM menu_blocks WHERE `key` = :key");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ? $result['id'] : false;
    }
    
    /**
     * Crea un blocco di default per un plugin
     * @param string $blockKey Chiave del blocco
     * @param string $pluginName Nome del plugin
     * @return int|false ID del blocco creato o false
     */
    private function createDefaultBlock(string $blockKey, string $pluginName) {
        // Mapping di blocchi comuni
        $defaultBlocks = [
            'tools' => ['name' => 'Tools', 'position' => 40],
            'content' => ['name' => 'Content', 'position' => 20],
            'settings' => ['name' => 'Settings', 'position' => 50],
            'system' => ['name' => 'System', 'position' => 60],
            'plugins' => ['name' => 'Plugins', 'position' => 70]
        ];
        
        $blockConfig = $defaultBlocks[$blockKey] ?? [
            'name' => ucfirst($blockKey),
            'position' => 100
        ];
        
        $blockConfig['key'] = $blockKey;
        $blockConfig['active'] = 1;
        
        return $this->registerMenuBlock($pluginName, $blockConfig);
    }
    
    /**
     * Rimuove blocchi vuoti creati da un plugin
     * @param string $pluginName Nome del plugin
     */
    private function cleanupEmptyPluginBlocks(string $pluginName): void {
        // Trova blocchi creati dal plugin che ora sono vuoti
        $stmt = $this->db->prepare("
            SELECT mb.id, mb.key 
            FROM menu_blocks mb 
            LEFT JOIN menu_items mi ON mb.id = mi.block_id 
            WHERE mb.plugin = :plugin 
            GROUP BY mb.id 
            HAVING COUNT(mi.id) = 0
        ");
        $stmt->bindParam(':plugin', $pluginName);
        $stmt->execute();
        $emptyBlocks = $stmt->fetchAll();
        
        foreach ($emptyBlocks as $block) {
            // Rimuovi solo se non è un blocco standard
            if (!in_array($block['key'], ['dashboard', 'content', 'users', 'settings'])) {
                $deleteStmt = $this->db->prepare("DELETE FROM menu_blocks WHERE id = :id");
                $deleteStmt->bindParam(':id', $block['id']);
                $deleteStmt->execute();
                
                LogHelper::info('Removed empty plugin block', [
                    'plugin' => $pluginName,
                    'block_key' => $block['key']
                ]);
            }
        }
    }
    
    /**
     * Ottiene la configurazione menu di un plugin dal suo file
     * @param string $pluginName Nome del plugin
     * @param string $pluginPath Percorso del plugin
     * @return array|null Configurazione menu o null
     */
    public function getPluginMenuConfig(string $pluginName, string $pluginPath): ?array {
        // Cerca configurazione nel file principale del plugin
        $mainFile = $pluginPath . '/' . $pluginName . '.php';
        
        if (!file_exists($mainFile)) {
            return null;
        }
        
        $content = file_get_contents($mainFile);
        
        // Cerca pattern per configurazione menu nei commenti
        if (preg_match('/\/\*\*[\s\S]*?Menu Config:(.*?)(?=\*\/)/s', $content, $matches)) {
            $configStr = trim($matches[1]);
            
            // Prova a decodificare come JSON
            $config = json_decode($configStr, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $config;
            }
        }
        
        // Configurazione di default basata sul tipo di plugin
        return $this->generateDefaultMenuConfig($pluginName, $content);
    }
    
    /**
     * Genera configurazione menu di default per un plugin
     * @param string $pluginName Nome del plugin
     * @param string $content Contenuto del file del plugin
     * @return array Configurazione menu di default
     */
    private function generateDefaultMenuConfig(string $pluginName, string $content): array {
        // Determina il blocco appropriato in base al tipo di plugin
        $blockKey = 'tools'; // default
        
        if (stripos($content, 'backup') !== false) {
            $blockKey = 'tools';
        } elseif (stripos($content, 'security') !== false) {
            $blockKey = 'system';
        } elseif (stripos($content, 'content') !== false || stripos($content, 'post') !== false) {
            $blockKey = 'content';
        } elseif (stripos($content, 'user') !== false) {
            $blockKey = 'users';
        }
        
        // Estrai nome dal header del plugin
        $pluginDisplayName = $pluginName;
        if (preg_match('/Plugin Name:\s*(.+)$/m', $content, $matches)) {
            $pluginDisplayName = trim($matches[1]);
        }
        
        return [
            'items' => [
                [
                    'block_key' => $blockKey,
                    'label' => $pluginDisplayName,
                    'url' => '/admin/' . str_replace('_', '-', $pluginName),
                    'icon' => 'fas fa-puzzle-piece',
                    'permission_key' => 'manage_' . $pluginName,
                    'position' => 100
                ]
            ]
        ];
    }
    
    /**
     * Verifica se la tabella menu_blocks ha la colonna plugin
     * @return bool True se la colonna esiste
     */
    public function checkMenuTablesStructure(): bool {
        try {
            // Verifica struttura menu_blocks
            $hasPluginColumnBlocks = $this->checkColumnExists('menu_blocks', 'plugin');
            
            if (!$hasPluginColumnBlocks) {
                // Aggiungi colonna plugin a menu_blocks
                $this->db->exec("ALTER TABLE menu_blocks ADD COLUMN plugin VARCHAR(255) DEFAULT NULL");
                LogHelper::info('Added plugin column to menu_blocks table');
            }
            
            // Verifica struttura menu_items
            $hasPluginColumnItems = $this->checkColumnExists('menu_items', 'plugin');
            
            if (!$hasPluginColumnItems) {
                // Aggiungi colonna plugin a menu_items
                $this->db->exec("ALTER TABLE menu_items ADD COLUMN plugin VARCHAR(255) DEFAULT NULL");
                LogHelper::info('Added plugin column to menu_items table');
            }
            
            return true;
            
        } catch (\Exception $e) {
            LogHelper::error('Failed to check/update menu tables structure', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Verifica se una colonna esiste in una tabella (compatibile SQLite/MySQL)
     * @param string $table Nome tabella
     * @param string $column Nome colonna
     * @return bool True se la colonna esiste
     */
    private function checkColumnExists(string $table, string $column): bool {
        try {
            // Prova prima con SQLite
            $stmt = $this->db->prepare("PRAGMA table_info({$table})");
            $stmt->execute();
            $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (!empty($columns)) {
                // È SQLite
                foreach ($columns as $col) {
                    if ($col['name'] === $column) {
                        return true;
                    }
                }
                return false;
            }
            
            // Fallback per MySQL
            $stmt = $this->db->prepare("SHOW COLUMNS FROM {$table} LIKE :column");
            $stmt->bindParam(':column', $column);
            $stmt->execute();
            return $stmt->rowCount() > 0;
            
        } catch (\Exception $e) {
            // Se fallisce tutto, prova a fare una query di test
            try {
                $this->db->query("SELECT {$column} FROM {$table} LIMIT 1");
                return true;
            } catch (\Exception $e2) {
                return false;
            }
        }
    }
}
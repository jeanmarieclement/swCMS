<?php

namespace App\Services;

use PDO;
use App\Core\Database\Database;

// App/Services/AdminMenuService.php
// Service for dynamic admin menu management (blocks, items, submenus, plugin support)

class AdminMenuService
{
    protected $pdo;
    protected $roleService;

    /**
     * Constructor
     * @param PDO $pdo
     * @param object $roleService
     */
    public function __construct($roleService)
    {
        $this->roleService = $roleService;
        $this->pdo = Database::getInstance();
    }

    /**
     * Get the full admin menu structure, filtered by user role.
     * @param string $userRole
     * @return array
     */
    public function getMenu($userRole)
    {
        // 1. Load all active blocks ordered by position
        $blocksStmt = $this->pdo->prepare("SELECT * FROM menu_blocks WHERE active = 1 ORDER BY position ASC");
        $blocksStmt->execute();
        $blocks = $blocksStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 2. Load all active items ordered by block and position
        $itemsStmt = $this->pdo->prepare("SELECT * FROM menu_items WHERE active = 1 ORDER BY block_id ASC, position ASC");
        $itemsStmt->execute();
        $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

        // 3. Build a map of items by parent_id (for submenus)
        $itemsByParent = [];
        foreach ($items as $item) {
            $itemsByParent[$item['parent_id']][] = $item;
        }

        // 4. Recursive function to build menu tree
        $buildTree = function($parentId) use (&$buildTree, $itemsByParent, $userRole) {
            $tree = [];
            if (!isset($itemsByParent[$parentId])) return $tree;
            foreach ($itemsByParent[$parentId] as $item) {
                // Permission check
                if ($item['permission_key'] && !$this->roleService->canAccessTemplate($userRole, $item['permission_key'])) {
                    continue;
                }
                $item['children'] = $buildTree($item['id']);
                $tree[] = $item;
            }
            return $tree;
        };

        // 5. Compose the menu structure
        $menu = [];
        foreach ($blocks as $block) {
            $blockItems = [];
            if (isset($itemsByParent[null])) {
                foreach ($itemsByParent[null] as $item) {
                    if ($item['block_id'] == $block['id']) {
                        // Permission check
                        if ($item['permission_key'] && !$this->roleService->canAccessTemplate($userRole, $item['permission_key'])) {
                            continue;
                        }
                        $item['children'] = $buildTree($item['id']);
                        $blockItems[] = $item;
                    }
                }
            }
            // Only add block if it has visible items
            if (!empty($blockItems)) {
                $block['items'] = $blockItems;
                $menu[] = $block;
            }
        }
        return $menu;
    }

    /**
     * Register a new menu block (for plugins or core extensions)
     * @param array $data
     * @return int|false Inserted block ID or false
     */
    public function registerMenuBlock($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO menu_blocks (name, `key`, position, active) VALUES (:name, :key, :position, :active)");
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':key', $data['key']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':active', $data['active']);
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Register a new menu item (for plugins or core extensions)
     * @param array $data
     * @return int|false Inserted item ID or false
     */
    public function registerMenuItem($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO menu_items (block_id, parent_id, label, url, icon, permission_key, position, plugin, active) VALUES (:block_id, :parent_id, :label, :url, :icon, :permission_key, :position, :plugin, :active)");
        $stmt->bindParam(':block_id', $data['block_id']);
        $stmt->bindParam(':parent_id', $data['parent_id']);
        $stmt->bindParam(':label', $data['label']);
        $stmt->bindParam(':url', $data['url']);
        $stmt->bindParam(':icon', $data['icon']);
        $stmt->bindParam(':permission_key', $data['permission_key']);
        $stmt->bindParam(':position', $data['position']);
        $stmt->bindParam(':plugin', $data['plugin']);
        $stmt->bindParam(':active', $data['active']);
        if ($stmt->execute()) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }
}

<?php
require_once __DIR__ . '/../../app/core/Database/Migration.php';

use App\Core\Database\Migration;

class SeedAdminMenu extends Migration
{
    public function __construct(\PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    public function up()
    {
        try {
            $this->db->beginTransaction();

            // Clear existing data and re-seed from scratch
            $this->db->exec("DELETE FROM menu_items");
            $this->db->exec("DELETE FROM menu_blocks");

            $isSqlite = (defined('DB_DRIVER') && DB_DRIVER === 'sqlite')
                || $this->db->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite';

            if ($isSqlite) {
                $this->db->exec("DELETE FROM sqlite_sequence WHERE name='menu_blocks'");
                $this->db->exec("DELETE FROM sqlite_sequence WHERE name='menu_items'");
            } else {
                $this->db->exec("ALTER TABLE menu_blocks AUTO_INCREMENT = 1");
                $this->db->exec("ALTER TABLE menu_items AUTO_INCREMENT = 1");
            }

            // Insert blocks
            $blocks = [
                ['Dashboard', 'dashboard', 1],
                ['Content',   'content',   2],
                ['Appearance','appearance', 3],
                ['Users',     'users',      4],
                ['System',    'system',     5],
            ];

            $blockInsert = $this->db->prepare(
                "INSERT INTO menu_blocks (name, `key`, position, active) VALUES (?, ?, ?, 1)"
            );
            foreach ($blocks as $b) {
                $blockInsert->execute($b);
            }

            // Fetch block IDs by key
            $blockIds = [];
            $rows = $this->db->query("SELECT id, `key` FROM menu_blocks")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $blockIds[$row['key']] = $row['id'];
            }

            // Insert items: [block_key, label, url, icon, permission_key, position]
            $items = [
                // Dashboard
                ['dashboard', 'Dashboard',  '/admin/dashboard', 'fas fa-tachometer-alt', null,               1],

                // Content
                ['content', 'Articles',  '/admin/articles',  'fas fa-newspaper',  'admin.articles',  1],
                ['content', 'Pages',     '/admin/pages',     'fas fa-file',        'admin.pages',     2],
                ['content', 'Categories','/admin/categories','fas fa-folder',      'admin.categories',3],
                ['content', 'Tags',      '/admin/tags',      'fas fa-tags',        'admin.tags',       4],
                ['content', 'Comments',  '/admin/comments',  'fas fa-comments',   'admin.comments',  5],
                ['content', 'Media',     '/admin/media',     'fas fa-images',      'admin.media',     6],

                // Appearance
                ['appearance', 'Themes', '/admin/themes', 'fas fa-palette',    'admin.themes', 1],
                ['appearance', 'Menus',  '/admin/menus',  'fas fa-bars',       'admin.menus',  2],

                // Users
                ['users', 'Users', '/admin/users', 'fas fa-users',     'admin.users', 1],
                ['users', 'Roles', '/admin/roles', 'fas fa-shield-alt','admin.roles', 2],

                // System
                ['system', 'Settings', '/admin/settings', 'fas fa-cog',         'admin.settings', 1],
                ['system', 'Plugins',  '/admin/plugins',  'fas fa-puzzle-piece','admin.plugins',  2],
            ];

            $itemInsert = $this->db->prepare(
                "INSERT INTO menu_items (block_id, parent_id, label, url, icon, permission_key, position, plugin, active)
                 VALUES (?, NULL, ?, ?, ?, ?, ?, NULL, 1)"
            );
            foreach ($items as [$blockKey, $label, $url, $icon, $permKey, $pos]) {
                $itemInsert->execute([$blockIds[$blockKey], $label, $url, $icon, $permKey, $pos]);
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

    public function down()
    {
        $this->db->exec("DELETE FROM menu_items");
        $this->db->exec("DELETE FROM menu_blocks");
    }
}

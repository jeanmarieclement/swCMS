<?php

namespace App\Models;

use App\Core\Model;

class Menu extends Model
{
    protected $table = 'menus';

    public function getAllMenus()
    {
        return $this->query("SELECT * FROM {$this->table} ORDER BY position ASC, id ASC")->fetchAll();
    }

    public function getMenuById($id)
    {
        return $this->getById($id);
    }

    public function getMenusByLocation($location)
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE location = :location AND active = 1 ORDER BY position ASC",
            [':location' => $location]
        )->fetchAll();
    }

    public function createMenu($data)
    {
        // Gestisce URL automatico basato sul tipo
        $url = $this->generateUrl($data);

        // Prepara i dati con i defaults e formato per insert()
        $insertData = [
            ':title' => $data['title'],
            ':url' => $url,
            ':type' => $data['type'] ?? 'custom',
            ':content_id' => $data['content_id'] ?? null,
            ':location' => $data['location'],
            ':position' => $data['position'] ?? 0,
            ':parent_id' => $data['parent_id'] ?: null,
            ':active' => $data['active'] ?? 1,
            ':target' => $data['target'] ?? '_self',
            ':css_class' => $data['css_class'] ?? '',
            ':created_at' => date('Y-m-d H:i:s'),
            ':updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->insert($insertData);
    }

    public function updateMenu($id, $data)
    {
        // Gestisce URL automatico basato sul tipo
        $url = $this->generateUrl($data);

        // Prepara i dati con i defaults e aggiunge updated_at
        $updateData = [
            'title' => $data['title'],
            'url' => $url,
            'type' => $data['type'] ?? 'custom',
            'content_id' => $data['content_id'] ?? null,
            'location' => $data['location'],
            'position' => $data['position'] ?? 0,
            'parent_id' => $data['parent_id'] ?: null,
            'active' => $data['active'] ?? 1,
            'target' => $data['target'] ?? '_self',
            'css_class' => $data['css_class'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->update($id, $updateData);
    }

    public function deleteMenu($id)
    {
        // Prima elimina i sottomenu utilizzando query() del core
        $this->query("DELETE FROM {$this->table} WHERE parent_id = :parent_id", [':parent_id' => $id]);

        // Poi elimina il menu principale utilizzando delete() del core
        return $this->delete($id);
    }

    public function getMenuHierarchy($location = null)
    {
        if ($location) {
            $menus = $this->query(
                "SELECT * FROM {$this->table} WHERE location = :location AND active = 1 ORDER BY position ASC, id ASC",
                [':location' => $location]
            )->fetchAll();
        } else {
            $menus = $this->query(
                "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY position ASC, id ASC"
            )->fetchAll();
        }

        return $this->buildMenuTree($menus);
    }

    private function buildMenuTree($menus, $parentId = null)
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu['parent_id'] == $parentId) {
                $menu['children'] = $this->buildMenuTree($menus, $menu['id']);
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    public function getMaxPosition($location)
    {
        $result = $this->query(
            "SELECT MAX(position) as max_pos FROM {$this->table} WHERE location = :location",
            [':location' => $location]
        )->fetch();
        return $result['max_pos'] ?? 0;
    }

    /**
     * Genera URL automatico basato sul tipo di menu
     */
    private function generateUrl($data)
    {
        $type = $data['type'] ?? 'custom';

        switch ($type) {
            case 'page':
                if (!empty($data['content_id'])) {
                    return $this->getPageUrl($data['content_id']);
                }
                break;

            case 'post':
                if (!empty($data['content_id'])) {
                    return $this->getPostUrl($data['content_id']);
                }
                break;

            case 'custom':
            default:
                return $data['url'] ?? '#';
        }

        return $data['url'] ?? '#';
    }

    /**
     * Ottiene l'URL di una pagina basato sull'ID
     */
    private function getPageUrl($pageId)
    {
        $result = $this->query(
            "SELECT slug FROM pages WHERE id = :id LIMIT 1",
            [':id' => $pageId]
        )->fetch();

        return $result ? "/page/{$result['slug']}" : "#";
    }

    /**
     * Ottiene l'URL di un post basato sull'ID
     */
    private function getPostUrl($postId)
    {
        $result = $this->query(
            "SELECT slug FROM posts WHERE id = :id LIMIT 1",
            [':id' => $postId]
        )->fetch();

        return $result ? "/article/{$result['slug']}" : "#";
    }

    /**
     * Ottiene tutte le pagine per il dropdown
     */
    public function getAllPages()
    {
        return $this->query(
            "SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY title ASC"
        )->fetchAll();
    }

    /**
     * Ottiene tutti i post per il dropdown
     */
    public function getAllPosts()
    {
        return $this->query(
            "SELECT id, title, slug FROM posts WHERE status = 'published' ORDER BY title ASC"
        )->fetchAll();
    }

    /**
     * Ottiene i tipi di menu disponibili
     */
    public function getMenuTypes()
    {
        return [
            'custom' => 'Collegamento Personalizzato',
            'page' => 'Pagina',
            'post' => 'Articolo'
        ];
    }

    /**
     * Get allowed columns for ORDER BY clause
     *
     * @return array List of column names allowed in ORDER BY
     */
    protected function getAllowedOrderByColumns()
    {
        return ['id', 'title', 'type', 'location', 'position', 'parent_id', 'active', 'created_at', 'updated_at'];
    }
}

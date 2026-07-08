<?php

namespace App\Controllers\Admin;

use App\Models\Menu;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\SessionHelper;
use App\Helpers\LogHelper;

class MenuController extends AdminController
{
    private $menuModel;

    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->menuModel = new Menu();
    }

    public function indexAction()
    {
        $menus = $this->menuModel->getAllMenus();

        $this->render('admin/menus/index', [
            'menus' => $menus,
            'page_title' => 'Gestione Menu'
        ]);
    }

    public function createAction()
    {
        // Pulisci i dati di sessione precedenti per evitare interferenze
        SessionHelper::removeValue('old_input');
        SessionHelper::removeValue('flash_errors');

        $locations = ['header', 'footer', 'sidebar'];
        $parentMenus = $this->menuModel->getAllMenus();
        $menuTypes = $this->menuModel->getMenuTypes();
        $pages = $this->menuModel->getAllPages();
        $posts = $this->menuModel->getAllPosts();


        $this->render('admin/menus/create', [
            'locations' => $locations,
            'parent_menus' => $parentMenus,
            'menu_types' => $menuTypes,
            'pages' => $pages,
            'posts' => $posts,
            'page_title' => 'Nuovo Menu'
        ]);
    }

    public function storeAction()
    {
        if (!RequestHelper::isPost()) {
            $this->redirect('/admin/menus');
            return;
        }

        $this->requireCsrf('/admin/menus/create', 'menu creation');

        // Determina content_id basato sul tipo
        $content_id = null;
        $type = RequestHelper::post('type', 'custom');
        $pageId = RequestHelper::post('page_id', '', 'raw');
        $postId = RequestHelper::post('post_id', '', 'raw');

        if ($type === 'page' && $pageId !== '') {
            $content_id = (int)$pageId;
        } elseif ($type === 'post' && $postId !== '') {
            $content_id = (int)$postId;
        }

        $parentId = RequestHelper::post('parent_id', '', 'raw');
        $data = [
            'title' => trim(RequestHelper::post('title', '')),
            'url' => trim(RequestHelper::post('url', '')),
            'type' => $type,
            'content_id' => $content_id,
            'location' => RequestHelper::post('location', 'header'),
            'position' => RequestHelper::post('position', 0, 'int'),
            'parent_id' => !empty($parentId) ? (int)$parentId : null,
            'active' => RequestHelper::post('active', null) !== null ? 1 : 0,
            'target' => RequestHelper::post('target', '_self'),
            'css_class' => trim(RequestHelper::post('css_class', ''))
        ];

        $errors = $this->validateMenuData($data);

        if (!empty($errors)) {
            SessionHelper::setValue('flash_errors', $errors);
            SessionHelper::setValue('old_input', $data);
            $this->redirect('/admin/menus/create');
            return;
        }

        if ($data['position'] == 0) {
            $data['position'] = $this->menuModel->getMaxPosition($data['location']) + 1;
        }

        if ($this->menuModel->createMenu($data)) {
            SessionHelper::setFlashMessage('Menu creato con successo.', 'success');
        } else {
            SessionHelper::setFlashMessage('Errore durante la creazione del menu.', 'error');
        }

        $this->redirect('/admin/menus');
    }

    public function editAction()
    {
        $id = RequestHelper::get('id', 0, 'int');

        SessionHelper::removeValue('flash_errors');

        if (empty($id) && !isset($this->params[0])) {
            $this->redirect('/admin/menus');
            return;
        }
        if (empty($id)) {
            $id = $this->params[0];
        }
        $menu = $this->menuModel->getMenuById($id);

        if (!$menu) {
            SessionHelper::setFlashMessage('Menu non trovato.', 'error');
            $this->redirect('/admin/menus');
            return;
        }

        $locations = ['header', 'footer', 'sidebar'];
        $parentMenus = $this->menuModel->getAllMenus();
        $menuTypes = $this->menuModel->getMenuTypes();
        $pages = $this->menuModel->getAllPages();
        $posts = $this->menuModel->getAllPosts();

        $this->render('admin/menus/edit', [
            'menu' => $menu,
            'locations' => $locations,
            'parent_menus' => $parentMenus,
            'menu_types' => $menuTypes,
            'pages' => $pages,
            'posts' => $posts,
            'page_title' => 'Modifica Menu'
        ]);
    }

    public function updateAction()
    {
        $id = RequestHelper::get('id', 0, 'int');

        if (empty($id) && !isset($this->params[0])) {
            $this->redirect('/admin/menus');
            return;
        }

        if (empty($id)) {
            $id = $this->params[0];
        }

        if (!RequestHelper::isPost()) {
            $this->redirect('/admin/menus');
            return;
        }

        $this->requireCsrf("/admin/menus/edit/$id", 'menu update');

        $menu = $this->menuModel->getMenuById($id);
        if (!$menu) {
            SessionHelper::setFlashMessage('Menu non trovato.', 'error');
            $this->redirect('/admin/menus');
            return;
        }

        // Determina content_id basato sul tipo
        $content_id = null;
        $type = RequestHelper::post('type', 'custom');
        $pageId = RequestHelper::post('page_id', '', 'raw');
        $postId = RequestHelper::post('post_id', '', 'raw');

        if ($type === 'page' && $pageId !== '') {
            $content_id = (int)$pageId;
        } elseif ($type === 'post' && $postId !== '') {
            $content_id = (int)$postId;
        }

        $parentId = RequestHelper::post('parent_id', '', 'raw');
        $data = [
            'title' => trim(RequestHelper::post('title', '')),
            'url' => trim(RequestHelper::post('url', '')),
            'type' => $type,
            'content_id' => $content_id,
            'location' => RequestHelper::post('location', 'header'),
            'position' => RequestHelper::post('position', 0, 'int'),
            'parent_id' => !empty($parentId) ? (int)$parentId : null,
            'active' => RequestHelper::post('active', null) !== null ? 1 : 0,
            'target' => RequestHelper::post('target', '_self'),
            'css_class' => trim(RequestHelper::post('css_class', ''))
        ];

        $errors = $this->validateMenuData($data);

        if (!empty($errors)) {
            SessionHelper::setValue('flash_errors', $errors);
            SessionHelper::setValue('old_input', $data);
            $this->redirect("/admin/menus/edit/$id");
            return;
        }

        if ($this->menuModel->updateMenu($id, $data)) {
            SessionHelper::setFlashMessage('Menu aggiornato con successo.', 'success');
        } else {
            SessionHelper::setFlashMessage('Errore durante l\'aggiornamento del menu.', 'error');
        }

        $this->redirect('/admin/menus');
    }

    public function deleteAction()
    {
        if (!RequestHelper::isPost()) {
            $this->redirect('/admin/menus');
            return;
        }
        $this->requireCsrf('/admin/menus', 'menu deletion');

        $id = RequestHelper::get('id', 0, 'int');

        if (empty($id) && !isset($this->params[0])) {
            $this->redirect('/admin/menus');
            return;
        }
        if (empty($id)) {
            $id = $this->params[0];
        }

        $menu = $this->menuModel->getMenuById($id);

        if (!$menu) {
            SessionHelper::setFlashMessage('Menu non trovato.', 'error');
            $this->redirect('/admin/menus');
            return;
        }

        if ($this->menuModel->deleteMenu($id)) {
            SessionHelper::setFlashMessage('Menu eliminato con successo.', 'success');
        } else {
            SessionHelper::setFlashMessage('Errore durante l\'eliminazione del menu.', 'error');
        }

        $this->redirect('/admin/menus');
    }

    private function validateMenuData($data)
    {
        $errors = [];

        if (empty($data['title'])) {
            $errors[] = 'Il titolo è obbligatorio.';
        }

        // Validazione URL diversa in base al tipo
        $type = $data['type'] ?? 'custom';
        if ($type === 'custom') {
            if (empty($data['url'])) {
                $errors[] = 'L\'URL è obbligatorio per i collegamenti personalizzati.';
            } elseif (!filter_var($data['url'], FILTER_VALIDATE_URL) && !preg_match('/^\//', $data['url'])) {
                $errors[] = 'L\'URL deve essere valido o iniziare con /.';
            }
        } elseif ($type === 'page' || $type === 'post') {
            if ($data['content_id'] === null || $data['content_id'] === '') {
                $errors[] = 'Seleziona un contenuto da collegare.';
            }
        }

        if (!in_array($data['location'], ['header', 'footer', 'sidebar'])) {
            $errors[] = 'Posizione non valida.';
        }

        if (!in_array($data['target'], ['_self', '_blank', '_parent', '_top'])) {
            $errors[] = 'Target non valido.';
        }

        if ($data['parent_id'] && !$this->menuModel->getMenuById($data['parent_id'])) {
            $errors[] = 'Menu genitore non valido.';
        }

        return $errors;
    }
}

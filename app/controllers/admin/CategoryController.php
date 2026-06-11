<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Helpers\SessionHelper;
use App\Helpers\RedirectHelper;
use App\middlewares\AuthMiddleware;
use App\Controllers\Admin\AdminController;

/**
 * AdminCategoryController.php
 * Controller for category management (backend)
 */
class CategoryController extends AdminController
{
    protected $categoryModel;

    /**
     * CategoryController constructor.
     * Requires admin authentication and initializes the category model.
     *
     * @param array $params Optional parameters for the controller
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // Only admin can manage users
        AuthMiddleware::requireAdmin();

        $this->categoryModel = new Category();
    }

    /**
     * Displays the list of categories.
     *
     * @return void
     */
    public function indexAction()
    {
        $categories = $this->categoryModel->getAll();
        $this->render('admin/categories/category_list', [
            'categories' => $categories,
            'success' => $this->getParam('success'),
            'error' => $this->getParam('error')
        ]);
    }

    /**
     * Displays the form to create a new category.
     *
     * @return void
     */
    public function createAction()
    {
        $this->render('admin/categories/category_form', [
            'action' => 'create',
            'category' => null,
            'errors' => []
        ]);
    }

    /**
     * Handles the creation of a new category after validation.
     *
     * @return void
     */
    public function storeAction()
    {
        $name = $this->getParam('name', '', 'POST');
        $slug = $this->getParam('slug', '', 'POST');
        $description = $this->getParam('description', '', 'POST');
        $errors = [];
        if ($name === '') {
            $errors[] = 'Il nome è obbligatorio.';
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Lo slug può contenere solo lettere minuscole, numeri e trattini.';
        }
        if ($this->categoryModel->slugExists($slug)) {
            $errors[] = 'Slug già utilizzato.';
        }
        if ($errors) {
            $this->render('admin/categories/category_form', [
                'action' => 'create',
                'category' => ['name' => $name, 'slug' => $slug, 'description' => $description],
                'errors' => $errors
            ]);
            return;
        }
        $this->categoryModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);
        SessionHelper::setFlashMessage('Category created successfully', 'success');
        RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
    }

    /**
     * Displays the form to edit an existing category.
     *
     * @return void
     */
    public function editAction()
    {
        $id = isset($this->params[0]) ? (int)$this->params[0] : 0;
        if ($id <= 0) {
            SessionHelper::setFlashMessage('Invalid category ID', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            SessionHelper::setFlashMessage('Category not found', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        $this->render('admin/categories/category_form', [
            'action' => 'edit',
            'category' => $category,
            'errors' => []
        ]);
    }

    /**
     * Handles the update of an existing category after validation.
     *
     * @return void
     */
    public function updateAction()
    {
        $id = isset($this->params[0]) ? (int)$this->params[0] : 0;
        if ($id <= 0) {
            SessionHelper::setFlashMessage('Invalid category ID', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        $name = $this->getParam('name', '', 'POST');
        $slug = $this->getParam('slug', '', 'POST');
        $description = $this->getParam('description', '', 'POST');
        $errors = [];
        if ($name === '') {
            $errors[] = 'name is required.';
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'slug can only contain lowercase letters, numbers, and hyphens.';
        }
        $existing = $this->categoryModel->getBySlug($slug);
        if ($existing && $existing['id'] != $id) {
            $errors[] = 'Slug already used.';
        }
        if ($errors) {
            $category = ['id' => $id, 'name' => $name, 'slug' => $slug, 'description' => $description];
            $this->render('admin/categories/category_form', [
                'action' => 'edit',
                'category' => $category,
                'errors' => $errors
            ]);
            return;
        }
        $this->categoryModel->update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);
        SessionHelper::setFlashMessage('Category updated successfully', 'success');
        RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
    }

    /**
     * Deletes a category by ID.
     *
     * @return void
     */
    public function deleteAction()
    {
        $id = isset($this->params[0]) ? (int)$this->params[0] : 0;
        if ($id <= 0) {
            SessionHelper::setFlashMessage('Invalid category ID', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            SessionHelper::setFlashMessage('Category not found', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        if ($this->categoryModel->isAssignedToContent($id)) {
            $this->render('admin/categories/category_delete', [
                'category' => $category,
                'assigned' => true
            ]);
            return;
        }
        $this->render('admin/categories/category_delete', [
            'category' => $category,
            'assigned' => false
        ]);
    }

    public function destroyAction()
    {
        $id = isset($this->params[0]) ? (int)$this->params[0] : 0;
        if ($id <= 0) {
            SessionHelper::setFlashMessage('Invalid category ID', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        if ($this->categoryModel->isAssignedToContent($id)) {
            SessionHelper::setFlashMessage('Category is assigned to content', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
        }
        $this->categoryModel->delete($id);
        SessionHelper::setFlashMessage('Category deleted successfully', 'success');
        RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/categories');
    }


    /**
     * Handles AJAX request to create a new category and returns JSON response.
     *
     * @return void
     */
    public function ajaxCreateAction()
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim($input['name'] ?? '');
        $slug = trim($input['slug'] ?? '');
        $description = trim($input['description'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors[] = 'name is required';
        }
        if ($slug === '') {
            $errors[] = 'slug is required';
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'slug can only contain lowercase letters, numbers, and hyphens';
        }
        if ($this->categoryModel->slugExists($slug)) {
            $errors[] = 'slug already exists';
        }

        if ($errors) {
            echo json_encode(['success' => false, 'error' => implode('. ', $errors)]);
            exit;
        }

        $catId = $this->categoryModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]);
        if ($catId) {
            $cat = $this->categoryModel->getById($catId);
            echo json_encode(['success' => true, 'category' => [
                'id' => $cat['id'],
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'description' => $cat['description']
            ]]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error during creation.']);
        }
        exit;
    }
}

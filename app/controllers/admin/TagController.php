<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\SecurityHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SessionHelper;
use App\Helpers\StringHelper;
use App\Models\Tag;
use App\middlewares\AuthMiddleware;

/**
 * Controller for tag management (backend)
 *
 * @package App\Controllers\Admin
 */
class TagController extends AdminController
{
    /**
     * @var Tag
     */
    protected Tag $tagModel;

    /**
     * TagController constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        AuthMiddleware::requireAdmin();
        $this->tagModel = new Tag();
    }


    /**
     * Visualizza la lista dei tag con messaggi flash
     *
     * @return void
     */
    public function indexAction(): void
    {
        $tags = $this->tagModel->getAll();
        $success = SessionHelper::getFlashMessage('success');
        $error = SessionHelper::getFlashMessage('error');
        $this->render('admin/tags/tag_list', [
            'tags' => $tags,
            'success' => $success,
            'error' => $error,
        ]);
    }

    /**
     * Mostra il form per la creazione di un nuovo tag
     *
     * @return void
     */
    public function createAction(): void
    {
        $this->render('admin/tags/tag_form', [
            'action' => 'create',
            'tag' => null,
            'errors' => [],
        ]);
    }


    /**
     * Salva un nuovo tag dopo validazione e sanitizzazione
     *
     * @return void
     */
    public function storeAction(): void
    {
        $this->requireCsrf('/admin/tags', 'tag creation');

        $name = $this->getParam('name', '', 'POST');
        $slug = $this->getParam('slug', '', 'POST');
        $description = $this->getParam('description', '', 'POST');
        $errors = [];

        if ($name === '') {
            $errors[] = 'Il nome è obbligatorio.';
        }
        // Se lo slug non è fornito, generarlo automaticamente
        if ($slug === '') {
            $slug = StringHelper::slugify($name);
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Lo slug può contenere solo lettere minuscole, numeri e trattini.';
        }
        if ($this->tagModel->slugExists($slug)) {
            $errors[] = 'Slug già utilizzato.';
        }
        if ($errors) {
            $this->render('admin/tags/tag_form', [
                'action' => 'create',
                'tag' => [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => $errors,
            ]);
            return;
        }
        $this->tagModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);
        SessionHelper::setFlashMessage('success', 'Tag creato con successo.');
        RedirectHelper::redirect('/admin/tags');
    }


    /**
     * Displays the form to edit an existing tag.
     *
     * @return void
     */
    public function editAction(): void
    {
        $id = $this->getParam('id');
        if (!$id) {
            SessionHelper::setFlashMessage('error', 'ID tag non valido.');
            RedirectHelper::redirect('/admin/tags');
        }
        $tag = $this->tagModel->getById($id);
        if (!$tag) {
            SessionHelper::setFlashMessage('error', 'Tag non trovato.');
            RedirectHelper::redirect('/admin/tags');
        }
        $this->render('admin/tags/tag_form', [
            'action' => 'edit',
            'tag' => $tag,
            'errors' => [],
        ]);
    }


    /**
     * Handles the update of an existing tag after validation and sanitization.
     *
     * @return void
     */
    public function updateAction(): void
    {
        $this->requireCsrf('/admin/tags', 'tag update');

        $id = $this->getParam('id', null, 'POST');
        $name = $this->getParam('name', '', 'POST');
        $slug = $this->getParam('slug', '', 'POST');
        $description = $this->getParam('description', '', 'POST');
        $errors = [];

        if ($name === '') {
            $errors[] = 'Il nome è obbligatorio.';
        }
        if ($slug === '') {
            $slug = StringHelper::slugify($name);
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Lo slug può contenere solo lettere minuscole, numeri e trattini.';
        }
        $current = $this->tagModel->getById($id);
        if ($this->tagModel->slugExists($slug) && $current && $current['slug'] !== $slug) {
            $errors[] = 'Slug già utilizzato.';
        }
        if ($errors) {
            $this->render('admin/tags/tag_form', [
                'action' => 'edit',
                'tag' => [
                    'id' => $id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $description,
                ],
                'errors' => $errors,
            ]);
            return;
        }
        $this->tagModel->update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);
        SessionHelper::setFlashMessage('success', 'Tag aggiornato con successo.');
        RedirectHelper::redirect('/admin/tags');
    }


    /**
     * Deletes a tag by ID.
     *
     * @return void
     */
    public function deleteAction(): void
    {
        $this->requireCsrf('/admin/tags', 'tag deletion');

        $id = $this->getParam('id', null, 'POST');
        if ($id) {
            $this->tagModel->delete($id);
            SessionHelper::setFlashMessage('success', 'Tag eliminato con successo.');
        } else {
            SessionHelper::setFlashMessage('error', 'ID tag non valido.');
        }
        RedirectHelper::redirect('/admin/tags');
    }


    /**
     * AJAX: crea rapidamente un tag, restituisce JSON
     *
     * @return void
     */
    public function ajaxCreateAction(): void
    {
        header('Content-Type: application/json');
        if (!\App\Helpers\CSRFHelper::validateRequest()) {
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            exit;
        }

        $name = $this->getParam('name', '', 'POST');
        $slug = $this->getParam('slug', '', 'POST');
        $description = $this->getParam('description', '', 'POST');
        $errors = [];

        if ($name === '') {
            $errors[] = 'Il nome è obbligatorio.';
        }
        if ($slug === '') {
            $slug = StringHelper::slugify($name);
        }
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            $errors[] = 'Lo slug può contenere solo lettere minuscole, numeri e trattini.';
        }
        if ($this->tagModel->slugExists($slug)) {
            $errors[] = 'Slug già utilizzato.';
        }
        if ($errors) {
            echo json_encode([
                'success' => false,
                'errors' => $errors,
            ]);
            exit;
        }
        $id = $this->tagModel->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
        ]);
        if ($id) {
            echo json_encode([
                'success' => true,
                // For Select2 compatibility, return the tag as {id, text}
                'tag' => [
                    'id' => $name,
                    'text' => $name
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'errors' => ['Errore nella creazione del tag.'],
            ]);
        }
        exit;
    }

    /**
     * AJAX: restituisce i tag filtrati per l'autocomplete Select2
     * @return void
     */
    public function ajaxListAction(): void
    {
        header('Content-Type: application/json');
        $q = $this->getParam('q', '');
        $tags = $this->tagModel->getAll();
        $results = [];
        foreach ($tags as $tag) {
            if ($q === '' || stripos($tag['name'], $q) !== false) {
                $results[] = [
                    'id' => $tag['name'], // Select2 expects 'id' and 'text'
                    'text' => $tag['name']
                ];
            }
        }
        echo json_encode($results);
        exit;
    }
}

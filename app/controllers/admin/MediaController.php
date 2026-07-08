<?php

namespace App\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Models\MediaModel;
use App\Helpers\SessionHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SecurityHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;

class MediaController extends AdminController
{
    private $mediaModel;

    /**
     * MediaController constructor.
     * Initializes the media model and requires authentication.
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->mediaModel = new MediaModel();
    }

    /**
     * Displays the media library with pagination and filters.
     *
     * @return void
     */
    public function indexAction()
    {
        $page = RequestHelper::get('page', 1, 'int') ?: 1;
        $filters = [
            'type' => RequestHelper::get('type', ''),
            'search' => RequestHelper::get('search', '')
        ];

        $data = $this->mediaModel->getList($filters, $page);

        // Assicuriamoci che ci sia sempre un array items, anche vuoto
        if (!isset($data['items']) || !is_array($data['items'])) {
            $data['items'] = [];
        }

        // Log per debug - aiuta a identificare problemi di redirect
        LogHelper::info('MediaController::indexAction - Media listing', [
            'items_count' => count($data['items']),
            'page' => $page,
            'filters' => $filters,
            'url' => RequestHelper::server('REQUEST_URI', '')
        ]);

        $data['title'] = 'Media Library';
        $data['active_menu'] = 'media';
        $data['filters'] = $filters;

        // Usa render() invece di view() per evitare loop di redirect
        $this->render('admin/media/index', $data);
    }

    /**
     * Displays the details for a specific media item.
     *
     * @param int $id Media item ID
     * @return void
     */
    public function viewAction($id)
    {
        if (empty($id) || !is_numeric($id)) {
            RedirectHelper::redirect('/admin/media');
        }
        $media = $this->mediaModel->getById($id);
        if (!$media) {
            RedirectHelper::redirect('/admin/media?notfound=1');
        }
        $this->render('admin/media/view', [
            'media' => $media,
            'title' => 'Visualizza Media',
            'active_menu' => 'media'
        ]);
    }

    /**
     * Handles media upload via form or AJAX.
     *
     * @return void
     */
    public function uploadAction()
    {
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/media', 'media upload');

            try {
                // $_FILES must be accessed directly (RequestHelper doesn't handle file uploads)
                $files = $_FILES['files'] ?? [];
                $userId = SessionHelper::hasValue('user_id') ? SessionHelper::getValue('user_id') : 0;

                // Pass sanitized POST data, but $_FILES directly
                $postData = [
                    'title' => RequestHelper::post('title', ''),
                    'description' => RequestHelper::post('description', ''),
                    'alt_text' => RequestHelper::post('alt_text', '')
                ];
                $uploaded = $this->mediaModel->upload($files, $userId, $postData);

                if ($this->isAjaxRequest()) {
                    LogHelper::debug('File caricati con successo', [
                        'files' => $uploaded
                    ]);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'files' => $uploaded
                    ]);
                    exit;
                }

                SessionHelper::setFlashMessage('File caricati con successo', 'success');
                RedirectHelper::redirect('/admin/media');
            } catch (\Exception $e) {
                if ($this->isAjaxRequest()) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => $e->getMessage()
                    ]);
                    exit;
                }

                SessionHelper::setFlashMessage($e->getMessage(), 'error');
                RedirectHelper::redirect('/admin/media');
            }
        }

        $this->render('admin/media/upload', [
            'title' => 'Carica Media',
            'active_menu' => 'media'
        ]);
    }

    public function editAction($id = 0)
    {
        if ($id == 0) {
            $id = $this->params[0];
        }
        $media = $this->mediaModel->getById($id);
        if (!$media) {
            SessionHelper::setFlashMessage('Media non trovato', 'error');
            RedirectHelper::redirect('/admin/media');
        }
        $this->render('admin/media/edit', [
            'media' => $media,
            'title' => 'Modifica Media',
            'active_menu' => 'media',
            'admin_url' => $this->settings['ADMIN_URL'],
            'csrf_field' => \App\Helpers\CSRFHelper::getTokenField()
        ]);
    }

    public function updateAction($id = 0)
    {
        if ($id == 0) {
            $id = $this->params[0];
        }
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/media', 'media update');

            try {
                $data = [
                    'title' => RequestHelper::post('title', ''),
                    'description' => RequestHelper::post('description', ''),
                    'alt_text' => RequestHelper::post('alt_text', '')
                ];
                $this->mediaModel->update($id, $data);
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                SessionHelper::setFlashMessage('Media aggiornato', 'success');
                RedirectHelper::redirect('/admin/media/edit/' . $id);
            } catch (\Exception $e) {
                if ($this->isAjaxRequest()) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                SessionHelper::setFlashMessage($e->getMessage(), 'error');
                RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/media/edit/' . $id);
            }
        }
        RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/media');
    }

    /**
     * Deletes a media item by ID.
     *
     * @param int $id Media item ID
     * @return void
     */
    public function deleteAction($id = 0)
    {
        if ($id == 0) {
            $id = $this->params[0];
        }
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/media', 'media deletion');

            try {
                $this->mediaModel->deleteMedia($id);
                if ($this->isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                SessionHelper::setFlashMessage('Media eliminato', 'success');
                RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/media');
            } catch (\Exception $e) {
                if ($this->isAjaxRequest()) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                SessionHelper::setFlashMessage($e->getMessage(), 'error');
                RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/media');
            }
        }
        RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/media');
    }

    /**
     * Checks if the current request is an AJAX request.
     *
     * @return bool
     */
    protected function isAjaxRequest()
    {
        $requestedWith = RequestHelper::server('HTTP_X_REQUESTED_WITH', '');
        LogHelper::debug('isAjaxRequest', [
            'HTTP_X_REQUESTED_WITH' => $requestedWith,
            'isAjax' => !empty($requestedWith) && strtolower($requestedWith) === 'xmlhttprequest'
        ]);
        return !empty($requestedWith) && strtolower($requestedWith) === 'xmlhttprequest';
    }

    /**
     * Restituisce la lista dei media in formato JSON per AJAX
     */
    public function ajaxListAction()
    {
        header('Content-Type: application/json');
        $filters = [
            'type' => RequestHelper::get('type', ''),
            'search' => RequestHelper::get('search', '')
        ];
        $page = RequestHelper::get('page', 1, 'int') ?: 1;
        $data = $this->mediaModel->getList($filters, $page);
        echo json_encode($data);
        exit;
    }

    /**
     * Gestisce upload media via AJAX e restituisce JSON
     */
    public function ajaxUploadAction()
    {
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid CSRF token'
                ]);
                exit;
            }

            try {
                // $_FILES must be accessed directly (RequestHelper doesn't handle file uploads)
                $files = $_FILES['file'] ?? $_FILES['files'] ?? [];
                $userId = SessionHelper::hasValue('user_id') ? SessionHelper::getValue('user_id') : 0;

                // Pass sanitized POST data
                $postData = [
                    'title' => RequestHelper::post('title', ''),
                    'description' => RequestHelper::post('description', ''),
                    'alt_text' => RequestHelper::post('alt_text', '')
                ];
                $uploaded = $this->mediaModel->upload($files, $userId, $postData);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'files' => $uploaded
                ]);
                exit;
            } catch (\Exception $e) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        } else {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Metodo non consentito']);
            exit;
        }
    }
}

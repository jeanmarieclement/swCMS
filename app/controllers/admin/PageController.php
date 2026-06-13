<?php

namespace App\Controllers\Admin;

use App\Models\Page;
use App\Helpers\PaginationHelper;
use App\Helpers\LogHelper;
use App\Helpers\SessionHelper;
use App\Helpers\TinyMCEHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SecurityHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\StringHelper;
use App\middlewares\AuthMiddleware;

/**
 * Page Controller
 * Handles page management in the admin panel
 */

class PageController extends AdminController
{
    private $pageModel;

    /**
     * PageController constructor.
     * Initializes the page model and requires authentication.
     *
     * @param array $params Optional parameters for the controller
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->pageModel = new Page();

        // Require authentication for all page management pages
        // For basic page access, at least author role is required
        AuthMiddleware::requireAuthor();
    }

    /**
     * Default action - list all pages
     */
    public function indexAction()
    {
        // Get current page for pagination
        $currentPage = (int)$this->getParam('page', 1);
        $currentPage = max(1, $currentPage);
        $perPage = 10;

        // Get status filter
        $status = $this->getParam('status', 'all');
        if (!in_array($status, ['all', 'published', 'draft', 'trash'])) {
            $status = 'all';
        }

        // Get total count for pagination
        $totalItems = $this->pageModel->countAll($status);

        // Use PaginationHelper to generate pagination data
        $pagination = PaginationHelper::paginate($totalItems, $currentPage, $perPage, $status);

        // Get pages with pagination
        $pages = $this->pageModel->getAllForAdmin($status, $pagination['per_page'], $pagination['offset']);

        // Set success/error messages from session
        $message = '';
        $messageType = '';

        if (SessionHelper::hasFlashMessage()) {
            $flash = SessionHelper::getFlashMessage();
            $message = $flash['message'] ?? '';
            $messageType = $flash['type'] ?? 'success';
        }

        // Render the view using the controller's render method
        $this->render('admin/pages/index', [
            'title' => 'Manage Pages',
            'pages' => $pages,
            'pagination' => $pagination,
            'status' => $status,
            'message' => $message,
            'messageType' => $messageType,
            'site_url' => $this->settings['SITE_URL']
        ]);
    }

    /**
     * Display the form to create a new page
     */
    public function createAction()
    {
        // Get parent pages for dropdown
        $parentPages = $this->pageModel->getPagesForDropdown();

        // Include TinyMCE
        $tinymceInclude = TinyMCEHelper::includeTinyMCE();

        // Render the view using the controller's render method
        $this->render('admin/pages/create', [
            'page' => [
                'title' => '',
                'slug' => '',
                'content' => '',
                'status' => 'draft',
                'parent_id' => null,
                'template' => 'default',
                'order' => 0
            ],
            'pages' => $parentPages,
            'action' => 'create',
            'tinymce_include' => $tinymceInclude,
            'csrf_token' => SessionHelper::getValue('csrf_token'),
            'admin_url' => $this->settings['ADMIN_URL'],
            'site_url' => $this->settings['SITE_URL']
        ]);
    }

    /**
     * Store a new page
     */
    public function storeAction()
    {
        // Check if form was submitted
        if (!RequestHelper::isPost()) {
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page creation from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/create');
            return;
        }

        // Validate required fields
        $title = RequestHelper::post('title', '');
        if (empty($title)) {
            SessionHelper::setFlashMessage('Title is required', 'error');
            return;
        }

        // Sanitize and prepare data
        $status = RequestHelper::post('status', 'draft');
        $data = [
            'title' => $title,
            'content' => RequestHelper::post('content', '', 'raw'),
            'status' => in_array($status, ['published', 'draft', 'trash']) ? $status : 'draft',
            'author_id' => SessionHelper::getValue('user_id'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle slug
        $slug = RequestHelper::post('slug', '');
        if (!empty($slug)) {
            $data['slug'] = StringHelper::slugify($slug);
        }

        // Handle parent page
        $parentId = RequestHelper::post('parent_id', 0, 'int');
        if (!empty($parentId)) {
            $data['parent_id'] = $parentId;
        }

        // Handle template
        $template = RequestHelper::post('template', '');
        if (!empty($template)) {
            $data['template'] = $template;
        }

        // Handle order
        $order = RequestHelper::post('order', null, 'int');
        if ($order !== null) {
            $data['order'] = $order;
        }

        // Create the page
        $pageId = $this->pageModel->create($data);

        if ($pageId) {
            // Store the page ID in the session to be used in editAction
            SessionHelper::setValue('edit_page_id', $pageId);
            SessionHelper::setFlashMessage('Page created successfully');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $pageId);
        } else {
            SessionHelper::setFlashMessage('Error creating page', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/create');
        }
    }

    /**
     * Display the form to edit a page
     */
    public function editAction()
    {
        // Get page ID from route parameters or session
        $id = null;
        LogHelper::debug('Page ID from session', ['id' => $this->params]);
        // First check if we have an ID in the route parameters
        if (isset($this->params[0]) && is_numeric($this->params[0])) {
            $id = $this->params[0];
        } elseif (SessionHelper::hasValue('edit_page_id')) {
            // If not, check if we have an ID in the session (from storeAction)
            $id = SessionHelper::getValue('edit_page_id');
            // Clear the session variable after using it
            SessionHelper::removeValue('edit_page_id');
        }

        if (!$id) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get parent pages for dropdown (excluding this page to prevent self-reference)
        $parentPages = $this->pageModel->getPagesForDropdown($id);

        // Include TinyMCE
        $tinymceInclude = TinyMCEHelper::includeTinyMCE();

        // Set success/error messages from session
        $message = '';
        $messageType = '';
        $saved = false;

        if (SessionHelper::hasFlashMessage()) {
            $flash = SessionHelper::getFlashMessage();
            $message = $flash['message'] ?? '';
            $messageType = $flash['type'] ?? 'success';
            $saved = ($messageType === 'success');
        }

        // Render the view using the controller's render method
        $this->render('admin/pages/edit', [
            'page' => $page,
            'pages' => $parentPages,
            'action' => 'edit',
            'message' => $message,
            'messageType' => $messageType,
            'saved' => $saved,
            'tinymce_include' => $tinymceInclude,
            'csrf_token' => SessionHelper::getValue('csrf_token'),
            'admin_url' => $this->settings['ADMIN_URL'],
            'site_url' => $this->settings['SITE_URL']
        ]);
    }

    /**
     * Update a page
     */
    public function updateAction()
    {
        // Get page ID from route parameters
        $id = $this->params[0] ?? null;

        if (!$id || !is_numeric($id)) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Check if form was submitted
        if (!RequestHelper::isPost()) {
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page update from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $id);
            return;
        }

        // Validate required fields
        $title = RequestHelper::post('title', '');
        if (empty($title)) {
            SessionHelper::setFlashMessage('Title is required', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $id);
            return;
        }

        // Get existing page to check if it exists
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Sanitize and prepare data
        $status = RequestHelper::post('status', 'draft');
        $data = [
            'title' => $title,
            'content' => RequestHelper::post('content', '', 'raw'),
            'status' => in_array($status, ['published', 'draft', 'trash']) ? $status : 'draft',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Handle slug
        $slug = RequestHelper::post('slug', '');
        if (!empty($slug)) {
            $data['slug'] = $this->sanitizeSlug($slug);
        }

        // Handle parent page
        $parentId = RequestHelper::post('parent_id', '', 'raw');
        if ($parentId !== '') {
            if (empty($parentId)) {
                $data['parent_id'] = null;
            } else {
                $parentIdInt = (int)$parentId;
                // Prevent setting parent to itself
                if ($parentIdInt != $id) {
                    $data['parent_id'] = $parentIdInt;
                }
            }
        }

        // Handle template
        $template = RequestHelper::post('template', '');
        if (!empty($template)) {
            $data['template'] = $template;
        }

        // Handle order
        $order = RequestHelper::post('order', null, 'int');
        if ($order !== null) {
            $data['order'] = $order;
        }

        // Get revision note if provided
        $revisionNote = RequestHelper::post('revision_note', 'Updated page');

        // Update the page and create a revision
        $success = $this->pageModel->updatePage($id, $data, $revisionNote);

        if ($success) {
            SessionHelper::setFlashMessage('Page updated successfully', 'success');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $id);
        } else {
            SessionHelper::setFlashMessage('Error updating page', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $id);
        }
    }

    /**
     * Display confirmation page before deleting a page
     */
    public function deleteAction()
    {
        // Get page ID from route parameters
        $id = $this->params[0] ?? null;

        if (!$id || !is_numeric($id)) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Check if page has children
        $children = $this->pageModel->getChildPages($id);

        // Render the view using the controller's render method
        $this->render('admin/pages/delete', [
            'page' => $page,
            'children' => $children,
            'formAction' => $this->settings['ADMIN_URL'] . '/pages/destroy/' . $id
        ]);
    }

    /**
     * Delete a page
     */
    public function destroyAction()
    {
        // Get page ID from route parameters
        $id = $this->params[0] ?? null;

        if (!$id || !is_numeric($id)) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Check if form was submitted
        if (!RequestHelper::isPost()) {
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page deletion from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get existing page to check if it exists
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Delete the page
        $success = $this->pageModel->delete($id);

        if ($success) {
            SessionHelper::setFlashMessage('Page deleted successfully', 'success');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
        } else {
            SessionHelper::setFlashMessage('Error deleting page', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
        }
    }

    /**
     * Change page status (published, draft, trash)
     */
    public function statusAction()
    {
        // Require POST method
        if (!RequestHelper::isPost()) {
            SessionHelper::setFlashMessage('Invalid request method', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page status change from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page ID and status from POST data
        $id = RequestHelper::post('id', 0, 'int');
        $status = RequestHelper::post('status', '');

        // Validate status value
        $allowedStatuses = ['published', 'draft', 'trash'];
        if (!in_array($status, $allowedStatuses)) {
            SessionHelper::setFlashMessage('Invalid status value', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        if (!$id) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get existing page to check if it exists
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Update status
        $success = $this->pageModel->changeStatus($id, $status);

        if ($success) {
            SessionHelper::setFlashMessage('Page status updated to ' . $status, 'success');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
        } else {
            SessionHelper::setFlashMessage('Error updating page status', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
        }
    }

    /**
     * Preview a page before publishing
     */
    public function previewAction()
    {
        // Get page ID from route parameters
        $id = $this->params[0] ?? null;

        if (!$id || !is_numeric($id)) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Render the preview view using the controller's render method
        $this->render('admin/pages/preview', [
            'page' => $page
        ]);
    }

    /**
     * Show revision history for a page
     */
    public function revisionsAction()
    {
        // Get page ID from route parameters
        $id = $this->params[0] ?? null;

        if (!$id || !is_numeric($id)) {
            SessionHelper::setFlashMessage('Invalid page ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($id);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get current page for pagination
        $currentPage = (int)$this->getParam('page', 1);
        $currentPage = max(1, $currentPage);
        $perPage = 10;
        $offset = ($currentPage - 1) * $perPage;

        // Get revisions
        $revisions = $this->pageModel->getRevisions($id, $perPage, $offset);
        $totalRevisions = $this->pageModel->countRevisions($id);

        // Calculate pagination
        $totalPages = ceil($totalRevisions / $perPage);

        // Set success/error messages from session
        $message = '';
        $messageType = '';

        if (SessionHelper::hasValue('page_message')) {
            $message = SessionHelper::getValue('page_message');
            $messageType = SessionHelper::getValue('page_message_type') ?? 'success';
            SessionHelper::removeValue('page_message');
            SessionHelper::removeValue('page_message_type');
        }

        // Render the view using the controller's render method
        $this->render('admin/pages/revisions', [
            'page' => $page,
            'revisions' => $revisions,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'message' => $message,
            'messageType' => $messageType,
            'admin_url' => $this->settings['ADMIN_URL'],
            'site_url' => $this->settings['SITE_URL'],
            'csrf_token' => SessionHelper::getValue('csrf_token')
        ]);
    }

    /**
     * View a specific revision
     */
    public function viewRevisionAction()
    {
        // Get page ID and revision ID from route parameters
        $pageId = $this->params[0] ?? null;
        $revisionId = $this->params[1] ?? null;

        if (!$pageId || !is_numeric($pageId) || !$revisionId || !is_numeric($revisionId)) {
            SessionHelper::setFlashMessage('Invalid request', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($pageId);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get revision data
        $revision = $this->pageModel->getRevision($revisionId);

        if (!$revision || $revision['page_id'] != $pageId) {
            SessionHelper::setFlashMessage('Revision not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Render the view using the controller's render method
        $this->render('admin/pages/view_revision', [
            'page' => $page,
            'revision' => $revision,
            'admin_url' => $this->settings['ADMIN_URL'],
            'site_url' => $this->settings['SITE_URL'],
            'csrf_token' => SessionHelper::getValue('csrf_token')
        ]);
    }

    /**
     * Compare two revisions
     */
    public function compareRevisionsAction()
    {
        // Get page ID and revision IDs from route parameters
        $pageId = $this->params[0] ?? null;
        $oldRevisionId = $this->params[1] ?? null;
        $newRevisionId = $this->params[2] ?? null;

        if (
            !$pageId || !is_numeric($pageId) || !$oldRevisionId || !is_numeric($oldRevisionId) ||
            !$newRevisionId || !is_numeric($newRevisionId)
        ) {
            SessionHelper::setFlashMessage('Invalid request', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($pageId);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get revision data
        $oldRevision = $this->pageModel->getRevision($oldRevisionId);
        $newRevision = $this->pageModel->getRevision($newRevisionId);

        if (!$oldRevision || $oldRevision['page_id'] != $pageId || !$newRevision || $newRevision['page_id'] != $pageId) {
            SessionHelper::setFlashMessage('One or both revisions not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Render the view using the controller's render method
        $this->render('admin/pages/compare_revisions', [
            'page' => $page,
            'oldRevision' => $oldRevision,
            'newRevision' => $newRevision,
            'admin_url' => $this->settings['ADMIN_URL'],
            'site_url' => $this->settings['SITE_URL'],
            'csrf_token' => SessionHelper::getValue('csrf_token')
        ]);
    }

    /**
     * Restore a page to a previous revision
     */
    public function restoreRevisionAction()
    {
        // Get page ID and revision ID from route parameters
        $pageId = $this->params[0] ?? null;
        $revisionId = $this->params[1] ?? null;

        if (!$pageId || !is_numeric($pageId) || !$revisionId || !is_numeric($revisionId)) {
            SessionHelper::setFlashMessage('Invalid request', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Check if form was submitted
        if (!RequestHelper::isPost()) {
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page revision restore from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($pageId);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Get revision data
        $revision = $this->pageModel->getRevision($revisionId);

        if (!$revision || $revision['page_id'] != $pageId) {
            SessionHelper::setFlashMessage('Revision not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Restore the revision
        $success = $this->pageModel->restoreRevision($pageId, $revisionId);

        if ($success) {
            SessionHelper::setFlashMessage('Page restored to revision #' . $revisionId, 'success');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/edit/' . $pageId);
        } else {
            SessionHelper::setFlashMessage('Error restoring revision', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
        }
    }

    /**
     * Delete a specific revision
     */
    public function deleteRevisionAction()
    {
        // Get page ID and revision ID from route parameters
        $pageId = $this->params[0] ?? null;
        $revisionId = $this->params[1] ?? null;

        if (!$pageId || !is_numeric($pageId) || !$revisionId || !is_numeric($revisionId)) {
            SessionHelper::setFlashMessage('Invalid request', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Check if form was submitted
        if (!RequestHelper::isPost()) {
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Validate CSRF token
        if (!CSRFHelper::validateRequest()) {
            SessionHelper::setFlashMessage('Invalid CSRF token', 'error');
            LogHelper::warning('CSRF validation failed for page revision deletion from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
            return;
        }

        // Get page data
        $page = $this->pageModel->getById($pageId);

        if (!$page) {
            SessionHelper::setFlashMessage('Page not found', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages');
            return;
        }

        // Delete the revision
        $success = $this->pageModel->deleteRevision($revisionId);

        if ($success) {
            SessionHelper::setFlashMessage('Revision deleted successfully', 'success');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
        } else {
            SessionHelper::setFlashMessage('Error deleting revision', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/pages/revisions/' . $pageId);
        }
    }

    /**
     * Sanitize a slug string
     *
     * @param string $slug The slug to sanitize
     * @return string The sanitized slug
     */
    private function sanitizeSlug($slug)
    {
        // Convert to lowercase and replace spaces/special chars with hyphens
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $slug), '-'));
    }
}

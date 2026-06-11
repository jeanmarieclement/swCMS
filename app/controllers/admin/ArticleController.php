<?php

namespace App\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Models\Post;
use App\Helpers\PaginationHelper;
use App\Models\Category;
use App\Helpers\SessionHelper;
use App\Helpers\TinyMCEHelper;
use App\Helpers\SecurityHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\middlewares\AuthMiddleware;

/**
 * Article Controller
 * Handles article management in the admin area
 */
class ArticleController extends AdminController
{
    protected $postModel;
    protected $categoryModel;

    /**
     * ArticleController constructor.
     * Checks if the user is logged in and loads models.
     *
     * @param array $params Optional parameters for the controller
     * @return void
     */
    public function __construct($params = [])
    {
        parent::__construct($params);

        // Require authentication for all article management pages
        // For basic article access, at least author role is required
        AuthMiddleware::requireAuthor();

        // Load models
        $this->postModel = new Post();

        $this->categoryModel = new Category();
    }

    /**
     * Displays the list of articles in the admin area.
     *
     * @return void
     */
    public function indexAction()
    {
        // Get status filter
        $status = $this->getParam('status', 'all');

        // Get pagination parameters
        $page = (int)$this->getParam('page', 1);
        $limit = 20;

        // Get total count for pagination
        $totalArticles = $this->postModel->countAll($status);

        // Use PaginationHelper to generate pagination data
        $pagination = PaginationHelper::paginate($totalArticles, $page, $limit, $status);

        // Get articles
        $articles = $this->postModel->getAllForAdmin($status, $pagination['per_page'], $pagination['offset']);

        // Render the view using the controller's render method
        $this->render('admin/articles/index', [
            'title' => 'Manage Articles',
            'page_name' => 'articles',
            'articles' => $articles,
            'status' => $status,
            'pagination' => $pagination,
            'site_url' => $this->settings['SITE_URL']
        ]);
    }

    /**
     * Displays the edit form for an article, or loads data for editing.
     * Handles both GET (display) and POST (submit) requests.
     *
     * @return void
     */
    public function editAction()
    {
        $id = null;

        // First check if we have an ID in the route parameters
        if (isset($this->params[0]) && is_numeric($this->params[0])) {
            $id = $this->params[0];
        } elseif (SessionHelper::hasValue('edit_article_id')) {
            // If not, check if we have an ID in the session (from storeAction)
            $id = SessionHelper::getValue('edit_article_id');
            // Clear the session variable after using it
            SessionHelper::removeValue('edit_article_id');
        }

        if (!$id) {
            SessionHelper::setFlashMessage('Invalid article ID', 'error');
            RedirectHelper::redirect($this->settings['ADMIN_URL'] . '/articles');
            return;
        }
        $article = null;
        $categories = $this->categoryModel->getAll();
        $articleCategories = [];
        $tags = '';

        // Handle form submission
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                LogHelper::warning('CSRF validation failed for article edit from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
                RedirectHelper::redirect('/admin/articles/edit/' . $id);
                return;
            }

            $postCategories = RequestHelper::post('categories');
            $postTags = RequestHelper::post('tags');
            $this->saveData($id, $postCategories, $postTags);
        }


        // If ID is provided, load the article
        if ($id) {
            $article = $this->postModel->getById($id);

            // If article not found, redirect to article list
            if (!$article) {
                SessionHelper::setFlashMessage('Article not found', 'error');
                RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
                exit;
            }

            // Get article categories and tags
            $articleCategories = $this->postModel->getCategories($id);
            $articleCategoryIds = array_column($articleCategories, 'id');

            $tags = $this->postModel->getTags($id);
        }

        // Include TinyMCE helper

        $tinymce_include = TinyMCEHelper::includeTinyMCE();

        // Pre-render the TinyMCE editor HTML
        $content = $article ? $article['content'] : '';
        $editor_html = TinyMCEHelper::editor('content', $content, 'content', 15);

        // Render the edit form using the controller's render method
        $this->render('admin/articles/edit', [
            'title' => $id ? 'Edit Article' : 'New Article',
            'page_name' => 'articles',
            'article' => $article,
            'categories' => $categories,
            'article_categories' => $articleCategoryIds ?? [],
            'tags' => $tags,
            'saved' => (bool)$this->getParam('saved', false),
            'tinymce_include' => $tinymce_include,
            'editor_html' => $editor_html,
            'site_url' => $this->settings['SITE_URL']
        ]);
    }


    /**
     * Saves article data (create or update), categories and tags.
     *
     * @param int|null $id Article ID (null for new article)
     * @param array|null $categories List of category IDs
     * @param array|null $tags List of tag names
     * @return void
     */
    private function saveData($id = null, $categories = null, $tags = [])
    {
        // Collect form data
        $data = [
            'title' => RequestHelper::post('title', ''),
            'slug' => RequestHelper::post('slug', ''),
            'content' => RequestHelper::post('content', '', 'raw'),
            'excerpt' => RequestHelper::post('excerpt', ''),
            'status' => RequestHelper::post('status', 'draft'),
            'featured_image' => RequestHelper::post('featured_image'),
            'comment_status' => RequestHelper::post('comment_status', 'open'),
            'author_id' => SessionHelper::getValue('user_id', 1) // Default to admin if not set
        ];

        // Validate required fields
        if (empty($data['title'])) {
            $this->render('admin/articles/edit', [
                'title' => $id ? 'Edit Article' : 'New Article',
                'page_name' => 'articles',
                'article' => $data,
                'categories' => $categories,
                'article_categories' => $articleCategoryIds ?? [],
                'tags' => $tags,
                'error' => 'Title is required',
                'site_url' => $this->settings['SITE_URL']
            ]);
            return;
        }

        // Create or update the article
        if ($id) {
            $success = $this->postModel->updatePost($id, $data);
            $newId = $id;
        } else {
            $newId = $this->postModel->create($data);
            $success = $newId !== false;
        }

        if ($success && $newId) {
            // Save categories
            $categoryIds = isset($categories) ? $categories : [];
            $this->postModel->setCategories($newId, $categoryIds);

            // Save tags
            $this->postModel->setTags($newId, $tags);

            SessionHelper::setFlashMessage('Article saved successfully', 'success');
            SessionHelper::setValue('edit_article_id', $newId);

            // Redirect to avoid form resubmission
            if (!$id) {
                RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles/edit/' . $newId);
            }
        } else {
            SessionHelper::setFlashMessage('Failed to save article', 'error');
            // If save failed, show error
            $this->render($id ? 'admin/articles/edit' : 'admin/articles/create', [
                'title' => $id ? 'Edit Article' : 'New Article',
                'page_name' => 'articles',
                'article' => $data,
                'categories' => $categories,
                'article_categories' => $articleCategoryIds ?? [],
                'tags' => $tags,
                'error' => 'Failed to save article',
                'site_url' => $this->settings['SITE_URL']
            ]);
            return false;
        }
    }

    /**
     * Displays the form to create a new article and handles submission.
     *
     * @return void
     */
    public function createAction()
    {

        // Handle form submission
        if (RequestHelper::isPost()) {
            // Validate CSRF token
            if (!CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                LogHelper::warning('CSRF validation failed for article creation from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown'));
                RedirectHelper::redirect('/admin/articles/create');
                return;
            }

            $postCategories = RequestHelper::post('categories');
            $postTags = RequestHelper::post('tags');

            $this->saveData(0, $postCategories, $postTags);
        }

        // Carica categorie e dati necessari
        $categories = $this->categoryModel->getAll();
        $tags = [];



        $tinymce_include = TinyMCEHelper::includeTinyMCE();

        // Dati vuoti per nuovo articolo
        $article = [
            'title' => '',
            'slug' => '',
            'content' => '',
            'excerpt' => '',
            'status' => 'draft',
            'featured_image' => null,
            'comment_status' => 'open',
            'author_id' => SessionHelper::hasValue('user_id') ? SessionHelper::getValue('user_id') : 1
        ];

        $this->render('admin/articles/create', [
            'title' => 'New Article',
            'page_name' => 'articles',
            'article' => $article,
            'categories' => $categories,
            'article_categories' => [],
            'tags' => $tags,
            'tinymce_include' => $tinymce_include,
            'site_url' => $this->settings['SITE_URL']
        ]);
    }

    /**
     * Deletes an article by ID.
     *
     * @return void
     */
    public function deleteAction()
    {
        // Get the article ID from the URL
        $articleId = (int)$this->getParam('id', 0);

        // can be in format /admin/articles/delete/19
        if (!$articleId && isset($this->params[0]) && is_numeric($this->params[0])) {
            $articleId = $this->params[0];
        }

        // Check if ID is provided
        if (!$articleId) {
            SessionHelper::setFlashMessage('Article ID is required', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
            exit;
        }

        // Check if article exists
        $article = $this->postModel->getById($articleId);
        if (!$article) {
            SessionHelper::setFlashMessage('Article not found', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
            exit;
        }

        // Delete the article
        $success = $this->postModel->delete($articleId);

        // Redirect with status
        if ($success) {
            SessionHelper::setFlashMessage('Article deleted successfully', 'success');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
        } else {
            SessionHelper::setFlashMessage('Failed to delete article', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
        }
        exit;
    }

    /**
     * Changes the status of an article (published, draft, trash).
     *
     * @return void
     */
    public function statusAction()
    {
        // Get the article ID and new status from the URL
        $articleId = (int)$this->getParam('id', isset($this->params[0]) ? (int)$this->params[0] : 0);
        $status = $this->getParam('status', isset($this->params[1]) ? $this->params[1] : '');

        // Check if ID and status are provided
        if (!$articleId || !in_array($status, ['published', 'draft', 'trash'])) {
            SessionHelper::setFlashMessage('Invalid parameters', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
            exit;
        }

        // Check if article exists
        $article = $this->postModel->getById($articleId);
        if (!$article) {
            SessionHelper::setFlashMessage('Article not found', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
            exit;
        }

        // Change the status
        $success = $this->postModel->changeStatus($articleId, $status);

        // Redirect with status
        if ($success) {
            SessionHelper::setFlashMessage('Article status changed successfully', 'success');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
        } else {
            SessionHelper::setFlashMessage('Failed to change article status', 'error');
            RedirectHelper::redirect($this->settings['SITE_URL'] . '/admin/articles');
        }
        exit;
    }
}

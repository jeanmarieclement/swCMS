<?php

namespace App\Controllers\Frontend;

use App\Controllers\Frontend\BaseController;
use App\Models\Page;
use App\Models\Comments;
use App\Helpers\SessionHelper;
/**
 * PageController manages the display of public static pages
 */
class PageController extends BaseController {
    protected $pageModel;
    protected $commentModel;
    
    /**
     * PageController constructor.
     *
     * @param array $params
     * @return void
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->pageModel = new Page;
        $this->commentModel = new Comments;
    }

    /**
     * Visualizza una pagina statica tramite slug
     *
     * @return void
     */
    public function showAction() {
        // Ottieni lo slug dal parametro catturato dalla route
        $slug = $this->params[0] ?? null;
        
        if (!$slug) {
            http_response_code(404);
            $this->render('404', ['error' => 'Page slug not provided']);
            return;
        }
        
        // Carica la pagina dal model tramite slug
        $page = $this->pageModel->getBySlug($slug);
        
        if (!$page || $page['status'] !== 'published') {
            http_response_code(404);
            $this->render('404', ['error' => 'Page not found']);
            return;
        }
        
        // Check if comments are enabled for this page
        $commentsEnabled = $this->commentModel->areCommentsEnabledForPage($page['id']);
        
        // Load comments if enabled
        $comments = [];
        $totalComments = 0;
        $currentPage = 1;
        $totalPages = 1;
        
        if ($commentsEnabled) {
            $currentPage = filter_input(INPUT_GET, 'comment_page', FILTER_VALIDATE_INT) ?: 1;
            $limit = 10;
            $offset = ($currentPage - 1) * $limit;
            
            // Use hierarchical comments for better reply structure
            $comments = $this->commentModel->getApprovedHierarchicalForPost($page['id'], $limit, $offset);
            $totalComments = $this->commentModel->countApprovedForPost($page['id']);
            $totalPages = ceil($totalComments / $limit);
        }
        
        // Get current user info for comments
        $userId = SessionHelper::getValue('user_id');
        $userDisplayName = SessionHelper::getValue('display_name') ?? SessionHelper::getValue('username');
        
        $this->render('page', [
            'page' => $page,
            'page_title' => $page['title'],
            'meta_description' => substr(strip_tags($page['content']), 0, 150),
            'comments_enabled' => $commentsEnabled,
            'comments' => $comments,
            'total_comments' => $totalComments,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'user_id' => $userId,
            'user_display_name' => $userDisplayName
        ]);
    }
    
    /**
     * Visualizza una pagina statica (backward compatibility)
     *
     * @param string $slug
     * @return void
     */
    public function viewAction($slug) {
        // Mantieni compatibilità con vecchie route
        $this->showAction();
    }
}

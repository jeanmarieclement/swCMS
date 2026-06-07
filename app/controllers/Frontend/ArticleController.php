<?php

namespace App\Controllers\Frontend;

use App\Controllers\Frontend\BaseController;
use App\Models\Post;
use App\Models\Comments;
use App\Helpers\SessionHelper;
/**
 * ArticleController gestisce la visualizzazione degli articoli pubblici
 */
class ArticleController extends BaseController {
    protected $articleModel;
    protected $commentModel;
    
    /**
     * ArticleController constructor.
     *
     * @param array $params Optional parameters
     * @return void
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->articleModel = new Post;
        $this->commentModel = new Comments;
    }
    /**
     * Visualizza un articolo tramite slug
     *
     * @return void
     */
    public function showAction() {
        // Ottieni lo slug dal parametro catturato dalla route
        $slug = $this->params[0] ?? null;
        
        if (!$slug) {
            http_response_code(404);
            $this->render('404', ['error' => 'Article slug not provided']);
            return;
        }
        
        // Carica l'articolo dal model tramite slug
        $article = $this->articleModel->getBySlug($slug, 'published');
        
        if (!$article) {
            http_response_code(404);
            $this->render('404', ['error' => 'Article not found']);
            return;
        }
        
        // Carica categorie e tag dell'articolo
        $article['categories'] = $this->articleModel->getCategories($article['id']);
        $article['tags'] = $this->articleModel->getTags($article['id']);
        
        // Check if comments are enabled for this article
        $commentsEnabled = $this->commentModel->areCommentsEnabledForPost($article['id']);
        
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
            $comments = $this->commentModel->getApprovedHierarchicalForPost($article['id'], $limit, $offset);
            $totalComments = $this->commentModel->countApprovedForPost($article['id']);
            $totalPages = ceil($totalComments / $limit);
        }
        
        // Get current user info for comments
        $userId = SessionHelper::getValue('user_id');
        $userDisplayName = SessionHelper::getValue('display_name') ?? SessionHelper::getValue('username');
        
        $this->render('article', [
            'article' => $article,
            'post' => $article, // alias for comment form
            'page_title' => $article['title'],
            'meta_description' => substr(strip_tags($article['content']), 0, 150),
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
     * Visualizza un articolo (backward compatibility)
     *
     * @param int $id ID dell'articolo
     * @return void
     */
    public function viewAction($id) {
        // Mantieni compatibilità con vecchie route basate su ID
        $article = $this->articleModel->getById($id);
        
        if (!$article || $article['status'] !== 'published') {
            http_response_code(404);
            $this->render('404', ['error' => 'Article not found']);
            return;
        }
        
        // Redirect alla route con slug per SEO
        $this->redirect('/article/' . $article['slug']);
    }
}

<?php

namespace App\Controllers\Frontend;

use App\Controllers\Frontend\BaseController;
use App\Models\Comments;
use App\Helpers\SessionHelper;
use App\Helpers\AuthHelper;
use App\Helpers\RedirectHelper;

class CommentController extends BaseController
{
    private $commentModel;

    public function __construct()
    {
        parent::__construct();
        $this->commentModel = new Comments();
    }

    /**
     * Handle comment submission
     */
    public function storeAction()
    {
        try {
            // Check if POST request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                SessionHelper::setFlashMessage('Invalid request method', 'error');
                RedirectHelper::redirect('/');
                return;
            }

            // Validate CSRF token
            if (!\App\Helpers\CSRFHelper::validateRequest()) {
                SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
                RedirectHelper::redirect('/');
                return;
            }

            // Get and validate input data
            $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
            $pageId = filter_input(INPUT_POST, 'page_id', FILTER_VALIDATE_INT);
            $parentId = filter_input(INPUT_POST, 'parent_id', FILTER_VALIDATE_INT); // For replies
            $content = trim(htmlspecialchars(filter_input(INPUT_POST, 'content', FILTER_UNSAFE_RAW) ?? '', ENT_QUOTES, 'UTF-8'));
            $authorName = trim(htmlspecialchars(filter_input(INPUT_POST, 'author_name', FILTER_UNSAFE_RAW) ?? '', ENT_QUOTES, 'UTF-8'));
            $authorEmail = trim(filter_input(INPUT_POST, 'author_email', FILTER_SANITIZE_EMAIL) ?? '');

            // Validate required fields
            if (empty($content)) {
                SessionHelper::setFlashMessage('Il contenuto del commento è obbligatorio', 'error');
                RedirectHelper::redirectLocal($this->getParam('redirect_url', '/', 'POST'));
                return;
            }

            if (!$postId && !$pageId) {
                SessionHelper::setFlashMessage('ID post o pagina richiesto', 'error');
                RedirectHelper::redirectLocal($this->getParam('redirect_url', '/', 'POST'));
                return;
            }

            // Check if comments are enabled for this content
            $commentsEnabled = false;
            if ($postId) {
                $commentsEnabled = $this->commentModel->areCommentsEnabledForPost($postId);
            } elseif ($pageId) {
                $commentsEnabled = $this->commentModel->areCommentsEnabledForPage($pageId);
            }

            if (!$commentsEnabled) {
                SessionHelper::setFlashMessage('I commenti sono disabilitati per questo contenuto', 'error');
                RedirectHelper::redirectLocal($this->getParam('redirect_url', '/', 'POST'));
                return;
            }

            // Check if user is logged in
            $userId = SessionHelper::getValue('user_id');

            if (!$userId) {
                // For non-logged users, validate name and email
                if (empty($authorName)) {
                    SessionHelper::setFlashMessage('Il nome è obbligatorio', 'error');
                    RedirectHelper::redirectLocal($this->getParam('redirect_url', '/', 'POST'));
                    return;
                }

                if (empty($authorEmail) || !filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
                    SessionHelper::setFlashMessage('Un indirizzo email valido è obbligatorio', 'error');
                    RedirectHelper::redirectLocal($this->getParam('redirect_url', '/', 'POST'));
                    return;
                }
            }

            // Prepare comment data
            $commentData = [
                'content' => $content,
                'status' => 'pending', // Comments are pending by default for moderation
                'author_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Set parent_id if this is a reply
            if ($parentId) {
                $commentData['parent_id'] = $parentId;
            }

            // Set post or page ID
            if ($postId) {
                $commentData['post_id'] = $postId;
            } else {
                $commentData['post_id'] = $pageId; // In this implementation, we use post_id for both posts and pages
            }

            // Set author information
            if ($userId) {
                $commentData['user_id'] = $userId;
                // Get user info from session for logged users
                $userDisplayName = AuthHelper::getCurrentUserDisplayName() ?? 'Utente registrato';
                $userEmail = AuthHelper::getCurrentUserEmail();

                $commentData['author_name'] = $userDisplayName;
                if ($userEmail) {
                    $commentData['author_email'] = $userEmail;
                }
            } else {
                $commentData['author_name'] = $authorName;
                $commentData['author_email'] = $authorEmail;
            }

            // Create the comment (or reply)
            if ($parentId) {
                // This is a reply - use createReply method
                $commentId = $this->commentModel->createReply($commentData);
                if ($commentId) {
                    SessionHelper::setFlashMessage('Risposta inviata con successo! Sarà visibile dopo la moderazione.', 'success');
                } else {
                    SessionHelper::setFlashMessage('Errore durante l\'invio della risposta. Riprova.', 'error');
                }
            } else {
                // This is a top-level comment
                $commentId = $this->commentModel->createComment($commentData);
                if ($commentId) {
                    SessionHelper::setFlashMessage('Commento inviato con successo! Sarà visibile dopo la moderazione.', 'success');
                } else {
                    SessionHelper::setFlashMessage('Errore durante l\'invio del commento. Riprova.', 'error');
                }
            }

            RedirectHelper::redirectLocal($_POST['redirect_url'] ?? '/');
        } catch (\Exception $e) {
            error_log('Error creating comment: ' . $e->getMessage());
            SessionHelper::setFlashMessage('Si è verificato un errore. Riprova più tardi.', 'error');
            RedirectHelper::redirectLocal($_POST['redirect_url'] ?? '/');
        }
    }

    /**
     * Get comments for a specific post/page (AJAX endpoint)
     */
    public function getCommentsAction()
    {
        header('Content-Type: application/json');

        try {
            $postId = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
            $pageId = filter_input(INPUT_GET, 'page_id', FILTER_VALIDATE_INT);
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
            $limit = 10;
            $offset = ($page - 1) * $limit;

            if (!$postId && !$pageId) {
                echo json_encode(['error' => 'Post ID or Page ID required']);
                return;
            }

            $contentId = $postId ?: $pageId;

            // Check if comments are enabled
            $commentsEnabled = false;
            if ($postId) {
                $commentsEnabled = $this->commentModel->areCommentsEnabledForPost($postId);
            } else {
                $commentsEnabled = $this->commentModel->areCommentsEnabledForPage($pageId);
            }

            if (!$commentsEnabled) {
                echo json_encode(['error' => 'Comments are disabled']);
                return;
            }

            // Get approved comments
            $comments = $this->commentModel->getApprovedForPost($contentId, $limit, $offset);
            $totalComments = $this->commentModel->countApprovedForPost($contentId);

            echo json_encode([
                'comments' => $comments,
                'total' => $totalComments,
                'current_page' => $page,
                'total_pages' => ceil($totalComments / $limit)
            ]);
        } catch (\Exception $e) {
            error_log('Error getting comments: ' . $e->getMessage());
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}

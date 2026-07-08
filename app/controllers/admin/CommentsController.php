<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Models\Comments;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\SessionHelper;
use App\Helpers\LogHelper;

class CommentsController extends AdminController
{
    public function indexAction()
    {
        $commentsModel = new Comments();
        $status = $this->getParam('status');
        $hierarchical = $this->getParam('hierarchical', 'true'); // Default to hierarchical view

        if ($hierarchical === 'true') {
            $comments = $commentsModel->getAllHierarchical($status);
        } else {
            $comments = $commentsModel->getAll($status);
        }

        $this->render('admin/comments/index', [
            'comments' => $comments,
            'status' => $status,
            'hierarchical' => $hierarchical,
        ]);
    }

    public function approveAction()
    {
        $this->moderate('approved', 'comment approval');
    }

    public function spamAction()
    {
        $this->moderate('spam', 'comment spam flag');
    }

    public function deleteAction()
    {
        if (!RequestHelper::isPost()) {
            $this->redirect('/admin/comments');
            return;
        }
        $this->requireCsrf('/admin/comments', 'comment deletion');

        $id = RequestHelper::post('id', 0, 'int');
        if ($id) {
            $commentsModel = new Comments();
            $commentsModel->delete($id);
        }
        $this->redirect('/admin/comments');
    }

    /**
     * Shared moderation handler (approve / spam)
     */
    private function moderate(string $status, string $context): void
    {
        if (!RequestHelper::isPost()) {
            $this->redirect('/admin/comments');
            return;
        }
        $this->requireCsrf('/admin/comments', $context);

        $id = RequestHelper::post('id', 0, 'int');
        if ($id) {
            $commentsModel = new Comments();
            $commentsModel->updateStatus($id, $status);
        }
        $this->redirect('/admin/comments');
    }

    public function replyAction()
    {
        $parentId = $this->getParam('id');
        $commentsModel = new Comments();

        // Initialize form data for display
        $formData = [];

        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/comments/reply?id=' . $parentId, 'comment reply');

            // Process reply form submission
            $replyData = [
                'parent_id' => $parentId,
                'content' => trim(RequestHelper::post('content', '', 'raw')),
                'author_name' => trim(RequestHelper::post('author_name')),
                'author_email' => trim(RequestHelper::post('author_email', '', 'email')),
                'status' => RequestHelper::post('status', 'approved'), // Admin replies are approved by default
                'user_id' => SessionHelper::getValue('user_id'), // If admin is logged in
            ];

            // Store form data for potential re-display on validation errors
            $formData = $replyData;

            // Validation
            if (empty($replyData['content'])) {
                $this->setFlashMessage('error', 'Il contenuto della risposta è obbligatorio.');
            } elseif (empty($replyData['author_name'])) {
                $this->setFlashMessage('error', 'Il nome dell\'autore è obbligatorio.');
            } else {
                $replyId = $commentsModel->createReply($replyData);

                if ($replyId) {
                    $this->setFlashMessage('success', 'Risposta aggiunta con successo.');
                    $this->redirect('/admin/comments');
                    return;
                } else {
                    $this->setFlashMessage('error', 'Errore durante l\'aggiunta della risposta.');
                }
            }
        }

        // Get parent comment for display (with user data)
        $parentComment = null;
        if ($parentId) {
            $parentComment = $commentsModel->getByIdWithUserData($parentId);
        }

        if (!$parentComment) {
            $this->setFlashMessage('error', 'Commento non trovato.');
            $this->redirect('/admin/comments');
            return;
        }

        $this->render('admin/comments/reply', [
            'parent_comment' => $parentComment,
            'form_data' => $formData,
        ]);
    }
}

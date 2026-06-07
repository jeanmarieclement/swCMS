<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Controllers\Frontend\CommentController;
use App\Helpers\SessionHelper;

class CsrfCommentProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Initialize session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommentSubmissionRequiresCsrfToken()
    {
        // Set up POST request without CSRF token
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'post_id' => 1,
            'content' => 'Test comment',
            'author_name' => 'Test User',
            'author_email' => 'test@example.com'
        ];

        $controller = new CommentController();

        // Capture redirect
        ob_start();
        $controller->storeAction();
        ob_end_clean();

        // Should have error message about CSRF
        $flash = SessionHelper::getFlashMessage();
        $this->assertStringContainsString('CSRF', $flash['message'] ?? '');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommentSubmissionWithInvalidCsrfToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'invalid_token',
            'post_id' => 1,
            'content' => 'Test comment',
            'author_name' => 'Test User',
            'author_email' => 'test@example.com'
        ];

        // Set valid token in session
        SessionHelper::setValue('csrf_token', 'valid_token_12345');

        $controller = new CommentController();

        ob_start();
        $controller->storeAction();
        ob_end_clean();

        $flash = SessionHelper::getFlashMessage();
        $this->assertStringContainsString('CSRF', $flash['message'] ?? '');
    }
}

<?php

namespace Tests\Integration;

use App\Controllers\InstallController;
use App\Core\View;
use App\Helpers\SystemSettingsHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Smarty\Smarty;

class FrontendCommentsViewTest extends TestCase
{
    private $smarty;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', dirname(__DIR__, 2));
        }
        if (!defined('APP_PATH')) {
            define('APP_PATH', ROOT_PATH . '/app');
        }
        if (!defined('PUBLIC_PATH')) {
            define('PUBLIC_PATH', ROOT_PATH . '/public');
        }
        if (!defined('VIEWS_PATH')) {
            define('VIEWS_PATH', ROOT_PATH . '/app/views');
        }

        $compileDir = sys_get_temp_dir() . '/swcms_test_compiled_' . md5(__DIR__);
        if (!is_dir($compileDir)) {
            mkdir($compileDir, 0777, true);
        }

        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(PUBLIC_PATH . '/themes/default/templates');
        $this->smarty->setCompileDir($compileDir);
        $this->smarty->setCacheDir(sys_get_temp_dir());
        $this->smarty->caching = Smarty::CACHING_OFF;
    }

    public function testCommentsEnabledDefaultSetting(): void
    {
        $this->assertSame('1', SystemSettingsHelper::get('COMMENTS_ENABLED'));

        $installer = new ReflectionClass(InstallController::class);
        $method = $installer->getMethod('insertDefaultSettings');
        $method->setAccessible(true);

        // Verify the code of InstallController defines COMMENTS_ENABLED in insertDefaultSettings
        $filename = $installer->getFileName();
        $contents = file_get_contents($filename);
        $this->assertStringContainsString("'COMMENTS_ENABLED' => ['1', 'Enable or disable comments globally']", $contents);
    }

    public function testArticleViewRendersCommentsFormAndListWhenEnabled(): void
    {
        $data = [
            'article' => [
                'id' => 101,
                'title' => 'Article With Comments',
                'content' => '<p>Article body</p>',
                'published_at' => '2026-09-07 12:00:00',
            ],
            'post' => [
                'id' => 101,
            ],
            'comments_enabled' => true,
            'comments' => [
                [
                    'id' => 1,
                    'post_id' => 101,
                    'page_id' => null,
                    'parent_id' => null,
                    'author_name' => 'Mario Rossi',
                    'user_display_name' => null,
                    'content' => 'Ottimo articolo informativo!',
                    'created_at' => '2026-09-07 14:00:00',
                    'replies' => []
                ]
            ],
            'total_comments' => 1,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'dummy_csrf_token_abc123'
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('article.tpl');

        // Check comments container exists
        $this->assertStringContainsString('id="comments"', $html);
        $this->assertStringContainsString('class="comments-section', $html);
        $this->assertStringContainsString('Commenti (1)', $html);
        $this->assertStringContainsString('Ottimo articolo informativo!', $html);
        $this->assertStringContainsString('Mario Rossi', $html);

        // Check form elements
        $this->assertStringContainsString('id="comment-form"', $html);
        $this->assertStringContainsString('action="/comments/store"', $html);
        $this->assertStringContainsString('name="csrf_token" value="dummy_csrf_token_abc123"', $html);
        $this->assertStringContainsString('name="post_id" value="101"', $html);
        $this->assertStringContainsString('name="page_id" value=""', $html);
        $this->assertStringContainsString('name="parent_id"', $html);
        $this->assertStringContainsString('name="author_name"', $html);
        $this->assertStringContainsString('name="author_email"', $html);
        $this->assertStringContainsString('name="content"', $html);
        $this->assertStringContainsString('Invia Commento', $html);
    }

    public function testArticleViewCompletelyOmitsCommentsWhenDisabled(): void
    {
        $data = [
            'article' => [
                'id' => 102,
                'title' => 'Article Without Comments',
                'content' => '<p>Article body</p>',
                'published_at' => '2026-09-07 12:00:00',
            ],
            'post' => [
                'id' => 102,
            ],
            'comments_enabled' => false,
            'comments' => [],
            'total_comments' => 0,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'dummy_token'
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('article.tpl');

        $this->assertStringNotContainsString('id="comments"', $html);
        $this->assertStringNotContainsString('comments-section', $html);
        $this->assertStringNotContainsString('comment-form', $html);
        $this->assertStringNotContainsString('Lascia un commento', $html);
        $this->assertStringNotContainsString('/comments/store', $html);
    }

    public function testPageViewRendersCommentsFormAndListWhenEnabled(): void
    {
        $data = [
            'page' => [
                'id' => 201,
                'title' => 'Static Page With Comments',
                'content' => '<p>Page content</p>',
                'published_at' => '2026-09-07 12:00:00',
            ],
            'comments_enabled' => true,
            'comments' => [
                [
                    'id' => 10,
                    'post_id' => null,
                    'page_id' => 201,
                    'parent_id' => null,
                    'author_name' => 'Giulia Bianchi',
                    'user_display_name' => null,
                    'content' => 'Commento specifico per questa pagina statica',
                    'created_at' => '2026-09-07 15:00:00',
                    'replies' => []
                ]
            ],
            'total_comments' => 1,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'dummy_csrf_token_page'
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('page.tpl');

        $this->assertStringContainsString('id="comments"', $html);
        $this->assertStringContainsString('Commenti (1)', $html);
        $this->assertStringContainsString('Commento specifico per questa pagina statica', $html);
        $this->assertStringContainsString('Giulia Bianchi', $html);

        // Check form elements: page_id is populated, post_id is empty
        $this->assertStringContainsString('name="post_id" value=""', $html);
        $this->assertStringContainsString('name="page_id" value="201"', $html);
        $this->assertStringContainsString('action="/comments/store"', $html);
    }

    public function testPageViewCompletelyOmitsCommentsWhenDisabled(): void
    {
        $data = [
            'page' => [
                'id' => 202,
                'title' => 'Static Page Without Comments',
                'content' => '<p>Page content</p>',
                'published_at' => '2026-09-07 12:00:00',
            ],
            'comments_enabled' => false,
            'comments' => [],
            'total_comments' => 0,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'dummy_token'
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('page.tpl');

        $this->assertStringNotContainsString('id="comments"', $html);
        $this->assertStringNotContainsString('comments-section', $html);
        $this->assertStringNotContainsString('comment-form', $html);
        $this->assertStringNotContainsString('Lascia un commento', $html);
    }

    public function testHierarchicalCommentsAndReplyButtons(): void
    {
        $data = [
            'comments_enabled' => true,
            'comments' => [
                [
                    'id' => 1,
                    'post_id' => 10,
                    'parent_id' => null,
                    'author_name' => 'Genitore',
                    'user_display_name' => null,
                    'content' => 'Commento principale di discussione',
                    'created_at' => '2026-09-07 10:00:00',
                    'replies' => [
                        [
                            'id' => 2,
                            'post_id' => 10,
                            'parent_id' => 1,
                            'author_name' => 'Figlio',
                            'user_display_name' => null,
                            'content' => 'Questa è una risposta diretta!',
                            'created_at' => '2026-09-07 10:30:00',
                            'replies' => []
                        ]
                    ]
                ]
            ],
            'total_comments' => 2,
            'current_page' => 1,
            'total_pages' => 1
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('partials/comments_list.tpl');

        $this->assertStringContainsString('Commenti (2)', $html);
        $this->assertStringContainsString('Commento principale di discussione', $html);
        $this->assertStringContainsString('Questa è una risposta diretta!', $html);
        $this->assertStringContainsString('Risposta', $html);
        $this->assertStringContainsString('data-parent-id="1"', $html);
    }

    public function testEmptyStateMessageWhenNoComments(): void
    {
        $data = [
            'comments_enabled' => true,
            'comments' => [],
            'total_comments' => 0,
            'current_page' => 1,
            'total_pages' => 1
        ];

        foreach ($data as $key => $val) {
            $this->smarty->assign($key, $val);
        }

        $html = $this->smarty->fetch('partials/comments_list.tpl');

        $this->assertStringContainsString('Nessun commento ancora. Sii il primo a commentare!', $html);
    }

    public function testCoreViewsArticleAndPageRenderComments(): void
    {
        $compileDir = sys_get_temp_dir() . '/swcms_test_compiled_' . md5(__DIR__);
        $coreSmarty = new Smarty();
        $coreSmarty->setTemplateDir(VIEWS_PATH);
        $coreSmarty->setCompileDir($compileDir);
        $coreSmarty->setCacheDir(sys_get_temp_dir());
        $coreSmarty->caching = Smarty::CACHING_OFF;

        $articleData = [
            'article' => [
                'id' => 301,
                'title' => 'Core Article',
                'content' => 'Core article content',
            ],
            'post' => ['id' => 301],
            'comments_enabled' => true,
            'comments' => [],
            'total_comments' => 0,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'core_csrf'
        ];

        foreach ($articleData as $k => $v) {
            $coreSmarty->assign($k, $v);
        }

        $html = $coreSmarty->fetch('frontend/article.tpl');
        $this->assertStringContainsString('id="comments"', $html);
        $this->assertStringContainsString('name="post_id" value="301"', $html);

        $coreSmarty->clearAllAssign();

        // Page core template
        $pageData = [
            'page' => [
                'id' => 401,
                'title' => 'Core Page',
                'content' => 'Core page content',
            ],
            'comments_enabled' => true,
            'comments' => [],
            'total_comments' => 0,
            'current_page' => 1,
            'total_pages' => 1,
            'user_id' => null,
            'user_display_name' => null,
            'csrf_token' => 'core_csrf'
        ];

        foreach ($pageData as $k => $v) {
            $coreSmarty->assign($k, $v);
        }

        $htmlPage = $coreSmarty->fetch('frontend/page.tpl');
        $this->assertStringContainsString('id="comments"', $htmlPage);
        $this->assertStringContainsString('name="page_id" value="401"', $htmlPage);
        $this->assertStringContainsString('name="post_id" value=""', $htmlPage);
    }

    public function testCommentsTargetIsolationInCommentForm(): void
    {
        // When post_id is provided, post_id is populated and page_id is empty
        $this->smarty->assign('comments_enabled', true);
        $this->smarty->assign('csrf_token', 'test_token');
        $this->smarty->assign('post', ['id' => 777]);
        $this->smarty->assign('page', null);
        $this->smarty->assign('user_id', null);

        $htmlArticle = $this->smarty->fetch('partials/comment_form.tpl');
        $this->assertStringContainsString('name="post_id" value="777"', $htmlArticle);
        $this->assertStringContainsString('name="page_id" value=""', $htmlArticle);

        // When page_id is provided, page_id is populated and post_id is empty
        $this->smarty->assign('post', null);
        $this->smarty->assign('page', ['id' => 888]);

        $htmlPage = $this->smarty->fetch('partials/comment_form.tpl');
        $this->assertStringContainsString('name="post_id" value=""', $htmlPage);
        $this->assertStringContainsString('name="page_id" value="888"', $htmlPage);
    }

    public function testAdminCommentsSettingsKeySynchronization(): void
    {
        // Disabling comments via lowercase key (used by admin settings form)
        SystemSettingsHelper::set('comments_enabled', '0');
        $this->assertSame('0', SystemSettingsHelper::get('COMMENTS_ENABLED'));
        $this->assertSame('0', SystemSettingsHelper::get('comments_enabled'));

        // Enabling comments via uppercase key
        SystemSettingsHelper::set('COMMENTS_ENABLED', '1');
        $this->assertSame('1', SystemSettingsHelper::get('COMMENTS_ENABLED'));
        $this->assertSame('1', SystemSettingsHelper::get('comments_enabled'));
    }

    public function testVersoMarteAuthorMapping(): void
    {
        $marsSmarty = new Smarty();
        $compileDir = sys_get_temp_dir() . '/swcms_test_compiled_' . md5(__DIR__);
        $marsSmarty->setTemplateDir(PUBLIC_PATH . '/themes/verso-marte/templates');
        $marsSmarty->setCompileDir($compileDir);
        $marsSmarty->setCacheDir(sys_get_temp_dir());
        $marsSmarty->caching = Smarty::CACHING_OFF;

        $data = [
            'comments_enabled' => true,
            'comments' => [
                [
                    'id' => 10,
                    'post_id' => 99,
                    'parent_id' => null,
                    'author_name' => 'Comandante Shepard',
                    'user_display_name' => null,
                    'content' => 'Rapporto di bordo inviato.',
                    'created_at' => '2026-09-07 10:00:00',
                    'replies' => []
                ]
            ],
            'total_comments' => 1
        ];

        foreach ($data as $k => $v) {
            $marsSmarty->assign($k, $v);
        }

        $html = $marsSmarty->fetch('partials/comments_list.tpl');
        $this->assertStringContainsString('Comandante Shepard', $html);
        $this->assertStringContainsString('data-parent-id="10"', $html);
        $this->assertStringContainsString('href="#comment-form"', $html);
    }
}



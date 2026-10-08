<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ArticleExcerptTest extends TestCase
{
    public function testControllerStoresExcerptAsPlainText(): void
    {
        $source = file_get_contents(ROOT_PATH . '/app/controllers/admin/ArticleController.php');

        $this->assertStringContainsString(
            "RequestHelper::post('excerpt', '', 'text')",
            $source
        );
    }

    public function testFormEscapesExcerptAndRoundTripsSpecialCharacters(): void
    {
        $smarty = new \Smarty\Smarty();
        $smarty->setTemplateDir(ROOT_PATH . '/app/views');
        $dir = sys_get_temp_dir() . '/swcms-excerpt-' . bin2hex(random_bytes(8));
        mkdir($dir);
        $smarty->setCompileDir($dir);
        $excerpt = 'Limiti dell\'invio & "DAT" </textarea><script>alert(1)</script>';
        $smarty->assign('article', ['excerpt' => $excerpt]);
        $smarty->assign('admin_url', '/admin');
        $smarty->assign('site_url', 'http://localhost');
        $smarty->assign('csrf_token', 'test');

        try {
            $html = $smarty->fetch('admin/articles/partials/_article_form.tpl');
            $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
            $node = (new \DOMXPath($dom))->query('//textarea[@name="excerpt"]')->item(0);
            $this->assertSame($excerpt, $node->textContent);
        } finally {
            foreach (glob($dir . '/*') as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
    }
}

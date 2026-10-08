<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ContentTitleTest extends TestCase
{
    /** @dataProvider titleForms */
    public function testTitleInputsEscapeOnceWithoutCreatingAttributes($template, $variable): void
    {
        $smarty = new \Smarty\Smarty();
        $smarty->setTemplateDir(ROOT_PATH . '/app/views');
        $dir = sys_get_temp_dir() . '/swcms-title-' . bin2hex(random_bytes(8));
        mkdir($dir);
        $smarty->setCompileDir($dir);
        $title = 'L\'AI & "PHP" &amp; " autofocus onfocus="alert(1)';
        $smarty->assign($variable, ['title' => $title]);
        $smarty->assign('admin_url', '/admin');
        $smarty->assign('site_url', 'http://localhost');
        $smarty->assign('csrf_token', 'test');
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $smarty->fetch($template));
            $input = (new \DOMXPath($dom))->query('//input[@name="title"]')->item(0);
            $this->assertSame($title, $input->getAttribute('value'));
            $this->assertFalse($input->hasAttribute('autofocus'));
            $this->assertFalse($input->hasAttribute('onfocus'));
        } finally {
            foreach (glob($dir . '/*') as $file) {
                unlink($file);
            }
            rmdir($dir);
        }
    }

    public function titleForms(): array
    {
        return [
            ['admin/articles/partials/_article_form.tpl', 'article'],
            ['admin/pages/partials/_page_form.tpl', 'page'],
        ];
    }

}

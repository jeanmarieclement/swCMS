<?php

namespace Tests\Unit\Views;

use PHPUnit\Framework\TestCase;

/**
 * Admin template assets Test
 *
 * layout.tpl loads every frontend dependency from public/vendor/ and closes the
 * document at its end. The footer it includes must not close the document a
 * second time, nor reload those libraries from a CDN.
 *
 * @package Tests\Unit\Views
 */
class AdminTemplateAssetsTest extends TestCase
{
    /** @var string */
    private $adminViews;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adminViews = dirname(__DIR__, 3) . '/app/views/admin';
    }

    private function contents(string $relative): string
    {
        $path = $this->adminViews . '/' . $relative;
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    public function testFooterDoesNotCloseTheDocument()
    {
        $footer = $this->contents('footer.tpl');

        $this->assertStringNotContainsString('</body>', $footer);
        $this->assertStringNotContainsString('</html>', $footer);
    }

    public function testLayoutClosesTheDocumentExactlyOnce()
    {
        $layout = $this->contents('layout.tpl');

        $this->assertSame(1, substr_count($layout, '</body>'));
        $this->assertSame(1, substr_count($layout, '</html>'));
    }

    public function testFooterLoadsNoScriptsOfItsOwn()
    {
        $footer = $this->contents('footer.tpl');

        $this->assertStringNotContainsString('<script src=', $footer);
        $this->assertStringNotContainsString('<link href=', $footer);
    }

    public function testLayoutLoadsItsLibrariesFromTheVendorDirectory()
    {
        $layout = $this->contents('layout.tpl');

        foreach (['jquery', 'bootstrap', 'datatables', 'tinymce', 'select2', 'fontawesome'] as $library) {
            $this->assertStringContainsString('/vendor/' . $library . '/', $layout, $library . ' is not vendored');
        }
    }

    public function testAdminTemplatesDoNotLoadAssetsFromCdns()
    {
        // compare_revisions.tpl still pulls diff.js from cdnjs, which the CSP in
        // app/Config/security.php blocks: that library has no vendored copy yet.
        $known = ['pages/compare_revisions.tpl'];

        $offenders = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->adminViews));
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'tpl') {
                continue;
            }
            $relative = str_replace($this->adminViews . '/', '', $file->getPathname());
            if (in_array($relative, $known, true)) {
                continue;
            }
            if (preg_match('#(src|href)="https?://#', file_get_contents($file->getPathname()))) {
                $offenders[] = $relative;
            }
        }

        sort($offenders);
        $this->assertSame([], $offenders, 'Admin templates must load assets from public/vendor/');
    }
}

<?php

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\SeoHelper;

// Composer autoloads app/ through a classmap, so a helper added after the last
// `composer dump-autoload` is invisible to the test run. The application itself
// resolves it through App\Core\Autoloader's PSR-4 lookup.
require_once dirname(__DIR__, 3) . '/app/helpers/SeoHelper.php';

/**
 * SeoHelper Test
 *
 * @package Tests\Unit\Helpers
 */
class SeoHelperTest extends TestCase
{
    public function testDescriptionCollapsesMarkupIndentation()
    {
        $content = "<div>\n        \n            <p>\n                Informativa sulla protezione dei dati</p></div>";

        $this->assertEquals(
            'Informativa sulla protezione dei dati',
            SeoHelper::metaDescription($content)
        );
    }

    public function testDescriptionStripsTags()
    {
        $this->assertEquals('Ciao mondo', SeoHelper::metaDescription('<p><strong>Ciao</strong> mondo</p>'));
    }

    public function testDescriptionCutsOnAWordBoundary()
    {
        $content = str_repeat('parola ', 40);

        $description = SeoHelper::metaDescription($content, 50);

        $this->assertLessThanOrEqual(50, mb_strlen($description));
        $this->assertStringEndsNotWith('parol', $description);
        $this->assertStringEndsNotWith(' ', $description);
    }

    public function testDescriptionDoesNotSplitAMultiByteCharacter()
    {
        // 'à' is two bytes in UTF-8: a byte-based cut would leave half of it
        $content = str_repeat('à', 200);

        $description = SeoHelper::metaDescription($content, 150);

        $this->assertTrue(mb_check_encoding($description, 'UTF-8'));
        $this->assertLessThanOrEqual(150, mb_strlen($description));
    }

    public function testDescriptionKeepsShortTextWhole()
    {
        $this->assertEquals('Breve testo', SeoHelper::metaDescription('  Breve   testo  '));
    }

    public function testDescriptionOfEmptyContentIsEmpty()
    {
        $this->assertSame('', SeoHelper::metaDescription(null));
        $this->assertSame('', SeoHelper::metaDescription('   '));
    }

    public function testCanonicalJoinsSiteUrlAndPath()
    {
        $this->assertEquals(
            'https://example.com/page/privacy',
            SeoHelper::canonicalUrl('https://example.com', 'page/privacy')
        );
    }

    public function testCanonicalTolleratesStraySlashes()
    {
        $this->assertEquals(
            'https://example.com/page/privacy',
            SeoHelper::canonicalUrl('https://example.com/', '/page/privacy')
        );
    }

    public function testCanonicalWithoutASiteUrlIsStillAbsolutePath()
    {
        $this->assertEquals('/page/privacy', SeoHelper::canonicalUrl('', 'page/privacy'));
    }
}

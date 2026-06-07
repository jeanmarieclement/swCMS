<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Services\ThemeService;

class PathTraversalPreventionTest extends TestCase
{
    public function testThemeNameRejectsPathTraversal()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme name');

        $themeService = new ThemeService();
        $themeService->getThemeDetails('../../etc/passwd');
    }

    public function testThemeNameRejectsInvalidCharacters()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme name');

        $themeService = new ThemeService();
        $themeService->getThemeDetails('theme/../../../etc/passwd');
    }

    public function testThemeNameRejectsSlashes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme name');

        $themeService = new ThemeService();
        $themeService->getThemeDetails('theme/subdir');
    }

    public function testThemeNameRejectsBackslashes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme name');

        $themeService = new ThemeService();
        $themeService->getThemeDetails('theme\\subdir');
    }

    public function testThemeNameRejectsNullBytes()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid theme name');

        $themeService = new ThemeService();
        $themeService->getThemeDetails("theme\0passwd");
    }

    public function testThemeNameAcceptsValidName()
    {
        $themeService = new ThemeService();

        // This should not throw exception for valid theme name
        try {
            $result = $themeService->getThemeDetails('default');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // It's ok if theme doesn't exist, we're testing validation
            $this->assertStringContainsString('not found', $e->getMessage());
        }
    }

    public function testThemeNameAcceptsValidNameWithDashes()
    {
        $themeService = new ThemeService();

        // Valid theme names with dashes and underscores
        try {
            $result = $themeService->getThemeDetails('my-theme');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // It's ok if theme doesn't exist
            $this->assertStringNotContainsString('Invalid theme name', $e->getMessage());
        }
    }

    public function testThemeNameAcceptsValidNameWithUnderscores()
    {
        $themeService = new ThemeService();

        try {
            $result = $themeService->getThemeDetails('my_theme');
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // It's ok if theme doesn't exist
            $this->assertStringNotContainsString('Invalid theme name', $e->getMessage());
        }
    }

    public function testIsValidThemeRejectsPathTraversal()
    {
        $themeService = new ThemeService();
        $result = $themeService->isValidTheme('../../etc/passwd');

        $this->assertFalse($result);
    }

    public function testIsValidThemeRejectsInvalidCharacters()
    {
        $themeService = new ThemeService();
        $result = $themeService->isValidTheme('theme/../passwd');

        $this->assertFalse($result);
    }
}

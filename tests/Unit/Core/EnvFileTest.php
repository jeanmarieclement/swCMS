<?php

namespace Tests\Unit\Core;

use App\Core\EnvFile;
use PHPUnit\Framework\TestCase;

/**
 * The .env reader has one job the app cannot do without: reading the file the
 * README tells people to copy into place.
 */
class EnvFileTest extends TestCase
{
    /** @var string */
    private $tmp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmp = tempnam(sys_get_temp_dir(), 'env');
    }

    protected function tearDown(): void
    {
        if (is_file($this->tmp)) {
            unlink($this->tmp);
        }

        parent::tearDown();
    }

    public function testTheShippedExampleParses()
    {
        // `cp .env.example .env` is the documented first step of a manual
        // install. parse_ini_file() chokes on its '#' comments, which left the
        // site silently running on defaults and unable to reach the database.
        $parsed = EnvFile::parse(ROOT_PATH . '/.env.example');

        $this->assertNotEmpty($parsed);
        $this->assertSame('mysql', $parsed['DB_DRIVER']);
        $this->assertArrayHasKey('DB_HOST', $parsed);
        $this->assertArrayHasKey('SITE_URL', $parsed);
    }

    public function testCommentsAreIgnored()
    {
        file_put_contents($this->tmp, "# Cache (Smarty) = fun\n; classic ini comment\nDB_NAME=swcms\n");

        $this->assertSame(['DB_NAME' => 'swcms'], EnvFile::parse($this->tmp));
    }

    public function testAValueMayContainAHash()
    {
        // Passwords do, and stripping there would corrupt the setting.
        file_put_contents($this->tmp, "DB_PASS=\"pa#ss\"\n");

        $this->assertSame('pa#ss', EnvFile::parse($this->tmp)['DB_PASS']);
    }

    public function testAMissingFileIsNotAnError()
    {
        $this->assertSame([], EnvFile::parse('/nonexistent/.env'));
    }

    public function testAnUnparseableFileYieldsNothingRatherThanFatalling()
    {
        file_put_contents($this->tmp, "DB_NAME=\"unterminated\nDB_USER=\n[\n");

        $this->assertSame([], EnvFile::parse($this->tmp));
    }
}

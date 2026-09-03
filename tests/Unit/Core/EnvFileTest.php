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

    public function testATrailingCommentIsNotPartOfTheValue()
    {
        file_put_contents($this->tmp, "DB_PORT=3306 # the MySQL port\nDB_PASS=p#ss\n");

        $parsed = EnvFile::parse($this->tmp);

        $this->assertSame('3306', $parsed['DB_PORT']);
        // No whitespace before it, so this one is a password, not a comment.
        $this->assertSame('p#ss', $parsed['DB_PASS']);
    }

    public function testAValueTheIniParserRejectsDoesNotDiscardTheRest()
    {
        // parse_ini_string() fails the whole file over a bare '=' in a value.
        // The database settings underneath must survive it.
        file_put_contents($this->tmp, "APP_KEY=base64:abcd==\nDB_NAME=swcms\nDB_USER=swcms_user\n");

        $parsed = EnvFile::parse($this->tmp);

        $this->assertSame('swcms', $parsed['DB_NAME']);
        $this->assertSame('swcms_user', $parsed['DB_USER']);
        $this->assertSame('base64:abcd==', $parsed['APP_KEY']);
    }

    public function testAMissingFileIsNotAnError()
    {
        $this->assertSame([], EnvFile::parse('/nonexistent/.env'));
    }

    public function testAFileWithNoSettingsAtAllYieldsNothing()
    {
        file_put_contents($this->tmp, "# nothing but commentary\n\n");

        $this->assertSame([], EnvFile::parse($this->tmp));
    }
}

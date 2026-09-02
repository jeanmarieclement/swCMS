<?php

namespace Tests\Unit;

use App\Core\Version;
use PHPUnit\Framework\TestCase;

/**
 * The VERSION file is the source of truth for the release version.
 *
 * The release workflow refuses to publish a tag that disagrees with it, so the
 * things that have to agree with it are worth locking down here: the version
 * Composer advertises, and what the running code reports.
 */
class VersionTest extends TestCase
{
    protected function tearDown(): void
    {
        Version::reset();
        parent::tearDown();
    }

    public function testTheVersionFileHoldsADottedVersion()
    {
        $contents = trim(file_get_contents(ROOT_PATH . '/VERSION'));

        $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/', $contents);
    }

    public function testComposerAdvertisesTheSameVersion()
    {
        $composer = json_decode(file_get_contents(ROOT_PATH . '/composer.json'), true);

        $this->assertSame(
            trim(file_get_contents(ROOT_PATH . '/VERSION')),
            $composer['version'] ?? null,
            'composer.json must carry the version the VERSION file declares'
        );
    }

    public function testCurrentReadsTheVersionFile()
    {
        $this->assertSame(trim(file_get_contents(ROOT_PATH . '/VERSION')), Version::current());
    }

    public function testCurrentIsNeverEmpty()
    {
        $this->assertNotSame('', Version::current());
    }
}

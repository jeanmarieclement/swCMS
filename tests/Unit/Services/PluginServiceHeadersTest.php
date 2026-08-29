<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PluginService;

/**
 * PluginService header defaults Test
 *
 * Depends: and Conflicts: are optional plugin headers. getPluginDetails() has to
 * return them as empty arrays when a plugin omits them, otherwise the admin
 * templates that read $plugin.depends / $plugin.conflicts emit
 * "Undefined array key" warnings on every render.
 *
 * @package Tests\Unit\Services
 */
class PluginServiceHeadersTest extends TestCase
{
    /** @var string */
    private $pluginsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginsPath = sys_get_temp_dir() . '/swcms-plugins-' . uniqid() . '/';
        mkdir($this->pluginsPath, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->pluginsPath);
        parent::tearDown();
    }

    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (array_diff(scandir($path), ['.', '..']) as $entry) {
            $full = $path . '/' . $entry;
            is_dir($full) ? $this->removeDirectory($full) : unlink($full);
        }
        rmdir($path);
    }

    private function writePlugin(string $name, string $header): void
    {
        mkdir($this->pluginsPath . $name, 0777, true);
        file_put_contents(
            $this->pluginsPath . $name . '/' . $name . '.php',
            "<?php\n/**\n" . $header . " */\n"
        );
    }

    private function service(): PluginService
    {
        return new PluginService($this->pluginsPath);
    }

    public function testDependsDefaultsToAnEmptyArrayWhenTheHeaderIsAbsent()
    {
        $this->writePlugin('no-headers', " * Plugin Name: No Headers\n * Version: 1.0.0\n");

        $plugin = $this->service()->getPluginDetails('no-headers');

        $this->assertArrayHasKey('depends', $plugin);
        $this->assertSame([], $plugin['depends']);
    }

    public function testConflictsDefaultsToAnEmptyArrayWhenTheHeaderIsAbsent()
    {
        $this->writePlugin('no-headers', " * Plugin Name: No Headers\n * Version: 1.0.0\n");

        $plugin = $this->service()->getPluginDetails('no-headers');

        $this->assertArrayHasKey('conflicts', $plugin);
        $this->assertSame([], $plugin['conflicts']);
    }

    public function testDeclaredDependenciesAreStillParsed()
    {
        $this->writePlugin(
            'with-headers',
            " * Plugin Name: With Headers\n * Depends: alpha, beta\n * Conflicts: gamma\n"
        );

        $plugin = $this->service()->getPluginDetails('with-headers');

        $this->assertSame(['alpha', 'beta'], array_values($plugin['depends']));
        $this->assertSame(['gamma'], array_values($plugin['conflicts']));
    }

    public function testEmptyHeaderValuesProduceAnEmptyArray()
    {
        // Both bundled plugins declare the headers with no value
        $this->writePlugin('empty-headers', " * Plugin Name: Empty\n * Depends: \n * Conflicts: \n");

        $plugin = $this->service()->getPluginDetails('empty-headers');

        $this->assertSame([], array_values($plugin['depends']));
        $this->assertSame([], array_values($plugin['conflicts']));
    }

    public function testUnknownPluginReturnsNull()
    {
        $this->assertNull($this->service()->getPluginDetails('does-not-exist'));
    }
}

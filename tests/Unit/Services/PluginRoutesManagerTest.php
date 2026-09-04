<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PluginRoutesManager;

/**
 * PluginRoutesManager Test
 *
 * The router loads plugin controllers from plugins/<plugin>/controllers/ at
 * dispatch time, so the activation gate has to recognise that layout too:
 * otherwise a plugin laid out the way plugins/README.md suggests gets no routes
 * registered at all.
 *
 * @package Tests\Unit\Services
 */
class PluginRoutesManagerTest extends TestCase
{
    /** @var string */
    private $pluginsPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pluginsPath = sys_get_temp_dir() . '/swcms-routes-' . uniqid() . '/';
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

    private function writePluginController(string $pluginName, string $controllerFile): void
    {
        $dir = $this->pluginsPath . $pluginName . '/controllers';
        mkdir($dir, 0777, true);
        file_put_contents($dir . '/' . $controllerFile, "<?php\n");
    }

    public function testControllerInsideThePluginFolderIsFound()
    {
        $this->writePluginController('backup-manager', 'BackupManagerController.php');

        $manager = new PluginRoutesManager($this->pluginsPath);

        $this->assertTrue($manager->hasController('backup-manager'));
    }

    public function testUnderscoresInThePluginNameAreHandled()
    {
        $this->writePluginController('my_cool-plugin', 'MyCoolPluginController.php');

        $manager = new PluginRoutesManager($this->pluginsPath);

        $this->assertTrue($manager->hasController('my_cool-plugin'));
    }

    public function testAPluginWithNoControllerIsStillRejected()
    {
        mkdir($this->pluginsPath . 'headless', 0777, true);

        $manager = new PluginRoutesManager($this->pluginsPath);

        $this->assertFalse($manager->hasController('headless'));
    }

    public function testAControllerUnderADifferentNameIsNotAccepted()
    {
        $this->writePluginController('backup-manager', 'SomethingElseController.php');

        $manager = new PluginRoutesManager($this->pluginsPath);

        $this->assertFalse($manager->hasController('backup-manager'));
    }

    public function testTheAdminControllersDirectoryIsStillHonoured()
    {
        // 'media' maps to MediaController, which ships in app/controllers/admin
        $manager = new PluginRoutesManager($this->pluginsPath);

        $this->assertTrue($manager->hasController('media'));
    }

    public function testTheBundledBackupManagerPluginIsRecognised()
    {
        // Default paths: the regression this issue is about
        $manager = new PluginRoutesManager();

        $this->assertTrue($manager->hasController('backup-manager'));
    }

    public function testTheBundledBackupManagerPluginGetsItsBaseRoute()
    {
        $manager = new PluginRoutesManager();

        $routes = $manager->generatePluginRoutes(
            'backup-manager',
            dirname(__DIR__, 3) . '/plugins/backup-manager'
        );

        $patterns = array_column($routes, 'pattern');
        $this->assertContains('admin/backup-manager', $patterns);
        $this->assertSame('BackupManager', $routes[0]['controller']);
    }
}

<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Autoloader;

class AutoloaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(Autoloader::class)) {
            require_once APP_PATH . '/core/Autoloader.php';
        }
    }

    public function testRegister()
    {
        $before = spl_autoload_functions();
        $initialCount = count($before);

        Autoloader::register();

        $after = spl_autoload_functions();
        $this->assertGreaterThanOrEqual($initialCount, count($after));

        // Verify Autoloader::loadClass is registered somewhere in the stack
        $found = false;
        foreach ($after as $fn) {
            if (is_array($fn) && $fn[0] === Autoloader::class && $fn[1] === 'loadClass') {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Autoloader::loadClass not found in spl_autoload stack');
    }

    public function testLoadCoreClass()
    {
        $this->markTestSkipped(
            'Cannot override file_exists/require_once (language constructs) in unit test context. ' .
            'Covered by integration tests via actual file loading.'
        );
    }

    public function testLoadModel()
    {
        $this->markTestSkipped(
            'Cannot override file_exists/require_once (language constructs) in unit test context. ' .
            'Covered by integration tests via actual file loading.'
        );
    }

    public function testLoadController()
    {
        $this->markTestSkipped(
            'Cannot override file_exists/require_once (language constructs) in unit test context. ' .
            'Covered by integration tests via actual file loading.'
        );
    }

    public function testLoadHelper()
    {
        $this->markTestSkipped(
            'Cannot override file_exists/require_once (language constructs) in unit test context. ' .
            'Covered by integration tests via actual file loading.'
        );
    }

    public function testLoadClass()
    {
        $this->markTestSkipped(
            'Autoloader uses static methods — cannot be partially mocked with PHPUnit. ' .
            'Covered end-to-end by Composer classmap autoloading in all other tests.'
        );
    }
}

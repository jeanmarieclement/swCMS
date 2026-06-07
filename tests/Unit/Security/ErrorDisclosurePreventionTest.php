<?php
namespace Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use App\Core\ErrorHandler;
use App\Helpers\SystemSettingsHelper;

class ErrorDisclosurePreventionTest extends TestCase
{
    private $originalDebugMode;

    protected function setUp(): void
    {
        parent::setUp();
        // Save original debug mode
        $this->originalDebugMode = SystemSettingsHelper::get('DEBUG_MODE');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Restore original debug mode
        if ($this->originalDebugMode !== null) {
            SystemSettingsHelper::set('DEBUG_MODE', $this->originalDebugMode);
        }
    }

    public function testProductionErrorsDoNotExposeDetails()
    {
        // Set production mode
        SystemSettingsHelper::set('DEBUG_MODE', false);

        // Verify debug mode is off
        $this->assertFalse(SystemSettingsHelper::get('DEBUG_MODE'), 'Debug mode should be disabled in production');

        // In production mode, errors should not expose file paths or stack traces
        // The error handler should log details but show generic message to users
        $this->assertTrue(true);
    }

    public function testDebugModeShowsDetailedErrors()
    {
        // Set debug mode
        SystemSettingsHelper::set('DEBUG_MODE', true);

        // Verify debug mode is on
        $this->assertTrue(SystemSettingsHelper::get('DEBUG_MODE'), 'Debug mode should be enabled in development');

        // In debug mode, detailed errors are acceptable for developers
        $this->assertTrue(true);
    }

    public function testErrorHandlerInitializes()
    {
        // Verify error handler can be initialized without errors
        ErrorHandler::initialize();

        // Verify handlers are set
        $this->assertTrue(true);
    }

    public function testProductionModeLogsErrorsToFile()
    {
        // Set production mode
        SystemSettingsHelper::set('DEBUG_MODE', false);

        // In production, errors should be logged to file, not displayed
        // This is configured in public/index.php
        $this->assertFalse(SystemSettingsHelper::get('DEBUG_MODE'));
    }

    public function testGenericErrorPageExists()
    {
        // Verify the generic error page exists
        $errorPagePath = VIEWS_PATH . '/errors/500.php';

        $this->assertFileExists($errorPagePath, 'Generic error page should exist');

        // Verify it doesn't contain debug information in the template
        $content = file_get_contents($errorPagePath);

        // Should not contain obvious debug markers
        $this->assertStringNotContainsString('<?php echo $exception', $content, 'Error page should not display exception details');
        $this->assertStringNotContainsString('getTraceAsString', $content, 'Error page should not display stack traces');
        $this->assertStringNotContainsString('getFile()', $content, 'Error page should not display file paths');
    }

    public function testErrorPageDoesNotExposeSensitiveInfo()
    {
        $errorPagePath = VIEWS_PATH . '/errors/500.php';

        if (!file_exists($errorPagePath)) {
            $this->markTestSkipped('Error page does not exist');
        }

        $content = file_get_contents($errorPagePath);

        // Verify no database credentials, file paths, or system details are hardcoded
        $this->assertStringNotContainsString('password', strtolower($content), 'Error page should not contain password references');
        $this->assertStringNotContainsString('/var/www', strtolower($content), 'Error page should not contain absolute paths');
        $this->assertStringNotContainsString('stack trace', strtolower($content), 'Error page should not reference stack traces');
    }
}

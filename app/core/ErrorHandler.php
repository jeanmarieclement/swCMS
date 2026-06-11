<?php

namespace App\Core;

use App\Helpers\LogHelper;
use App\Helpers\SystemSettingsHelper;

/**
 * ErrorHandler
 *
 * Manages error and exception handling for the application
 */
class ErrorHandler
{
    /**
     * Initialize error and exception handlers
     *
     * @return void
     */
    public static function initialize()
    {
        // Set error handler
        set_error_handler([self::class, 'handleError']);

        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Register shutdown function
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     *
     * @param int $level Error level
     * @param string $message Error message
     * @param string $file File where the error occurred
     * @param int $line Line where the error occurred
     * @return bool
     */
    public static function handleError($level, $message, $file, $line)
    {
        // Don't log suppressed errors (using @ operator)
        if (error_reporting() === 0) {
            return false;
        }

        // Format the error type
        $errorType = self::getErrorType($level);

        // Log detailed error information to file
        LogHelper::error("PHP {$errorType}: {$message}", [
            'file' => $file,
            'line' => $line,
            'error_level' => $level,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ]);

        // If it's a fatal error, show a generic error page in production
        if (in_array($level, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            if (self::isProductionEnvironment()) {
                self::showErrorPage();
            }
        }

        // Let PHP continue with its default error handling
        return false;
    }

    /**
     * Handle uncaught exceptions
     *
     * @param \Throwable $exception The uncaught exception
     * @return void
     */
    public static function handleException($exception)
    {
        // Log detailed error information to file
        LogHelper::critical("Uncaught exception: " . get_class($exception), [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Determine if we're in production mode
        $isProduction = self::isProductionEnvironment();

        // In debug/development mode, show detailed exception information
        if (!$isProduction) {
            // Display detailed exception information for developers
            echo "<h1>Application Error</h1>";
            echo "<p><strong>Type:</strong> " . get_class($exception) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>File:</strong> " . $exception->getFile() . "</p>";
            echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
            echo "<h2>Stack Trace:</h2>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            // In production, show a generic user-friendly error page
            // DO NOT expose file paths, database errors, or stack traces
            self::showErrorPage();
        }

        exit(1);
    }

    /**
     * Handle fatal errors on shutdown
     *
     * @return void
     */
    public static function handleShutdown()
    {
        $error = error_get_last();

        // Check if the error was a fatal error
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            // Log detailed error information to file
            LogHelper::critical("Fatal error on shutdown", [
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => self::getErrorType($error['type'])
            ]);

            // In production, show generic error page (no sensitive details)
            if (self::isProductionEnvironment()) {
                self::showErrorPage();
            }
        }
    }

    /**
     * Get the error type as a string
     *
     * @param int $type The error type
     * @return string
     */
    private static function getErrorType($type)
    {
        $errorTypes = [
            E_ERROR => 'Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return isset($errorTypes[$type]) ? $errorTypes[$type] : 'Unknown Error';
    }

    /**
     * Determine if the application is running in production environment
     *
     * @return bool
     */
    private static function isProductionEnvironment()
    {
        // Check APP_ENV environment variable first
        $appEnv = getenv('APP_ENV');
        if ($appEnv !== false) {
            return $appEnv === 'production';
        }

        // Check DEBUG_MODE setting
        $debugMode = SystemSettingsHelper::get('DEBUG_MODE');
        if ($debugMode !== null) {
            return !$debugMode;
        }

        // Check display_errors INI setting
        if (!ini_get('display_errors')) {
            return true;
        }

        // Default to production (safe mode) if uncertain
        return true;
    }

    /**
     * Show a generic error page
     *
     * @return void
     */
    private static function showErrorPage()
    {
        http_response_code(500);

        // Try to load a custom error template if available
        $errorTemplate = \VIEWS_PATH . '/errors/500.php';
        if (file_exists($errorTemplate)) {
            include $errorTemplate;
        } else {
            // Fallback to a basic error message that doesn't expose system details
            echo "<!DOCTYPE html>\n";
            echo "<html lang=\"en\">\n";
            echo "<head>\n";
            echo "    <meta charset=\"UTF-8\">\n";
            echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
            echo "    <title>Server Error</title>\n";
            echo "</head>\n";
            echo "<body>\n";
            echo "    <h1>Something went wrong</h1>\n";
            echo "    <p>We're sorry, but something went wrong. Please try again later.</p>\n";
            echo "    <p><a href=\"/\">Return to homepage</a></p>\n";
            echo "</body>\n";
            echo "</html>";
        }
    }
}

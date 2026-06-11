<?php

namespace App\Helpers;

/**
 * LogHelper
 *
 * Helper class for logging application events and errors with advanced features.
 */
class LogHelper
{
    // Define log levels and their priorities
    const LOG_LEVELS = [
        'debug'   => 100,  // Detailed debug information
        'info'    => 200,  // Interesting events
        'notice'  => 250,  // Normal but significant events
        'warning' => 300,  // Exceptional occurrences that are not errors
        'error'   => 400,  // Runtime errors
        'critical' => 500,  // Critical conditions
        'alert'   => 550,  // Action must be taken immediately
        'emergency' => 600   // System is unusable
    ];

    // Default minimum log level to record
    private static $minLogLevel = 'debug';

    // Default log directory and format
    private static $logDir = null;
    private static $logFormat = "[{date}] [{level}] {message}\n";

    /**
     * Initialize the logger with custom settings
     *
     * @param string $minLevel Minimum level to log
     * @param string $logDir Custom log directory (optional)
     * @param string $logFormat Custom log format (optional)
     * @return void
     */
    public static function init($minLevel = 'debug', $logDir = null, $logFormat = null)
    {
        if (isset(self::LOG_LEVELS[$minLevel])) {
            self::$minLogLevel = $minLevel;
        }

        if ($logDir !== null) {
            self::$logDir = rtrim($logDir, '/');
        }

        if ($logFormat !== null) {
            self::$logFormat = $logFormat;
        }
    }

    /**
     * Get the log directory path
     *
     * @return string The log directory path
     */
    private static function getLogDir()
    {
        if (self::$logDir === null) {
            self::$logDir = __DIR__ . '/../../logs';
        }

        // Create directory if it doesn't exist
        if (!file_exists(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        return self::$logDir;
    }

    /**
     * Log a message to the application log file
     *
     * @param string $message The message to log
     * @param string $level The log level (error, info, warning, etc.)
     * @param array $context Additional context data to include in log
     * @return bool Whether the log was written successfully
     */
    public static function logError($message, $level = 'error', $context = [])
    {
        // Check if this level should be logged
        if (
            !isset(self::LOG_LEVELS[$level]) ||
            self::LOG_LEVELS[$level] < self::LOG_LEVELS[self::$minLogLevel]
        ) {
            return false;
        }

        // Format the log entry
        $date = date('Y-m-d H:i:s');
        $entry = str_replace(
            ['{date}', '{level}', '{message}'],
            [$date, strtoupper($level), $message],
            self::$logFormat
        );

        // Add context if provided
        if (!empty($context)) {
            $contextStr = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $entry = rtrim($entry) . " Context: $contextStr\n";
        }

        // Determine log file based on level
        $filename = $level === 'error' || $level === 'critical' ||
                   $level === 'alert' || $level === 'emergency'
                   ? 'error.log' : 'app.log';
        $logfile = self::getLogDir() . '/' . $filename;

        // Write to log file
        return file_put_contents($logfile, $entry, FILE_APPEND) !== false;
    }

    /**
     * Log a debug message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return bool Whether the log was written successfully
     */
    public static function debug($message, $context = [])
    {
        return self::logError($message, 'debug', $context);
    }

    /**
     * Log an info message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return bool Whether the log was written successfully
     */
    public static function info($message, $context = [])
    {
        return self::logError($message, 'info', $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return bool Whether the log was written successfully
     */
    public static function warning($message, $context = [])
    {
        return self::logError($message, 'warning', $context);
    }

    /**
     * Log an error message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return bool Whether the log was written successfully
     */
    public static function error($message, $context = [])
    {
        return self::logError($message, 'error', $context);
    }

    /**
     * Log a critical message
     *
     * @param string $message The message to log
     * @param array $context Additional context data
     * @return bool Whether the log was written successfully
     */
    public static function critical($message, $context = [])
    {
        return self::logError($message, 'critical', $context);
    }
}

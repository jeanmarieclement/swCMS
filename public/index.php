<?php
/**
 * Main entry point for the CMS
 * This file handles all requests and routes them to the appropriate controllers
 */

use App\Core\Autoloader;
use App\Controllers\InstallController;
use App\Core\ErrorHandler;
use App\Helpers\LogHelper;
use App\Core\Router;
use App\Core\HookSystem;
use App\Services\PluginService;
use App\Helpers\SystemSettingsHelper;

// Define the application path
define('APP_PATH', dirname(__DIR__) . '/app');
define('PUBLIC_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));

// Check if installation is needed before loading full config
$installFlagFile = ROOT_PATH . '/data/.installed';
if (!file_exists($installFlagFile)) {
    // Load minimal config for installation
    require_once APP_PATH . '/Config/install_config.php';
    require_once APP_PATH . '/core/Autoloader.php';
    
    Autoloader::register();
    
    // Run installation wizard
    $installer = new InstallController();
    $installer->run();
    exit;
}

// Load the configuration
require_once APP_PATH . '/Config/Config.php';

// Load the autoloader
require_once APP_PATH . '/core/Autoloader.php';

Autoloader::register();

// Configure error reporting and logging based on environment
// Check APP_ENV environment variable first, then fall back to DEBUG_MODE setting
$appEnv = getenv('APP_ENV');
$isProduction = ($appEnv === 'production') || ($appEnv === false && !SystemSettingsHelper::get('DEBUG_MODE'));

if (!$isProduction) {
    // Development/Debug mode: Show detailed errors for developers
    error_reporting(E_ALL | E_DEPRECATED);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    LogHelper::init('debug');
} else {
    // Production mode: Log errors to file, do not display to users
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);

    // Set error log file path
    $logDir = ROOT_PATH . '/logs';
    if (!is_dir($logDir)) {
        if (!mkdir($logDir, 0755, true)) {
            // Log to system default if directory creation fails
            error_log("Failed to create logs directory: " . $logDir);
            $logDir = sys_get_temp_dir();
        }
    }
    ini_set('error_log', $logDir . '/php_errors.log');

    LogHelper::init('warning');
}
    

// Initialize error and exception handlers
ErrorHandler::initialize();

// Configure secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

// Set secure flag if using HTTPS (including reverse proxy detection)
$isHttps = (
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
);

if ($isHttps) {
    ini_set('session.cookie_secure', 1);
}

// Initialize session with secure settings
session_start();

// Initialize CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================
// Security Headers (CWE-693, CWE-1021)
// ==========================================

// X-Frame-Options: Prevent clickjacking attacks
header('X-Frame-Options: SAMEORIGIN');

// X-Content-Type-Options: Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// X-XSS-Protection: Enable browser XSS protection (legacy browsers)
header('X-XSS-Protection: 1; mode=block');

// Referrer-Policy: Control referrer information disclosure
header('Referrer-Policy: strict-origin-when-cross-origin');

// Content Security Policy: Mitigate XSS and injection attacks
// Load CSP configuration if available, otherwise use defaults
$securityConfig = file_exists(APP_PATH . '/Config/security.php')
    ? require APP_PATH . '/Config/security.php'
    : null;

if ($securityConfig && isset($securityConfig['csp'])) {
    $cspDirectives = [];
    foreach ($securityConfig['csp'] as $directive => $value) {
        $cspDirectives[] = $directive . ' ' . $value;
    }
    $csp = implode('; ', $cspDirectives);
} else {
    // Default CSP if config file not found
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
           "img-src 'self' data: https:; " .
           "font-src 'self' data: https://cdn.jsdelivr.net; " .
           "connect-src 'self'; " .
           "frame-ancestors 'self'";
}
header("Content-Security-Policy: " . $csp);

// Strict-Transport-Security: Force HTTPS connections (only when HTTPS is active)
// Use the same HTTPS detection logic as session security
if ($isHttps) {
    $hstsConfig = $securityConfig['hsts'] ?? ['max-age' => 31536000, 'includeSubDomains' => true];
    $hstsValue = 'max-age=' . ($hstsConfig['max-age'] ?? 31536000);
    if ($hstsConfig['includeSubDomains'] ?? true) {
        $hstsValue .= '; includeSubDomains';
    }
    if ($hstsConfig['preload'] ?? false) {
        $hstsValue .= '; preload';
    }
    header('Strict-Transport-Security: ' . $hstsValue);
}

// ==========================================
// End Security Headers
// ==========================================

// Initialize Hook System
$hookSystem = HookSystem::getInstance();
$hookSystem->initializeCoreHooks();

// Initialize and load active plugins
try {
    $pluginService = new PluginService();
    $pluginService->loadActivePlugins();
} catch (\Exception $e) {
    LogHelper::error('Error loading plugins', ['error' => $e->getMessage()]);
}

// Fire init hook for plugins
$hookSystem->doAction('init');

try {
    // Start the application
    $router = new Router();
    $router->dispatch();
} catch (\Throwable $e) {
    // Log detailed error information to file
    LogHelper::critical("Unhandled exception in router", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    // Show error page based on environment
    if (!$isProduction) {
        // Debug mode - let the exception handler show detailed information
        throw $e;
    } else {
        // Production mode - show generic error page without sensitive details
        http_response_code(500);
        if (file_exists(APP_PATH . '/views/errors/500.php')) {
            include APP_PATH . '/views/errors/500.php';
        } else {
            echo '<!DOCTYPE html>';
            echo '<html><head><title>Server Error</title></head><body>';
            echo '<h1>Something went wrong</h1>';
            echo '<p>We\'re sorry, but something went wrong. Please try again later.</p>';
            echo '<p><a href="/">Return to homepage</a></p>';
            echo '</body></html>';
        }
    }
}

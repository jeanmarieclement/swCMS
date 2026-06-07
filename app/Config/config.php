<?php
/**
 * Main configuration file for the CMS
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

// Load environment variables from .env file if it exists
$envFile = ROOT_PATH . '/.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    if ($envVars) {
        foreach ($envVars as $key => $value) {
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
            }
        }
    }
}

// Helper function to get environment variable with fallback
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Database configuration with .env support
define('DB_DRIVER', env('DB_DRIVER', 'mysql'));
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'swcms'));
define('DB_USER', env('DB_USER', 'swcms_user'));
define('DB_PASS', env('DB_PASS', 'swcms_password'));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// SQLite path configuration
if (DB_DRIVER === 'sqlite') {
    define('DB_SQLITE_PATH', env('DB_SQLITE_PATH', ROOT_PATH . '/data/database.sqlite'));
}

// Path configurations
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('MODELS_PATH', APP_PATH . '/models');
define('VIEWS_PATH', APP_PATH . '/views');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('PLUGINS_PATH', ROOT_PATH . '/plugins');
define('THEMES_PATH', ROOT_PATH . '/themes');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('SERVICES_PATH', APP_PATH . '/services');
define('FRONTEND_CONTROLLERS_PATH', CONTROLLERS_PATH . '/Frontend');
define('ADMIN_CONTROLLERS_PATH', CONTROLLERS_PATH . '/Admin');
define('FRONTEND_VIEWS_PATH', VIEWS_PATH . '/Frontend');
define('ADMIN_VIEWS_PATH', VIEWS_PATH . '/Admin');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Cache configuration
define('CACHE_DRIVER', env('CACHE_DRIVER', 'file')); // file or database
define('CACHE_TTL', env('CACHE_TTL', 3600)); // Default TTL in seconds (1 hour)
define('CACHE_PATH', ROOT_PATH . '/storage/cache');





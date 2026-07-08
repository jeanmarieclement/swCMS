<?php
/**
 * Test Bootstrap File
 * Sets up environment for testing
 */

// Root paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

// App sub-paths
if (!defined('CONTROLLERS_PATH')) {
    define('CONTROLLERS_PATH', APP_PATH . '/controllers');
}
if (!defined('MODELS_PATH')) {
    define('MODELS_PATH', APP_PATH . '/models');
}
if (!defined('VIEWS_PATH')) {
    define('VIEWS_PATH', APP_PATH . '/views');
}
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APP_PATH . '/Config');
}
if (!defined('HELPERS_PATH')) {
    define('HELPERS_PATH', APP_PATH . '/helpers');
}
if (!defined('SERVICES_PATH')) {
    define('SERVICES_PATH', APP_PATH . '/services');
}

// Derived paths
if (!defined('PLUGINS_PATH')) {
    define('PLUGINS_PATH', ROOT_PATH . '/plugins');
}
if (!defined('THEMES_PATH')) {
    define('THEMES_PATH', ROOT_PATH . '/themes');
}
if (!defined('UPLOADS_PATH')) {
    define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
}
if (!defined('LOGS_PATH')) {
    define('LOGS_PATH', ROOT_PATH . '/logs');
}
if (!defined('CACHE_PATH')) {
    define('CACHE_PATH', ROOT_PATH . '/storage/cache');
}
if (!defined('FRONTEND_CONTROLLERS_PATH')) {
    define('FRONTEND_CONTROLLERS_PATH', CONTROLLERS_PATH . '/Frontend');
}
if (!defined('ADMIN_CONTROLLERS_PATH')) {
    define('ADMIN_CONTROLLERS_PATH', CONTROLLERS_PATH . '/Admin');
}
if (!defined('FRONTEND_VIEWS_PATH')) {
    define('FRONTEND_VIEWS_PATH', VIEWS_PATH . '/Frontend');
}
if (!defined('ADMIN_VIEWS_PATH')) {
    define('ADMIN_VIEWS_PATH', VIEWS_PATH . '/Admin');
}

// Database constants: prefer environment variables (Docker sets these) over defaults
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');
}
if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'swcms');
}
if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'swcms_user');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'swcms_password');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}
if (!defined('DB_PORT')) {
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
}
if (!defined('DB_SQLITE_PATH')) {
    // In-memory DB by default so unit tests never touch the real database
    define('DB_SQLITE_PATH', getenv('DB_SQLITE_PATH') ?: ':memory:');
}

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// When testing against the in-memory SQLite DB, create the minimal schema
// the models expect (the shared PDO singleton keeps it alive for the run)
if (DB_DRIVER === 'sqlite' && DB_SQLITE_PATH === ':memory:') {
    $testDb = \App\Core\Database\Database::getInstance();
    $testDb->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        display_name VARCHAR(100) DEFAULT NULL,
        role VARCHAR(50) NOT NULL DEFAULT 'subscriber',
        status VARCHAR(10) NOT NULL DEFAULT 'active',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT NULL
    )");
    $testDb->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        `key` VARCHAR(191) NOT NULL UNIQUE,
        value TEXT,
        description TEXT,
        autoload INTEGER DEFAULT 1
    )");
}

// Configure session settings for testing (before any session_start calls)
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.cache_limiter', '');

<?php
/**
 * Test Bootstrap File
 * Sets up environment for testing
 */

// Define constants for testing
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!defined('APP_PATH')) {
    define('APP_PATH', ROOT_PATH . '/app');
}

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

// Define database constants for testing (mock values - in global namespace)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'test_db');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'test_user');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', 'test_pass');
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', 'utf8mb4');
}

// Load Composer autoloader
require_once ROOT_PATH . '/vendor/autoload.php';

// Configure session settings for testing (before any session_start calls)
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.cache_limiter', '');

// Include any additional setup for tests

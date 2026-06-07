<?php
/**
 * Minimal configuration file for installation process
 */

// Define basic paths
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__));
}

if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', ROOT_PATH . '/public');
}

// Path configurations for installation
define('CONTROLLERS_PATH', APP_PATH . '/controllers');
define('MODELS_PATH', APP_PATH . '/models');
define('VIEWS_PATH', APP_PATH . '/views');
define('HELPERS_PATH', APP_PATH . '/helpers');
define('LOGS_PATH', ROOT_PATH . '/logs');
define('DATA_PATH', ROOT_PATH . '/data');

// Create necessary directories if they don't exist
$directories = [DATA_PATH, LOGS_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Basic error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration for installation
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
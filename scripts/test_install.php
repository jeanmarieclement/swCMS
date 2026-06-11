<?php
/**
 * Test script to verify installation system integration
 * This script simulates the installation process and checks database integration
 */

// Set up basic paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('DATA_PATH', ROOT_PATH . '/data');

require_once APP_PATH . '/Config/install_config.php';
require_once APP_PATH . '/core/Autoloader.php';

use App\Core\Autoloader;
use App\Helpers\SystemSettingsHelper;

Autoloader::register();

echo "=== swCMS Installation System Test ===\n\n";

// Test 1: Check installation flag
echo "1. Testing installation flag detection...\n";
$flagFile = ROOT_PATH . '/data/.installed';
if (file_exists($flagFile)) {
    echo "   ✓ Installation flag exists - installer should NOT run\n";
    $flagData = json_decode(file_get_contents($flagFile), true);
    if ($flagData) {
        echo "   ✓ Flag data: " . json_encode($flagData, JSON_PRETTY_PRINT) . "\n";
    }
} else {
    echo "   ✓ No installation flag - installer should run\n";
}

// Test 2: Check configuration system
echo "\n2. Testing configuration system...\n";
try {
    // Load main config to test .env integration
    require_once APP_PATH . '/Config/config.php';
    echo "   ✓ Configuration loaded successfully\n";
    echo "   ✓ DB_DRIVER: " . DB_DRIVER . "\n";
    echo "   ✓ DB_HOST: " . DB_HOST . "\n";
    echo "   ✓ DB_NAME: " . DB_NAME . "\n";
    
    // Test env function
    if (function_exists('env')) {
        echo "   ✓ env() function available\n";
    } else {
        echo "   ✗ env() function missing\n";
    }
} catch (Exception $e) {
    echo "   ✗ Configuration error: " . $e->getMessage() . "\n";
}

// Test 3: Check database connection (if configured)
echo "\n3. Testing database connection...\n";
try {
    if (DB_DRIVER === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, defined('DB_PORT') ? DB_PORT : 3306);
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        echo "   ✓ MySQL connection successful\n";
    } elseif (DB_DRIVER === 'sqlite') {
        $sqlitePath = defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : DATA_PATH . '/database.sqlite';
        $dsn = 'sqlite:' . $sqlitePath;
        $pdo = new PDO($dsn);
        echo "   ✓ SQLite connection successful\n";
    }
} catch (PDOException $e) {
    echo "   ⚠ Database connection failed (expected during first install): " . $e->getMessage() . "\n";
}

// Test 4: Check required directories
echo "\n4. Testing directory permissions...\n";
$directories = [
    ROOT_PATH . '/data' => 'Data directory',
    ROOT_PATH . '/logs' => 'Logs directory',
    ROOT_PATH . '/public/uploads' => 'Uploads directory (if exists)',
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✓ $description: writable\n";
        } else {
            echo "   ✗ $description: not writable\n";
        }
    } else {
        echo "   ⚠ $description: does not exist\n";
    }
}

// Test 5: Check SystemSettingsHelper integration
echo "\n5. Testing SystemSettingsHelper integration...\n";
try {
    // Only test if database is available and settings table exists
    if (isset($pdo)) {
        // Check if settings table exists
        if (DB_DRIVER === 'mysql') {
            $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
        } else {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'");
        }
        
        if ($stmt->fetch()) {
            echo "   ✓ Settings table exists\n";
            
            // Test SystemSettingsHelper
            $siteName = SystemSettingsHelper::get('SITE_NAME');
            echo "   ✓ SystemSettingsHelper working, SITE_NAME: " . ($siteName ?? 'not set') . "\n";
        } else {
            echo "   ⚠ Settings table does not exist (will be created during installation)\n";
        }
    }
} catch (Exception $e) {
    echo "   ⚠ SystemSettingsHelper test skipped: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo test the installer:\n";
echo "1. Remove the installation flag: php scripts/remove_install_flag.php\n";
echo "2. Visit your site URL in a browser\n";
echo "3. Complete the installation wizard\n";
echo "4. Run this test again to verify everything works\n";
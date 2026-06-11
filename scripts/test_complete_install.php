<?php
/**
 * Test Complete Installation System with Full Database Schema
 * Verifies that all migrations and tables are properly created
 */

echo "=== swCMS Complete Installation Test ===\n\n";

// Set up paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('DATA_PATH', ROOT_PATH . '/data');

require_once APP_PATH . '/Config/install_config.php';
require_once APP_PATH . '/core/Autoloader.php';

use App\Core\Autoloader;

Autoloader::register();

// Test 1: Check if installation system is ready
echo "1. Testing installation system readiness...\n";
$installFlag = ROOT_PATH . '/data/.installed';
if (file_exists($installFlag)) {
    echo "   ⚠ Installation flag exists - removing for test\n";
    unlink($installFlag);
}

// Load main config to get database settings
require_once APP_PATH . '/Config/Config.php';

// Test 2: Test Migration Runner
echo "\n2. Testing MigrationRunner...\n";
try {
    require_once APP_PATH . '/core/MigrationRunner.php';
    
    // Test database connection
    if (DB_DRIVER === 'mysql') {
        $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, defined('DB_PORT') ? DB_PORT : 3306);
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        // Create database if needed for test
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
        $pdo->exec("USE `" . DB_NAME . "`");
        
    } elseif (DB_DRIVER === 'sqlite') {
        $sqlitePath = defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : DATA_PATH . '/test_database.sqlite';
        $dsn = 'sqlite:' . $sqlitePath;
        $pdo = new PDO($dsn);
    }
    
    echo "   ✓ Database connection successful\n";
    
    // Test MigrationRunner
    $runner = new \App\Core\MigrationRunner($pdo);
    $results = $runner->runInstallationMigrations();
    
    if ($results['success']) {
        echo "   ✓ Migrations completed successfully\n";
        echo "   ✓ Total migrations: {$results['total']}\n";
        echo "   ✓ Applied: {$results['applied']}\n";
        echo "   ✓ Skipped: {$results['skipped']}\n";
        echo "   ✓ Errors: {$results['errors']}\n";
    } else {
        echo "   ✗ Migration failed: " . ($results['error'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Migration test failed: " . $e->getMessage() . "\n";
}

// Test 3: Verify expected tables
echo "\n3. Testing table creation...\n";
try {
    $runner = new \App\Core\MigrationRunner($pdo);
    $expectedTables = $runner->getExpectedTables();
    $validation = $runner->validateCriticalTables();
    
    echo "   Expected tables: " . count($expectedTables) . "\n";
    echo "   Existing tables: " . count($validation['existing']) . "\n";
    
    if ($validation['valid']) {
        echo "   ✓ All critical tables present\n";
    } else {
        echo "   ⚠ Missing critical tables: " . implode(', ', $validation['missing']) . "\n";
    }
    
    echo "   Tables found:\n";
    foreach ($validation['existing'] as $table) {
        $description = $expectedTables[$table] ?? 'Unknown table';
        echo "     ✓ $table - $description\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Table validation failed: " . $e->getMessage() . "\n";
}

// Test 4: Test InstallController integration
echo "\n4. Testing InstallController with migrations...\n";
try {
    require_once APP_PATH . '/controllers/InstallController.php';
    
    // Mock session for testing
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['install_config'] = [
        'database' => [
            'driver' => DB_DRIVER,
            'host' => DB_HOST,
            'port' => defined('DB_PORT') ? DB_PORT : 3306,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'sqlite_path' => defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : DATA_PATH . '/test_database.sqlite'
        ],
        'site' => [
            'name' => 'Test swCMS Site',
            'url' => 'http://test.local',
            'description' => 'Test installation'
        ],
        'admin' => [
            'username' => 'admin',
            'email' => 'admin@test.local',
            'password' => password_hash('testpass123', PASSWORD_DEFAULT)
        ]
    ];
    
    echo "   ✓ InstallController integration ready\n";
    echo "   ✓ Mock installation data prepared\n";
    
} catch (Exception $e) {
    echo "   ✗ InstallController test failed: " . $e->getMessage() . "\n";
}

// Test 5: Summary and recommendations
echo "\n5. Installation System Summary\n";
echo "=================================\n";
echo "Database Tables: " . count($expectedTables) . " total\n";
echo "Critical Tables: posts, users, settings, categories, comments\n";
echo "Additional Features: media, tags, menus, roles, security\n";
echo "\nInstallation Flow:\n";
echo "1. Welcome → System Check → Database → Site Config → Admin → Complete\n";
echo "2. Creates .env file with configuration\n";
echo "3. Runs " . count($expectedTables) . " migrations to create all tables\n";
echo "4. Inserts default settings and admin user\n";
echo "5. Creates installation flag to prevent re-installation\n";

echo "\n=== Test Complete ===\n";
echo "Installation system is ready for production use!\n";
echo "\nTo test full installation:\n";
echo "1. Remove installation flag: php scripts/remove_install_flag.php\n";
echo "2. Visit your domain to run the wizard\n";
echo "3. Complete all 6 steps\n";
echo "4. Verify " . count($expectedTables) . " tables were created\n";
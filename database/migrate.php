<?php
// Simple migration manager script for swCMS
// Supports up/down and single migration execution via CLI arguments
// Usage:
//   php migrate.php up [migration_filename]
//   php migrate.php down [migration_filename]
//
// Parameters:
//   up|down           Required. 'up' applies migrations, 'down' reverts.
//   migration_filename Optional. If specified, applies/reverts only that migration.
//
// If no arguments or invalid arguments are given, this help is shown.


// Database configuration (edit if needed)
require_once __DIR__ . '/../vendor/autoload.php';

// Define the application path
if (!defined('APP_PATH')) {
    define('APP_PATH', dirname(__DIR__) . '/App');
}
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', __DIR__);
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Load the configuration file
require_once APP_PATH . '/Config/Config.php';
// Include the custom Database class
require_once __DIR__ . '/../App/Core/Database/Database.php';

use App\Core\Database\Database;

$pdo = Database::getInstance();

// Create migrations table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Retrieve already applied migrations
$applied = $pdo->query("SELECT migration FROM migrations")->fetchAll($pdo::FETCH_COLUMN);

// Scan the migrations folder
$migrationFiles = glob(__DIR__ . '/migrations/*.php');

// Parse CLI arguments
if (!isset($argv[1]) || !in_array(strtolower($argv[1]), ['up', 'down'])) {
    echo "swCMS Migration Manager\n";
    echo "Usage:\n";
    echo "  php migrate.php up [migration_filename]\n";
    echo "  php migrate.php down [migration_filename]\n";
    echo "\n";
    echo "Parameters:\n";
    echo "  up|down            Required. 'up' applies migrations, 'down' reverts.\n";
    echo "  migration_filename Optional. If specified, applies/reverts only that migration.\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php migrate.php up\n";
    echo "  php migrate.php down\n";
    echo "  php migrate.php up 2025_06_19_000010_create_users_table.php\n";
    echo "  php migrate.php down 2025_06_19_000010_create_users_table.php\n";
    exit(0);
}
$action = strtolower($argv[1]);
$migrationArg = isset($argv[2]) ? $argv[2] : null;

// Helper: get migration file by name
function findMigrationFile($files, $filename) {
    foreach ($files as $file) {
        if (basename($file) === $filename) return $file;
    }
    return false;
}

if ($migrationArg) {
    // Run on a single migration file
    $file = findMigrationFile($migrationFiles, $migrationArg);
    if (!$file) {
        echo "[ERROR] Migration file $migrationArg not found\n";
        exit(1);
    }
    $filename = basename($file);
    require_once $file;
    $contents = file_get_contents($file);
    if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
        $className = $matches[1];
    } else {
        echo "[ERROR] No class found in $filename\n";
        exit(1);
    }
    if (!class_exists($className)) {
        echo "[ERROR] Class $className not found in $filename\n";
        exit(1);
    }
    $migration = new $className();
    try {
        if ($action === 'down') {
            $migration->down();
            $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
            $stmt->execute(['migration' => $filename]);
            echo "[OK] $filename reverted (down)\n";
        } else {
            if (in_array($filename, $applied)) {
                echo "[SKIP] $filename already applied\n";
            } else {
                $migration->up();
                $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                $stmt->execute(['migration' => $filename]);
                echo "[OK] $filename applied\n";
            }
        }
    } catch (Exception $e) {
        echo "[ERROR] $filename: " . $e->getMessage() . "\n";
        exit(1);
    }
} elseif ($action === 'down') {
    // Down last applied migration
    if (empty($applied)) {
        echo "[INFO] No migrations to revert.\n";
        exit(0);
    }
    $filename = end($applied);
    $file = findMigrationFile($migrationFiles, $filename);
    if (!$file) {
        echo "[ERROR] Migration file $filename not found\n";
        exit(1);
    }
    require_once $file;
    $contents = file_get_contents($file);
    if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
        $className = $matches[1];
    } else {
        echo "[ERROR] No class found in $filename\n";
        exit(1);
    }
    if (!class_exists($className)) {
        echo "[ERROR] Class $className not found in $filename\n";
        exit(1);
    }
    $migration = new $className();
    try {
        $migration->down();
        $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration = :migration");
        $stmt->execute(['migration' => $filename]);
        echo "[OK] $filename reverted (down)\n";
    } catch (Exception $e) {
        echo "[ERROR] $filename: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    // Default: up all pending migrations
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        if (in_array($filename, $applied)) {
            echo "[SKIP] $filename already applied\n";
            continue;
        }
        require_once $file;
        // Get the class name from the first declaration found in the file
        $contents = file_get_contents($file);
        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $className = $matches[1];
        } else {
            echo "[ERROR] No class found in $filename\n";
            continue;
        }
        if (!class_exists($className)) {
            echo "[ERROR] Class $className not found in $filename\n";
            continue;
        }
        $migration = new $className();
        try {
            $migration->up();
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $filename]);
            echo "[OK] $filename applied\n";
        } catch (Exception $e) {
            echo "[ERROR] $filename: " . $e->getMessage() . "\n";
            break;
        }
    }
}

echo "Migrations completed.\n";

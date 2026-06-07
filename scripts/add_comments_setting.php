<?php
// Define required constants
define('APP_PATH', __DIR__ . '/../App/');
define('ROOT_PATH', __DIR__ . '/../');
define('PUBLIC_PATH', __DIR__ . '/../public/');

require_once __DIR__ . '/../App/Config/config.php';
require_once __DIR__ . '/../App/Core/Database/Database.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    
    // First, let's see the structure of the settings table
    echo "Settings table structure:\n";
    if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
        $stmt = $db->query("PRAGMA table_info(settings)");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- " . $column['name'] . " (" . $column['type'] . ")\n";
        }
    } else {
        $stmt = $db->query("DESCRIBE settings");
        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
        }
    }
    
    // Use the correct column names from the table structure
    // 'key' is a reserved word in MySQL, so we need to escape it with backticks
    
    // Check if COMMENTS_ENABLED setting exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE `key` = 'COMMENTS_ENABLED'");
    $stmt->execute();
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        // Insert the global comments setting
        $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, description) VALUES ('COMMENTS_ENABLED', '1', 'Enable or disable comments globally')");
        $stmt->execute();
        echo "COMMENTS_ENABLED setting added successfully\n";
    } else {
        echo "COMMENTS_ENABLED setting already exists\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
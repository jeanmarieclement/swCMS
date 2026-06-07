<?php
/**
 * Create and populate SQLite database from MySQL data
 */

try {
    // Create SQLite database
    $pdo = new PDO('sqlite:/var/www/html/data/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating SQLite database schema...\n";
    
    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    // Read and execute schema
    $schema = file_get_contents('/var/www/html/scripts/mysql_to_sqlite_schema.sql');
    $pdo->exec($schema);
    echo "Schema created successfully.\n";
    
    // Read and execute data import
    $data = file_get_contents('/var/www/html/scripts/import_data_ordered.sql');
    $pdo->exec($data);
    echo "Data imported successfully.\n";
    
    // Verify data
    $tables = ['users', 'roles', 'posts', 'pages', 'categories', 'tags', 'settings'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "Table $table: $count records\n";
        } catch (Exception $e) {
            echo "Table $table: Error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "SQLite database created and populated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
<?php

namespace App\Core;

/**
 * Migration Runner for Installation Process
 * Simplified migration runner that works without Composer dependencies
 */
class MigrationRunner
{
    private $pdo;
    private $migrationsPath;
    private $appliedMigrations = [];

    public function __construct($pdo, $migrationsPath = null)
    {
        $this->pdo = $pdo;
        $this->migrationsPath = $migrationsPath ?: \ROOT_PATH . '/database/migrations';
    }

    /**
     * Run all pending migrations during installation
     */
    public function runInstallationMigrations()
    {
        try {
            // Create migrations table if it doesn't exist
            $this->createMigrationsTable();

            // Get applied migrations
            $this->loadAppliedMigrations();

            // Get all migration files
            $migrationFiles = $this->getMigrationFiles();

            $results = [];

            foreach ($migrationFiles as $file) {
                $filename = basename($file);

                if (in_array($filename, $this->appliedMigrations)) {
                    $results[] = [
                        'file' => $filename,
                        'status' => 'skipped',
                        'message' => 'Already applied'
                    ];
                    continue;
                }

                try {
                    $this->runSingleMigration($file);
                    $results[] = [
                        'file' => $filename,
                        'status' => 'success',
                        'message' => 'Applied successfully'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'file' => $filename,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];

                    // Stop on first error during installation
                    break;
                }
            }

            return [
                'success' => !$this->hasErrors($results),
                'results' => $results,
                'total' => count($migrationFiles),
                'applied' => count(array_filter($results, fn($r) => $r['status'] === 'success')),
                'skipped' => count(array_filter($results, fn($r) => $r['status'] === 'skipped')),
                'errors' => count(array_filter($results, fn($r) => $r['status'] === 'error'))
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => []
            ];
        }
    }

    /**
     * Create migrations tracking table
     */
    private function createMigrationsTable()
    {
        if (defined('DB_DRIVER') && \DB_DRIVER === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                migration TEXT NOT NULL,
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        }

        $this->pdo->exec($sql);
    }

    /**
     * Load list of applied migrations
     */
    private function loadAppliedMigrations()
    {
        try {
            $stmt = $this->pdo->query("SELECT migration FROM migrations ORDER BY applied_at");
            $this->appliedMigrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            // Table might not exist yet
            $this->appliedMigrations = [];
        }
    }

    /**
     * Get all migration files sorted by name
     */
    private function getMigrationFiles()
    {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    /**
     * Run a single migration file
     */
    private function runSingleMigration($file)
    {
        $filename = basename($file);

        // Load the migration file
        require_once $file;

        // Extract class name from file
        $contents = file_get_contents($file);
        if (!preg_match('/class\s+(\w+)/', $contents, $matches)) {
            throw new \Exception("No class found in {$filename}");
        }

        $className = $matches[1];

        if (!class_exists($className)) {
            throw new \Exception("Class {$className} not found in {$filename}");
        }

        // Create instance and run migration
        $migration = new $className();
        $migration->up();

        // Record migration as applied
        $stmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$filename]);
    }

    /**
     * Check if results contain errors
     */
    private function hasErrors($results)
    {
        return !empty(array_filter($results, fn($r) => $r['status'] === 'error'));
    }

    /**
     * Get tables that will be created by migrations
     */
    public function getExpectedTables()
    {
        return [
            'settings' => 'System configuration settings',
            'users' => 'User accounts and authentication',
            'posts' => 'Blog posts and articles',
            'pages' => 'Static pages content (implied)',
            'categories' => 'Content categorization',
            'tags' => 'Content tagging system',
            'post_tags' => 'Many-to-many post-tag relationships',
            'comments' => 'User comments on posts',
            'media' => 'Media library and file management',
            'roles' => 'User roles and permissions',
            'menus' => 'Site navigation menus',
            'menu_items' => 'Individual menu items',
            'menu_blocks' => 'Menu display blocks',
            'options' => 'Additional system options',
            'migrations' => 'Migration tracking table',
            'user_login_attempts' => 'Security login attempt tracking'
        ];
    }

    /**
     * Quick validation to ensure critical tables exist
     */
    public function validateCriticalTables()
    {
        $criticalTables = ['settings', 'users', 'posts', 'categories', 'comments'];
        $existingTables = $this->getExistingTables();

        $missing = array_diff($criticalTables, $existingTables);

        return [
            'valid' => empty($missing),
            'existing' => $existingTables,
            'missing' => $missing,
            'critical' => $criticalTables
        ];
    }

    /**
     * Get list of existing tables
     */
    private function getExistingTables()
    {
        try {
            if (defined('DB_DRIVER') && \DB_DRIVER === 'sqlite') {
                $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            } else {
                $stmt = $this->pdo->query("SHOW TABLES");
            }
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            return [];
        }
    }
}

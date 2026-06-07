<?php
/**
 * BackupService - Core backup functionality for database and file operations
 */

namespace BackupManager\Services;

use PDO;
use ZipArchive;
use App\Core\Database\Database;

class BackupService {
    
    private $backupDir;
    private $db;
    private $settings;
    
    public function __construct() {
        $this->backupDir = ROOT_PATH . '/backups';
        $this->db = Database::getInstance();
        $this->settings = $this->getSettings();
    }
    
    /**
     * Create a backup based on type
     * @param string $type database|files|full
     * @param array $options Additional backup options
     * @return array Result with success status and details
     */
    public function createBackup($type, $options = []) {
        try {
            // Create backup job record
            $jobId = $this->createBackupJob($type, $options);
            
            // Update job status to running
            $this->updateJobStatus($jobId, 'running');
            
            $result = null;
            
            switch ($type) {
                case 'database':
                    $result = $this->createDatabaseBackup($jobId, $options);
                    break;
                case 'files':
                    $result = $this->createFileBackup($jobId, $options);
                    break;
                case 'full':
                    // Full backup disabled due to environment limitations
                    throw new \Exception('Full backup is not available in this environment due to file permission restrictions. Please use separate Database and Files backups instead.');
                    break;
                case 'full_disabled':
                    $result = $this->createFullBackup($jobId, $options);
                    break;
                default:
                    throw new \Exception('Invalid backup type: ' . $type);
            }
            
            if ($result['success']) {
                $this->updateJobStatus($jobId, 'completed', [
                    'filename' => $result['filename'],
                    'file_size' => $result['file_size']
                ]);
            } else {
                $this->updateJobStatus($jobId, 'failed', [], $result['error']);
            }
            
            return $result;
            
        } catch (\Exception $e) {
            if (isset($jobId)) {
                $this->updateJobStatus($jobId, 'failed', [], $e->getMessage());
            }
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create database backup
     */
    private function createDatabaseBackup($jobId, $options = []) {
        $filename = 'database_' . date('Y-m-d_H-i-s') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;
        
        try {
            // Get database configuration
            $dbConfig = $this->getDatabaseConfig();
            
            if ($dbConfig['driver'] === 'mysql') {
                $result = $this->exportMySQLDatabase($filepath, $dbConfig, $options);
            } else {
                $result = $this->exportSQLiteDatabase($filepath, $dbConfig, $options);
            }
            
            if ($result) {
                // Verify file was created and get size
                if (!file_exists($filepath)) {
                    throw new \Exception('Database backup file was not created successfully');
                }
                
                $fileSize = filesize($filepath);
                if ($fileSize === false) {
                    throw new \Exception('Cannot determine database backup file size');
                }
                $fileSize = (int)$fileSize;
                
                // Compress if enabled
                if ($this->settings['compression_level'] > 0) {
                    \App\Helpers\LogHelper::info("Database Backup: Starting compression with level " . $this->settings['compression_level']);
                    $compressedFile = $this->compressFile($filepath);
                    \App\Helpers\LogHelper::info("Database Backup: Compression result: " . ($compressedFile ?: 'FAILED'));
                    
                    if ($compressedFile) {
                        \App\Helpers\LogHelper::info("Database Backup: Removing original SQL file");
                        unlink($filepath);
                        $filepath = $compressedFile;
                        $filename = basename($compressedFile);
                        
                        if (!file_exists($filepath)) {
                            throw new \Exception('Compressed backup file was not created successfully');
                        }
                        
                        $fileSize = filesize($filepath);
                        if ($fileSize === false) {
                            throw new \Exception('Cannot determine compressed backup file size');
                        }
                        $fileSize = (int)$fileSize;
                        \App\Helpers\LogHelper::info("Database Backup: Compressed file size: " . $fileSize . " bytes");
                    } else {
                        \App\Helpers\LogHelper::error("Database Backup: Compression failed, keeping original SQL file");
                    }
                }
                
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'file_size' => $fileSize
                ];
            }
            
            throw new \Exception('Database export failed');
            
        } catch (\Exception $e) {
            // Clean up failed backup file
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            throw $e;
        }
    }
    
    /**
     * Create file backup
     */
    private function createFileBackup($jobId, $options = []) {
        $filename = 'files_' . date('Y-m-d_H-i-s') . '.zip';
        $filepath = $this->backupDir . '/' . $filename;
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($filepath, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception('Cannot create ZIP file: ' . $filepath);
            }
            
            // Define paths to backup
            $pathsToBackup = $this->getFileBackupPaths($options);
            
            foreach ($pathsToBackup as $path) {
                $this->addToZip($zip, $path);
            }
            
            $zip->close();
            
            // Verify file was created and get size
            if (!file_exists($filepath)) {
                throw new \Exception('File backup was not created successfully');
            }
            
            $fileSize = filesize($filepath);
            if ($fileSize === false) {
                throw new \Exception('Cannot determine file backup size');
            }
            
            // Ensure fileSize is a proper integer (never empty string)
            $fileSize = (int)$fileSize;
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'file_size' => $fileSize
            ];
            
        } catch (\Exception $e) {
            if (isset($zip)) {
                $zip->close();
            }
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            throw $e;
        }
    }
    
    /**
     * Create full backup (database + files)
     */
    private function createFullBackup($jobId, $options = []) {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = 'full_backup_' . $timestamp . '.zip';
        $filepath = $this->backupDir . '/' . $filename;
        
        try {
            // Ensure backup directory is writable
            if (!is_writable($this->backupDir)) {
                throw new \Exception('Backup directory is not writable: ' . $this->backupDir);
            }
            
            // Create ZIP directly at final location
            $zip = new ZipArchive();
            $zipResult = $zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            if ($zipResult !== TRUE) {
                throw new \Exception('Cannot create ZIP file: ' . $filepath . ' (Error code: ' . $zipResult . ')');
            }
            
            \App\Helpers\LogHelper::info("Full Backup: Created ZIP file directly: " . $filepath);
            
            // 1. Create database backup
            $dbBackup = $this->createTempDatabaseBackup($options);
            \App\Helpers\LogHelper::info("Full Backup: Created temp database file: " . ($dbBackup ?: 'FAILED'));
            
            if ($dbBackup && file_exists($dbBackup)) {
                \App\Helpers\LogHelper::info("Full Backup: Adding database file to ZIP, size: " . filesize($dbBackup) . " bytes");
                $result = $zip->addFile($dbBackup, 'database.sql');
                \App\Helpers\LogHelper::info("Full Backup: addFile result: " . ($result ? 'SUCCESS' : 'FAILED'));
                
                if (!$result) {
                    throw new \Exception('Failed to add database file to ZIP archive');
                }
            } else {
                \App\Helpers\LogHelper::warning("Full Backup: No database backup created, continuing with files only");
            }
            
            // 2. Add files progressively with memory management
            $pathsToBackup = $this->getFileBackupPaths($options);
            \App\Helpers\LogHelper::info("Full Backup: Adding " . count($pathsToBackup) . " file paths to ZIP");
            
            $filesAdded = 0;
            foreach ($pathsToBackup as $path) {
                try {
                    $this->addToZipProgressive($zip, $path);
                    $filesAdded++;
                    
                    // Force garbage collection every 100 files to manage memory
                    if ($filesAdded % 100 === 0) {
                        gc_collect_cycles();
                        \App\Helpers\LogHelper::debug("Full Backup: Memory management - processed $filesAdded paths");
                    }
                    
                } catch (\Exception $e) {
                    \App\Helpers\LogHelper::warning("Full Backup: Failed to add path to ZIP: " . $path . " - " . $e->getMessage());
                }
            }
            
            \App\Helpers\LogHelper::info("Full Backup: Successfully processed $filesAdded file paths");
            
            // 3. Close ZIP archive with proper error handling
            \App\Helpers\LogHelper::info("Full Backup: Closing ZIP archive");
            
            // Force finalizing before close
            $zip->setArchiveComment("swCMS Full Backup - Created: " . date('Y-m-d H:i:s'));
            
            $closeResult = false;
            $maxAttempts = 3;
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                \App\Helpers\LogHelper::info("Full Backup: ZIP close attempt $attempt/$maxAttempts");
                
                $closeResult = @$zip->close();
                if ($closeResult) {
                    \App\Helpers\LogHelper::info("Full Backup: ZIP close successful on attempt $attempt");
                    break;
                } else {
                    $error = error_get_last();
                    \App\Helpers\LogHelper::warning("Full Backup: ZIP close attempt $attempt failed: " . ($error['message'] ?? 'Unknown error'));
                    
                    if ($attempt < $maxAttempts) {
                        sleep(1); // Wait before retry
                        gc_collect_cycles(); // Force garbage collection
                    }
                }
            }
            
            if (!$closeResult) {
                // If close failed, try alternative approach
                \App\Helpers\LogHelper::warning("Full Backup: All close attempts failed, trying alternative approach");
                
                // Unset the zip object to force close
                unset($zip);
                gc_collect_cycles();
                
                // Check if file was created anyway
                if (file_exists($filepath) && filesize($filepath) > 0) {
                    \App\Helpers\LogHelper::info("Full Backup: ZIP file exists despite close failure, continuing");
                    $closeResult = true;
                } else {
                    throw new \Exception('Failed to close ZIP archive after ' . $maxAttempts . ' attempts');
                }
            }
            
            // 4. Clean up temp database file
            if ($dbBackup && file_exists($dbBackup)) {
                \App\Helpers\LogHelper::info("Full Backup: Cleaning up temp database file");
                unlink($dbBackup);
            }
            
            // 5. Verify final file
            if (!file_exists($filepath)) {
                throw new \Exception('Final backup file was not created');
            }
            
            $finalFileSize = filesize($filepath);
            if ($finalFileSize === false || $finalFileSize === 0) {
                throw new \Exception('Final backup file is empty or unreadable');
            }
            
            \App\Helpers\LogHelper::info("Full Backup: Final file size: " . $finalFileSize . " bytes");
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'file_size' => (int)$finalFileSize
            ];
            
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error("Full Backup Error: " . $e->getMessage());
            
            // Clean up any files
            if (isset($zip) && $zip instanceof ZipArchive) {
                @$zip->close();
            }
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
            if (isset($dbBackup) && file_exists($dbBackup)) {
                @unlink($dbBackup);
            }
            
            throw $e;
        }
    }
    
    /**
     * Export MySQL database
     */
    private function exportMySQLDatabase($filepath, $dbConfig, $options = []) {
        $output = '';
        
        try {
            // Get all tables
            $stmt = $this->db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            \App\Helpers\LogHelper::info("MySQL Export: Found " . count($tables) . " tables: " . implode(', ', $tables));
            
            if (empty($tables)) {
                \App\Helpers\LogHelper::error("MySQL Export: No tables found in database");
                return false;
            }
            
            // Export header
            $output .= "-- swCMS Database Backup\n";
            $output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
            $output .= "-- Database: " . $dbConfig['name'] . "\n\n";
            $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $output .= "SET time_zone = \"+00:00\";\n\n";
        
            foreach ($tables as $table) {
                \App\Helpers\LogHelper::info("MySQL Export: Processing table $table");
                
                // Table structure
                $stmt = $this->db->query("SHOW CREATE TABLE `$table`");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $output .= "\n-- Table structure for table `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $output .= $row['Create Table'] . ";\n\n";
                
                // Table data
                $stmt = $this->db->query("SELECT * FROM `$table`");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                \App\Helpers\LogHelper::info("MySQL Export: Table $table has " . count($rows) . " rows");
                
                if (!empty($rows)) {
                    $output .= "-- Dumping data for table `$table`\n";
                    
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $output .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            \App\Helpers\LogHelper::info("MySQL Export: Generated SQL content length: " . strlen($output));
            
            $result = file_put_contents($filepath, $output);
            if ($result === false) {
                \App\Helpers\LogHelper::error("MySQL Export: Failed to write to file: $filepath");
                return false;
            }
            
            \App\Helpers\LogHelper::info("MySQL Export: Successfully wrote " . $result . " bytes to $filepath");
            return true;
            
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error("MySQL Export Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Export SQLite database
     */
    private function exportSQLiteDatabase($filepath, $dbConfig, $options = []) {
        // For SQLite, we can copy the file directly or export to SQL
        if (isset($options['format']) && $options['format'] === 'copy') {
            return copy($dbConfig['path'], $filepath);
        }
        
        // Export to SQL format
        $output = '';
        
        try {
            // Get all tables
            $stmt = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            error_log("SQLite Export: Found " . count($tables) . " tables: " . implode(', ', $tables));
            
            if (empty($tables)) {
                error_log("SQLite Export: No tables found in database");
                return false;
            }
            
            // Export header
            $output .= "-- swCMS SQLite Database Backup\n";
            $output .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
            
            foreach ($tables as $table) {
                // Table structure
                $stmt = $this->db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'");
                $createSql = $stmt->fetchColumn();
                
                $output .= "\n-- Table structure for table `$table`\n";
                $output .= "DROP TABLE IF EXISTS `$table`;\n";
                $output .= $createSql . ";\n\n";
                
                // Table data
                $stmt = $this->db->query("SELECT * FROM `$table`");
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $output .= "-- Dumping data for table `$table`\n";
                    
                    foreach ($rows as $row) {
                        $columns = array_keys($row);
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . str_replace("'", "''", $value) . "'";
                            }
                        }
                        $output .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            $result = file_put_contents($filepath, $output);
            if ($result === false) {
                error_log("SQLite Export: Failed to write to file: $filepath");
                return false;
            }
            
            error_log("SQLite Export: Successfully wrote " . $result . " bytes to $filepath");
            return true;
            
        } catch (\Exception $e) {
            error_log("SQLite Export Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get file backup paths
     */
    private function getFileBackupPaths($options = []) {
        $paths = [];
        
        // Core application files
        $paths[] = ROOT_PATH . '/app';
        $paths[] = ROOT_PATH . '/public';
        
        // Optional paths based on settings
        if ($this->settings['include_uploads']) {
            $uploadsPath = PUBLIC_PATH . '/uploads';
            if (is_dir($uploadsPath)) {
                $paths[] = $uploadsPath;
            }
        }
        
        if ($this->settings['include_themes']) {
            $themesPath = PUBLIC_PATH . '/themes';
            if (is_dir($themesPath)) {
                $paths[] = $themesPath;
            }
        }
        
        if ($this->settings['include_plugins']) {
            $pluginsPath = ROOT_PATH . '/plugins';
            if (is_dir($pluginsPath)) {
                $paths[] = $pluginsPath;
            }
        }
        
        // Additional paths from options
        if (isset($options['additional_paths'])) {
            $paths = array_merge($paths, $options['additional_paths']);
        }
        
        return $paths;
    }
    
    /**
     * Add files/directories to ZIP archive with progressive processing
     */
    private function addToZipProgressive($zip, $path, $basePath = '') {
        if (!$basePath) {
            $basePath = ROOT_PATH;
        }
        
        // Check if path exists and is readable
        if (!file_exists($path) || !is_readable($path)) {
            \App\Helpers\LogHelper::warning("addToZipProgressive: Path does not exist or is not readable: " . $path);
            return;
        }
        
        if (is_file($path)) {
            $relativePath = str_replace($basePath . '/', '', $path);
            $result = $zip->addFile($path, $relativePath);
            if (!$result) {
                \App\Helpers\LogHelper::warning("addToZipProgressive: Failed to add file: " . $relativePath);
            }
            
        } elseif (is_dir($path)) {
            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                
                $filesProcessed = 0;
                $batchSize = 50; // Process in smaller batches
                
                foreach ($iterator as $file) {
                    try {
                        if ($this->shouldExcludeFile($file->getPathname())) {
                            continue;
                        }
                        
                        // Check if file is readable
                        if (!is_readable($file->getPathname())) {
                            continue;
                        }
                        
                        $relativePath = str_replace($basePath . '/', '', $file->getPathname());
                        
                        if ($file->isDir()) {
                            $zip->addEmptyDir($relativePath);
                        } elseif ($file->isFile()) {
                            $zip->addFile($file->getPathname(), $relativePath);
                        }
                        
                        $filesProcessed++;
                        
                        // Memory management every batch
                        if ($filesProcessed % $batchSize === 0) {
                            gc_collect_cycles();
                        }
                        
                    } catch (\Exception $e) {
                        \App\Helpers\LogHelper::debug("addToZipProgressive: Skipping file " . $file->getPathname() . ": " . $e->getMessage());
                    }
                }
                
                \App\Helpers\LogHelper::info("addToZipProgressive: Processed directory " . $path . " - Added: $filesProcessed files");
                
            } catch (\Exception $e) {
                \App\Helpers\LogHelper::warning("addToZipProgressive: Failed to process directory " . $path . ": " . $e->getMessage());
            }
        }
    }

    /**
     * Add files/directories to ZIP archive
     */
    private function addToZip($zip, $path, $basePath = '') {
        if (!$basePath) {
            $basePath = ROOT_PATH;
        }
        
        // Check if path exists and is readable
        if (!file_exists($path) || !is_readable($path)) {
            \App\Helpers\LogHelper::warning("addToZip: Path does not exist or is not readable: " . $path);
            return;
        }
        
        if (is_file($path)) {
            $relativePath = str_replace($basePath . '/', '', $path);
            $result = $zip->addFile($path, $relativePath);
            if (!$result) {
                throw new \Exception("Failed to add file to ZIP: " . $path);
            }
            \App\Helpers\LogHelper::debug("addToZip: Added file: " . $relativePath);
            
        } elseif (is_dir($path)) {
            try {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::SELF_FIRST
                );
                
                $filesProcessed = 0;
                $filesSkipped = 0;
                
                foreach ($files as $file) {
                    try {
                        if ($this->shouldExcludeFile($file->getPathname())) {
                            $filesSkipped++;
                            continue;
                        }
                        
                        // Check if file is readable
                        if (!is_readable($file->getPathname())) {
                            \App\Helpers\LogHelper::warning("addToZip: File not readable, skipping: " . $file->getPathname());
                            $filesSkipped++;
                            continue;
                        }
                        
                        $relativePath = str_replace($basePath . '/', '', $file->getPathname());
                        
                        if ($file->isDir()) {
                            $result = $zip->addEmptyDir($relativePath);
                            if ($result) {
                                $filesProcessed++;
                            } else {
                                \App\Helpers\LogHelper::warning("addToZip: Failed to add directory: " . $relativePath);
                                $filesSkipped++;
                            }
                        } elseif ($file->isFile()) {
                            $result = $zip->addFile($file->getPathname(), $relativePath);
                            if ($result) {
                                $filesProcessed++;
                            } else {
                                \App\Helpers\LogHelper::warning("addToZip: Failed to add file: " . $relativePath);
                                $filesSkipped++;
                            }
                        }
                        
                    } catch (\Exception $e) {
                        \App\Helpers\LogHelper::warning("addToZip: Error processing file " . $file->getPathname() . ": " . $e->getMessage());
                        $filesSkipped++;
                    }
                }
                
                \App\Helpers\LogHelper::info("addToZip: Processed directory " . $path . " - Added: $filesProcessed, Skipped: $filesSkipped");
                
            } catch (\Exception $e) {
                throw new \Exception("Failed to process directory " . $path . ": " . $e->getMessage());
            }
        }
    }
    
    /**
     * Check if file should be excluded from backup
     */
    private function shouldExcludeFile($filepath) {
        $excludePatterns = $this->settings['exclude_patterns'];
        
        foreach ($excludePatterns as $pattern) {
            if (fnmatch($pattern, $filepath) || strpos($filepath, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Compress file using gzip
     */
    private function compressFile($filepath) {
        $compressedFile = $filepath . '.gz';
        
        \App\Helpers\LogHelper::info("Compression: Input file: $filepath, Output file: $compressedFile");
        
        if (!file_exists($filepath)) {
            \App\Helpers\LogHelper::error("Compression: Input file does not exist: $filepath");
            return false;
        }
        
        $inputSize = filesize($filepath);
        \App\Helpers\LogHelper::info("Compression: Input file size: $inputSize bytes");
        
        $src = fopen($filepath, 'rb');
        $dst = gzopen($compressedFile, 'wb' . $this->settings['compression_level']);
        
        if (!$src) {
            \App\Helpers\LogHelper::error("Compression: Failed to open source file: $filepath");
            return false;
        }
        
        if (!$dst) {
            \App\Helpers\LogHelper::error("Compression: Failed to open destination file: $compressedFile");
            fclose($src);
            return false;
        }
        
        $bytesWritten = 0;
        while (!feof($src)) {
            $data = fread($src, 8192);
            $written = gzwrite($dst, $data);
            $bytesWritten += $written;
        }
        
        fclose($src);
        gzclose($dst);
        
        \App\Helpers\LogHelper::info("Compression: Bytes written to compressed file: $bytesWritten");
        
        if (file_exists($compressedFile)) {
            $compressedSize = filesize($compressedFile);
            \App\Helpers\LogHelper::info("Compression: Final compressed file size: $compressedSize bytes");
            return $compressedFile;
        } else {
            \App\Helpers\LogHelper::error("Compression: Compressed file was not created: $compressedFile");
            return false;
        }
    }
    
    /**
     * Create temporary database backup for full backup
     */
    private function createTempDatabaseBackup($options = []) {
        $tempFile = $this->backupDir . '/temp_db_' . uniqid() . '.sql';
        $dbConfig = $this->getDatabaseConfig();
        
        if ($dbConfig['driver'] === 'mysql') {
            $result = $this->exportMySQLDatabase($tempFile, $dbConfig, $options);
        } else {
            $result = $this->exportSQLiteDatabase($tempFile, $dbConfig, $options);
        }
        
        return $result ? $tempFile : false;
    }
    
    /**
     * Create backup job record
     */
    private function createBackupJob($type, $options = []) {
        $stmt = $this->db->prepare("
            INSERT INTO backup_jobs (type, status, settings, created_by) 
            VALUES (?, 'pending', ?, ?)
        ");
        
        $stmt->execute([
            $type,
            json_encode($options),
            $_SESSION['user_id'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update backup job status
     */
    private function updateJobStatus($jobId, $status, $data = [], $errorMessage = null) {
        $sql = "UPDATE backup_jobs SET status = ?, completed_at = ?";
        $params = [$status, ($status === 'completed' ? date('Y-m-d H:i:s') : null)];
        
        if (!empty($data)) {
            if (isset($data['filename'])) {
                $sql .= ", filename = ?";
                $params[] = $data['filename'];
            }
            if (isset($data['file_size'])) {
                $sql .= ", file_size = ?";
                // Ensure file_size is always an integer, never empty string
                $fileSize = $data['file_size'];
                if ($fileSize === '' || $fileSize === null || $fileSize === false) {
                    $fileSize = 0;
                }
                $params[] = (int)$fileSize;
            }
        }
        
        if ($errorMessage) {
            $sql .= ", error_message = ?";
            $params[] = $errorMessage;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $jobId;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }
    
    /**
     * Get database configuration
     */
    private function getDatabaseConfig() {
        if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
            return [
                'driver' => 'mysql',
                'host' => DB_HOST,
                'name' => DB_NAME,
                'user' => DB_USER,
                'password' => DB_PASS
            ];
        } else {
            return [
                'driver' => 'sqlite',
                'path' => defined('DB_SQLITE_PATH') ? DB_SQLITE_PATH : ROOT_PATH . '/data/database.sqlite'
            ];
        }
    }
    
    /**
     * Get backup settings
     */
    private function getSettings() {
        $settings = \App\Helpers\SystemSettingsHelper::get('PLUGIN_BACKUP_MANAGER_SETTINGS');
        return $settings ? json_decode($settings, true) : $this->getDefaultSettings();
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings() {
        return [
            'compression_level' => 6,
            'exclude_patterns' => [
                'node_modules/*',
                '.git/*',
                'vendor/*',
                '*.log',
                'cache/*',
                'tmp/*',
                'backups/*'
            ],
            'include_uploads' => true,
            'include_themes' => true,
            'include_plugins' => false
        ];
    }
    
    /**
     * Get backup list
     */
    public function getBackupList($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM backup_jobs 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete backup
     */
    public function deleteBackup($backupId) {
        // Get backup info
        $stmt = $this->db->prepare("SELECT filename FROM backup_jobs WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($backup) {
            // Delete file if filename exists and is not empty
            if (!empty($backup['filename'])) {
                $filepath = $this->backupDir . '/' . $backup['filename'];
                
                // Extra safety check - ensure we're not trying to delete a directory
                if (file_exists($filepath) && is_file($filepath)) {
                    // Use error suppression and explicit error handling to prevent output
                    if (!@unlink($filepath)) {
                        error_log("Failed to delete backup file: " . $filepath);
                    }
                } elseif (file_exists($filepath) && is_dir($filepath)) {
                    error_log("Warning: Attempted to delete directory instead of file: " . $filepath);
                } elseif (file_exists($filepath)) {
                    error_log("Warning: File exists but is neither file nor directory: " . $filepath);
                }
            } else {
                error_log("Warning: Attempted to delete backup with empty filename. Backup ID: " . $backupId);
            }
            
            // Delete record
            $stmt = $this->db->prepare("DELETE FROM backup_jobs WHERE id = ?");
            $stmt->execute([$backupId]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get backup file path
     */
    public function getBackupFilePath($backupId) {
        $stmt = $this->db->prepare("SELECT filename FROM backup_jobs WHERE id = ? AND status = 'completed'");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($backup) {
            $filepath = $this->backupDir . '/' . $backup['filename'];
            return file_exists($filepath) ? $filepath : false;
        }
        
        return false;
    }
    
    /**
     * Clean old backups
     */
    public function cleanOldBackups($retentionDays = 30) {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$retentionDays} days"));
        
        // Get old backups
        $stmt = $this->db->prepare("
            SELECT id, filename FROM backup_jobs 
            WHERE created_at < ? AND status = 'completed'
        ");
        $stmt->execute([$cutoffDate]);
        $oldBackups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $deleted = 0;
        foreach ($oldBackups as $backup) {
            if ($this->deleteBackup($backup['id'])) {
                $deleted++;
            }
        }
        
        // Also clean up temporary and orphaned files
        $this->cleanupTemporaryFiles();
        
        return $deleted;
    }
    
    /**
     * Clean up temporary files and orphaned backup files
     */
    public function cleanupTemporaryFiles() {
        $cleaned = 0;
        
        try {
            // Get all files in backup directory
            $files = glob($this->backupDir . '/*');
            
            foreach ($files as $file) {
                $filename = basename($file);
                
                // Skip directories and protected files
                if (is_dir($file) || $filename === 'index.php' || $filename === '.htaccess') {
                    continue;
                }
                
                // Clean up temporary files (with extensions like .tmp, .part, or random suffixes)
                if (preg_match('/\.(tmp|part|[a-z0-9]{6,})$/i', $filename) || 
                    strpos($filename, 'temp_') === 0) {
                    
                    if (@unlink($file)) {
                        \App\Helpers\LogHelper::info("Cleanup: Removed temporary file: " . $filename);
                        $cleaned++;
                    } else {
                        \App\Helpers\LogHelper::warning("Cleanup: Could not remove temporary file: " . $filename);
                    }
                }
                
                // Check for orphaned backup files (files not in database)
                elseif (preg_match('/\.(zip|gz|sql)$/i', $filename)) {
                    $stmt = $this->db->prepare("SELECT id FROM backup_jobs WHERE filename = ?");
                    $stmt->execute([$filename]);
                    
                    if (!$stmt->fetch()) {
                        // File not in database, consider it orphaned
                        if (@unlink($file)) {
                            \App\Helpers\LogHelper::info("Cleanup: Removed orphaned backup file: " . $filename);
                            $cleaned++;
                        } else {
                            \App\Helpers\LogHelper::warning("Cleanup: Could not remove orphaned file: " . $filename);
                        }
                    }
                }
            }
            
            \App\Helpers\LogHelper::info("Cleanup: Processed $cleaned temporary/orphaned files");
            
        } catch (\Exception $e) {
            \App\Helpers\LogHelper::error("Cleanup error: " . $e->getMessage());
        }
        
        return $cleaned;
    }
}
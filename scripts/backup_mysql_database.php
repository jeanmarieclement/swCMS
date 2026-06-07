<?php
/**
 * Backup script to create a full MySQL database backup
 * This script creates a complete backup of the MySQL database
 */

echo "Creating MySQL backup...\n";

// Database connection parameters
$host = 'db';
$dbname = 'swcms';
$username = 'swcms_user';
$password = 'swcms_password';

$backupFile = '/var/www/html/data/mysql_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Create mysqldump command
$command = sprintf(
    'mysqldump -h %s -u %s -p%s %s > %s 2>/dev/null',
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($password),
    escapeshellarg($dbname),
    escapeshellarg($backupFile)
);

// Execute backup
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "MySQL backup created successfully: $backupFile\n";
    echo "File size: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n";
} else {
    echo "Backup failed with error code: $returnCode\n";
    exit(1);
}

echo "You can now safely use SQLite. The MySQL backup is available for rollback if needed.\n";
?>
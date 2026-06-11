<?php
/**
 * Backup script to create a full MySQL database backup
 * Reads connection settings from .env (same source as app/Config/config.php)
 */

echo "Creating MySQL backup...\n";

$envFile = dirname(__DIR__) . '/.env';
if (!is_file($envFile)) {
    echo "Error: .env file not found at $envFile\n";
    exit(1);
}

$env = parse_ini_file($envFile);
$host = $env['DB_HOST'] ?? 'db';
$dbname = $env['DB_NAME'] ?? 'swcms';
$username = $env['DB_USER'] ?? '';
$password = $env['DB_PASS'] ?? '';

if ($username === '') {
    echo "Error: DB_USER not set in .env\n";
    exit(1);
}

$backupFile = dirname(__DIR__) . '/data/mysql_backup_' . date('Y-m-d_H-i-s') . '.sql';

// MYSQL_PWD keeps the password out of the process list (visible via `ps` with -p<pass>)
$command = sprintf(
    'MYSQL_PWD=%s mysqldump -h %s -u %s %s > %s 2>/dev/null',
    escapeshellarg($password),
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($dbname),
    escapeshellarg($backupFile)
);

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "MySQL backup created successfully: $backupFile\n";
    echo "File size: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n";
} else {
    echo "Backup failed with error code: $returnCode\n";
    exit(1);
}

echo "You can now safely use SQLite. The MySQL backup is available for rollback if needed.\n";

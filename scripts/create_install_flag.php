<?php
/**
 * Utility script to manually create installation flag
 * Run this script if you need to bypass the installer or mark installation as complete
 */

require_once dirname(__DIR__) . '/app/Config/install_config.php';

$flagPath = ROOT_PATH . '/data/.installed';
$flagContent = json_encode([
    'installed_at' => date('Y-m-d H:i:s'),
    'version' => '1.0.0',
    'installer_ip' => $_SERVER['REMOTE_ADDR'] ?? 'manual',
    'manual_install' => true
]);

if (file_put_contents($flagPath, $flagContent)) {
    echo "Installation flag created successfully at: $flagPath\n";
    echo "The installation wizard will no longer run.\n";
} else {
    echo "Error: Could not create installation flag at: $flagPath\n";
    echo "Please check directory permissions.\n";
}
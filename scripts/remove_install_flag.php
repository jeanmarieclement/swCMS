<?php
/**
 * Utility script to remove installation flag
 * Run this script if you need to re-run the installer
 */

require_once dirname(__DIR__) . '/app/Config/install_config.php';

$flagPath = ROOT_PATH . '/data/.installed';

if (file_exists($flagPath)) {
    if (unlink($flagPath)) {
        echo "Installation flag removed successfully from: $flagPath\n";
        echo "The installation wizard will run on next page load.\n";
    } else {
        echo "Error: Could not remove installation flag from: $flagPath\n";
        echo "Please check file permissions.\n";
    }
} else {
    echo "Installation flag does not exist at: $flagPath\n";
    echo "The installation wizard should run on next page load.\n";
}
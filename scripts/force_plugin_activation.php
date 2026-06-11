<?php

// Force plugin activation
define('APP_PATH', __DIR__ . '/app');
define('PUBLIC_PATH', __DIR__ . '/public');
define('ROOT_PATH', __DIR__);

require_once __DIR__ . '/app/Config/Config.php';
require_once __DIR__ . '/app/Core/Database/Database.php';
require_once __DIR__ . '/app/Helpers/SystemSettingsHelper.php';
require_once __DIR__ . '/app/Helpers/LogHelper.php';
require_once __DIR__ . '/app/Services/PluginService.php';

use App\Services\PluginService;
use App\Helpers\SystemSettingsHelper;

echo "🔄 Force Plugin Activation Test\n";
echo "===============================\n\n";

try {
    // First deactivate the plugin to clean state
    echo "1️⃣ Deactivating backup-manager...\n";
    $pluginService = new PluginService();
    
    $activePlugins = SystemSettingsHelper::get('ACTIVE_PLUGINS');
    $activePlugins = $activePlugins ? json_decode($activePlugins, true) : [];
    
    if (in_array('backup-manager', $activePlugins)) {
        $result = $pluginService->deactivatePlugin('backup-manager');
        echo $result ? "✅ Deactivated\n" : "❌ Failed to deactivate\n";
    } else {
        echo "ℹ️ Already inactive\n";
    }
    
    echo "\n2️⃣ Activating backup-manager...\n";
    $result = $pluginService->activatePlugin('backup-manager');
    echo $result ? "✅ Activated\n" : "❌ Failed to activate\n";
    
    echo "\n3️⃣ Checking routes file...\n";
    $routesFile = __DIR__ . '/app/Core/plugin_routes_include.php';
    if (file_exists($routesFile)) {
        $content = file_get_contents($routesFile);
        echo "File size: " . strlen($content) . " bytes\n";
        echo "Content preview:\n";
        echo "---\n";
        echo $content;
        echo "---\n";
    } else {
        echo "❌ Routes file not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
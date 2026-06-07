<?php

// Script per rigenerare le route dei plugin

require_once __DIR__ . '/App/Core/Database/Database.php';
require_once __DIR__ . '/App/Services/PluginRoutesManager.php';
require_once __DIR__ . '/App/Helpers/LogHelper.php';
require_once __DIR__ . '/App/Helpers/SystemSettingsHelper.php';

use App\Services\PluginRoutesManager;
use App\Helpers\SystemSettingsHelper;

echo "🔄 Rigenerazione route plugin\n";
echo "============================\n\n";

try {
    $routesManager = new PluginRoutesManager();
    
    // Ottieni plugin attivi
    $activePlugins = SystemSettingsHelper::get('ACTIVE_PLUGINS');
    $activePlugins = $activePlugins ? json_decode($activePlugins, true) : [];
    
    echo "Plugin attivi trovati: " . count($activePlugins) . "\n";
    foreach ($activePlugins as $plugin) {
        echo "  - {$plugin}\n";
    }
    echo "\n";
    
    // Per ogni plugin attivo, genera le route
    foreach ($activePlugins as $pluginName) {
        echo "📦 Processando plugin: {$pluginName}\n";
        
        // Verifica controller
        $hasController = $routesManager->hasController($pluginName);
        echo "  Controller esistente: " . ($hasController ? "✅ Sì" : "❌ No") . "\n";
        
        if ($hasController) {
            // Genera route
            $pluginPath = __DIR__ . '/plugins/' . $pluginName;
            $routes = $routesManager->generatePluginRoutes($pluginName, $pluginPath);
            
            echo "  Route generate: " . count($routes) . "\n";
            foreach ($routes as $route) {
                echo "    - {$route['pattern']} -> {$route['controller']}::{$route['action']}\n";
            }
            
            // Registra le route
            $routesManager->registerPluginRoutes($pluginName, $routes);
            echo "  ✅ Route registrate\n";
        }
        
        echo "\n";
    }
    
    // Genera il file finale
    echo "📄 Generazione file route finale...\n";
    $result = $routesManager->generateRoutesFile();
    echo $result ? "✅ File generato con successo\n" : "❌ Errore nella generazione\n";
    
    // Mostra il contenuto del file generato
    $routesFile = __DIR__ . '/App/Core/plugin_routes_include.php';
    if (file_exists($routesFile)) {
        echo "\n📋 Contenuto file route:\n";
        echo "========================\n";
        $content = file_get_contents($routesFile);
        echo $content;
    }
    
    echo "\n🎉 Rigenerazione completata!\n";
    
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
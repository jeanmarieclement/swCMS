<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Helpers\SystemSettingsHelper;

/**
 * Plugin Routes Manager
 * Gestisce automaticamente le route dei plugin
 */
class PluginRoutesManager
{
    private $routesFile;

    private $pluginsPath;

    /**
     * @param string|null $pluginsPath Plugin directory, defaults to the bundled one
     */
    public function __construct(?string $pluginsPath = null)
    {
        $this->routesFile = __DIR__ . '/../core/plugin_routes.php';
        $this->pluginsPath = $pluginsPath === null
            ? __DIR__ . '/../../plugins/'
            : rtrim($pluginsPath, '/') . '/';
    }

    /**
     * Registra le route di un plugin
     * @param string $pluginName Nome del plugin
     * @param array $routes Array delle route
     * @return bool Success status
     */
    public function registerPluginRoutes(string $pluginName, array $routes): bool
    {
        try {
            $currentRoutes = $this->loadPluginRoutes();
            $currentRoutes[$pluginName] = $routes;

            $this->savePluginRoutes($currentRoutes);

            LogHelper::info('Plugin routes registered', [
                'plugin' => $pluginName,
                'routes_count' => count($routes)
            ]);

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Failed to register plugin routes', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Rimuove le route di un plugin
     * @param string $pluginName Nome del plugin
     * @return bool Success status
     */
    public function unregisterPluginRoutes(string $pluginName): bool
    {
        try {
            $currentRoutes = $this->loadPluginRoutes();

            if (isset($currentRoutes[$pluginName])) {
                unset($currentRoutes[$pluginName]);
                $this->savePluginRoutes($currentRoutes);

                LogHelper::info('Plugin routes unregistered', ['plugin' => $pluginName]);
            }

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Failed to unregister plugin routes', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Ottiene tutte le route dei plugin attivi
     * @return array Route dei plugin attivi
     */
    public function getActivePluginRoutes(): array
    {
        $activePlugins = $this->getActivePlugins();
        $allRoutes = $this->loadPluginRoutes();
        $activeRoutes = [];

        \App\Helpers\LogHelper::info("Loading active plugin routes", [
            'active_plugins' => $activePlugins,
            'all_routes_plugins' => array_keys($allRoutes),
            'all_routes_count' => count($allRoutes)
        ]);

        foreach ($activePlugins as $pluginName) {
            if (isset($allRoutes[$pluginName])) {
                $activeRoutes[$pluginName] = $allRoutes[$pluginName];
            }
        }

        \App\Helpers\LogHelper::info("Active plugin routes result", [
            'active_routes_plugins' => array_keys($activeRoutes),
            'active_routes_count' => count($activeRoutes)
        ]);

        return $activeRoutes;
    }

    /**
     * Genera route automatiche per un plugin
     * @param string $pluginName Nome del plugin
     * @param string $pluginPath Percorso del plugin
     * @return array Route generate
     */
    public function generatePluginRoutes(string $pluginName, string $pluginPath): array
    {
        $routes = [];
        $baseUrl = str_replace('_', '-', $pluginName);
        $controllerName = $this->getControllerName($pluginName);

        // Route base
        $routes[] = [
            'pattern' => "admin/{$baseUrl}",
            'controller' => $controllerName,
            'action' => 'index'
        ];

        // Route comuni
        $commonActions = [
            'create',
            'edit',
            'delete',
            'settings',
            'download',
            'upload',
            'schedules',
            'schedule',
            'list',
            'stats',
            'restore',
            'cleanup',
        ];

        foreach ($commonActions as $action) {
            $routes[] = [
                'pattern' => "admin/{$baseUrl}/{$action}",
                'controller' => $controllerName,
                'action' => $action
            ];

            // Route con parametri ID
            if (in_array($action, ['edit', 'delete', 'view'])) {
                $routes[] = [
                    'pattern' => "admin/{$baseUrl}/{$action}/([0-9]+)",
                    'controller' => $controllerName,
                    'action' => $action
                ];
            }
        }

        // Route personalizzate dal file del plugin
        $customRoutes = $this->getCustomRoutesFromPlugin($pluginName, $pluginPath);
        if (!empty($customRoutes)) {
            $routes = array_merge($routes, $customRoutes);
        }

        return $routes;
    }

    /**
     * Carica le route dei plugin dal file
     * @return array Route caricate
     */
    private function loadPluginRoutes(): array
    {
        if (!file_exists($this->routesFile)) {
            return [];
        }

        $content = file_get_contents($this->routesFile);
        $data = json_decode($content, true);

        return $data ?: [];
    }

    /**
     * Salva le route dei plugin nel file
     * @param array $routes Route da salvare
     */
    private function savePluginRoutes(array $routes): void
    {
        $content = json_encode($routes, JSON_PRETTY_PRINT);
        file_put_contents($this->routesFile, $content);
    }

    /**
     * Ottiene il nome del controller per un plugin
     * @param string $pluginName Nome del plugin
     * @return string Nome del controller
     */
    private function getControllerName(string $pluginName): string
    {
        // Converte plugin-name in PluginNameController
        // Esempio: backup-manager -> BackupManager
        $parts = explode('-', $pluginName);

        $controllerName = '';
        foreach ($parts as $part) {
            // Gestisce anche underscore all'interno di ogni parte
            $subParts = explode('_', $part);
            foreach ($subParts as $subPart) {
                $controllerName .= ucfirst(trim($subPart));
            }
        }

        return $controllerName;
    }

    /**
     * Ottiene route personalizzate dal file del plugin
     * @param string $pluginName Nome del plugin
     * @param string $pluginPath Percorso del plugin
     * @return array Route personalizzate
     */
    private function getCustomRoutesFromPlugin(string $pluginName, string $pluginPath): array
    {
        $mainFile = $pluginPath . '/' . $pluginName . '.php';

        if (!file_exists($mainFile)) {
            return [];
        }

        $content = file_get_contents($mainFile);

        // Cerca configurazione route nei commenti
        if (preg_match('/\/\*\*[\s\S]*?Routes Config:(.*?)(?=\*\/)/s', $content, $matches)) {
            $configStr = trim($matches[1]);

            // Prova a decodificare come JSON
            $config = json_decode($configStr, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($config['routes'])) {
                return $config['routes'];
            }
        }

        return [];
    }

    /**
     * Ottiene i plugin attivi
     * @return array Lista plugin attivi
     */
    private function getActivePlugins(): array
    {
        $activePlugins = SystemSettingsHelper::get('ACTIVE_PLUGINS');
        return $activePlugins ? json_decode($activePlugins, true) : [];
    }

    /**
     * Verifica se esiste un controller per il plugin
     * @param string $pluginName Nome del plugin
     * @return bool True se il controller esiste
     */
    public function hasController(string $pluginName): bool
    {
        $controllerName = $this->getControllerName($pluginName);

        // Both locations the Router already loads a plugin controller from:
        // the admin controllers directory, and the plugin's own controllers/
        // folder, which is the layout plugins/README.md documents.
        $candidates = [
            // Use absolute path instead of constant in case it's not defined
            defined('ADMIN_CONTROLLERS_PATH')
                ? ADMIN_CONTROLLERS_PATH . "/{$controllerName}Controller.php"
                : __DIR__ . "/../controllers/admin/{$controllerName}Controller.php",
            $this->pluginsPath . "{$pluginName}/controllers/{$controllerName}Controller.php",
        ];

        foreach ($candidates as $controllerPath) {
            if (file_exists($controllerPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Crea un file routes.php che può essere incluso dal Router
     * @return bool Success status
     */
    public function generateRoutesFile(): bool
    {
        try {
            $activeRoutes = $this->getActivePluginRoutes();
            $routesCode = "<?php\n\n";
            $routesCode .= "// Auto-generated plugin routes file\n";
            $routesCode .= "// This file is automatically updated when plugins are activated/deactivated\n";
            $routesCode .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($activeRoutes as $pluginName => $routes) {
                $routesCode .= "// Routes for plugin: {$pluginName}\n";

                foreach ($routes as $route) {
                    $pattern = $route['pattern'];
                    $controller = $route['controller'];
                    $action = $route['action'];

                    $routesCode .= "\$this->addRoute('{$pattern}', ['controller' => '{$controller}', 'action' => '{$action}']);\n";
                }

                $routesCode .= "\n";
            }

            $routesFile = __DIR__ . '/../core/plugin_routes_include.php';
            file_put_contents($routesFile, $routesCode);

            LogHelper::info('Plugin routes file generated', [
                'plugins_count' => count($activeRoutes),
                'file' => $routesFile
            ]);

            return true;
        } catch (\Exception $e) {
            LogHelper::error('Failed to generate plugin routes file', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Pulisce route orfane (di plugin non più esistenti)
     * @return bool Success status
     */
    public function cleanupOrphanedRoutes(): bool
    {
        try {
            $allRoutes = $this->loadPluginRoutes();
            $pluginsPath = __DIR__ . '/../../plugins/';
            $cleanedRoutes = [];

            foreach ($allRoutes as $pluginName => $routes) {
                $pluginPath = $pluginsPath . $pluginName;

                // Mantieni le route solo se il plugin esiste ancora
                if (is_dir($pluginPath)) {
                    $cleanedRoutes[$pluginName] = $routes;
                } else {
                    LogHelper::info('Cleaned orphaned routes for missing plugin', [
                        'plugin' => $pluginName
                    ]);
                }
            }

            $this->savePluginRoutes($cleanedRoutes);
            return true;
        } catch (\Exception $e) {
            LogHelper::error('Failed to cleanup orphaned routes', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}

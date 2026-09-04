<?php

namespace App\Services;

use App\Helpers\SystemSettingsHelper;
use App\Core\Version;
use App\Helpers\LogHelper;
use App\Core\Database\Database;
use App\Services\PluginMenuManager;
use App\Services\PluginRoutesManager;

/**
 * Plugin Service
 * Handles plugin operations and management
 */
class PluginService
{
    private $pluginsPath;
    private $db;
    private $menuManager;
    private $routesManager;

    /**
     * @param string|null $pluginsPath Plugin directory, defaults to the bundled one
     */
    public function __construct(?string $pluginsPath = null)
    {
        $this->pluginsPath = $pluginsPath === null
            ? __DIR__ . '/../../plugins/'
            : rtrim($pluginsPath, '/') . '/';
        $this->db = Database::getInstance();
        $this->menuManager = new PluginMenuManager();
        $this->routesManager = new PluginRoutesManager($this->pluginsPath);

        // Create plugins directory if it doesn't exist
        if (!is_dir($this->pluginsPath)) {
            mkdir($this->pluginsPath, 0755, true);
        }
    }

    /**
     * Get all available plugins
     * @return array Array of plugin information
     */
    public function getAvailablePlugins(): array
    {
        $plugins = [];

        if (!is_dir($this->pluginsPath)) {
            LogHelper::warning('Plugins directory not found', ['path' => $this->pluginsPath]);
            return $plugins;
        }

        $directories = scandir($this->pluginsPath);

        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($this->pluginsPath . $dir)) {
                continue;
            }

            $pluginInfo = $this->getPluginDetails($dir);
            if ($pluginInfo) {
                $plugins[] = $pluginInfo;
            }
        }

        return $plugins;
    }

    /**
     * Get detailed information about a specific plugin
     * @param string $pluginName Plugin directory name
     * @return array|null Plugin details or null if not found
     */
    public function getPluginDetails(string $pluginName): ?array
    {
        $pluginPath = $this->pluginsPath . $pluginName;

        if (!is_dir($pluginPath)) {
            return null;
        }

        $plugin = [
            'name' => $pluginName,
            'display_name' => ucfirst(str_replace(['-', '_'], ' ', $pluginName)),
            'path' => $pluginPath,
            'description' => 'No description available',
            'version' => '1.0.0',
            'author' => 'Unknown',
            'requires' => '1.0.0',
            'tested_up_to' => '1.0.0',
            'main_file' => null,
            'has_settings' => false,
            'has_hooks' => false,
            'files' => [],
            // Depends and Conflicts are optional headers: the parser only writes
            // them when the line is present, and the admin templates read them
            // unguarded, so they have to exist as empty arrays.
            'depends' => [],
            'conflicts' => []
        ];

        // Check for main plugin file
        $mainFile = $pluginPath . '/' . $pluginName . '.php';
        if (file_exists($mainFile)) {
            $plugin['main_file'] = $mainFile;

            // Parse plugin header for metadata
            $pluginData = $this->parsePluginHeader($mainFile);
            if ($pluginData) {
                $plugin = array_merge($plugin, $pluginData);
            }
        }

        // Check for settings file
        $settingsFile = $pluginPath . '/settings.php';
        if (file_exists($settingsFile)) {
            $plugin['has_settings'] = true;
        }

        // Check for hooks file
        $hooksFile = $pluginPath . '/hooks.php';
        if (file_exists($hooksFile)) {
            $plugin['has_hooks'] = true;
        }

        // Scan for all files
        $plugin['files'] = $this->getPluginFiles($pluginPath);

        return $plugin;
    }

    /**
     * Parse plugin header for metadata
     * @param string $filePath Path to plugin main file
     * @return array|null Plugin metadata
     */
    private function parsePluginHeader(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $fileContent = file_get_contents($filePath, false, null, 0, 4096);
        $headers = [
            'display_name' => 'Plugin Name',
            'description' => 'Description',
            'version' => 'Version',
            'author' => 'Author',
            'author_uri' => 'Author URI',
            'plugin_uri' => 'Plugin URI',
            'requires' => 'Requires',
            'tested_up_to' => 'Tested up to',
            'requires_php' => 'Requires PHP',
            'network' => 'Network',
            'update_uri' => 'Update URI',
            'depends' => 'Depends',
            'conflicts' => 'Conflicts',
            'api_version' => 'API Version',
            'priority' => 'Priority'
        ];

        $pluginData = [];

        foreach ($headers as $key => $header) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($header, '/') . ':(.*)$/mi', $fileContent, $match)) {
                $value = trim($match[1]);

                // Parse comma-separated values for dependencies
                if (in_array($key, ['depends', 'conflicts'])) {
                    $value = array_map('trim', explode(',', $value));
                    $value = array_filter($value); // Remove empty values
                }

                $pluginData[$key] = $value;
            }
        }

        return $pluginData;
    }

    /**
     * Get all files in a plugin directory
     * @param string $path Plugin directory path
     * @return array List of files with extensions
     */
    private function getPluginFiles(string $path): array
    {
        $files = [];

        if (!is_dir($path)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($path . '/', '', $file->getPathname());
                $extension = strtoupper($file->getExtension());

                $files[] = [
                    'name' => $relativePath,
                    'extension' => $extension ?: 'FILE'
                ];
            }
        }

        // Sort by name
        usort($files, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return $files;
    }

    /**
     * Get list of active plugins
     * @return array List of active plugin names
     */
    public function getActivePlugins(): array
    {
        $activePlugins = SystemSettingsHelper::get('ACTIVE_PLUGINS');

        if (empty($activePlugins)) {
            return [];
        }

        return json_decode($activePlugins, true) ?: [];
    }

    /**
     * Check if a plugin is active
     * @param string $pluginName Plugin name
     * @return bool True if plugin is active
     */
    public function isPluginActive(string $pluginName): bool
    {
        $activePlugins = $this->getActivePlugins();
        return in_array($pluginName, $activePlugins);
    }

    /**
     * Activate a plugin
     * @param string $pluginName Plugin name to activate
     * @return bool Success status
     */
    public function activatePlugin(string $pluginName): bool
    {
        // Validate plugin exists
        if (!$this->isValidPlugin($pluginName)) {
            throw new \Exception("Plugin '$pluginName' not found or invalid");
        }

        // Check dependencies and conflicts
        $this->validatePluginDependencies($pluginName);

        $activePlugins = $this->getActivePlugins();

        if (!in_array($pluginName, $activePlugins)) {
            $activePlugins[] = $pluginName;

            try {
                // Ensure menu tables have correct structure
                $this->menuManager->checkMenuTablesStructure();

                // Register plugin menus
                $pluginPath = $this->pluginsPath . $pluginName;
                $menuConfig = $this->menuManager->getPluginMenuConfig($pluginName, $pluginPath);

                if ($menuConfig) {
                    $this->menuManager->registerPluginMenus($pluginName, $menuConfig);
                }

                // Register plugin routes
                $hasController = $this->routesManager->hasController($pluginName);
                LogHelper::info("Plugin route generation", ['plugin' => $pluginName, 'hasController' => $hasController]);

                if ($hasController) {
                    LogHelper::info("Generating routes for plugin", ['plugin' => $pluginName]);
                    $routes = $this->routesManager->generatePluginRoutes($pluginName, $pluginPath);
                    LogHelper::info("Routes generated", ['plugin' => $pluginName, 'routes_count' => count($routes)]);
                    $this->routesManager->registerPluginRoutes($pluginName, $routes);
                    LogHelper::info("Routes registered, will generate file after plugin activation", ['plugin' => $pluginName]);
                } else {
                    LogHelper::warning("No controller found for plugin", ['plugin' => $pluginName]);
                }

                // Update active plugins setting FIRST
                $result = SystemSettingsHelper::set('ACTIVE_PLUGINS', json_encode($activePlugins));

                // THEN generate routes file (now active_plugins will be updated)
                if ($hasController && $result) {
                    $this->routesManager->generateRoutesFile();
                    LogHelper::info("Routes file generated after plugin activation", ['plugin' => $pluginName]);
                }

                if ($result) {
                    // Run plugin activation hook
                    $this->runPluginHook($pluginName, 'activate');

                    LogHelper::info('Plugin activated successfully', ['plugin' => $pluginName]);
                    return true;
                } else {
                    LogHelper::error('Failed to update active plugins setting', ['plugin' => $pluginName]);
                    return false;
                }
            } catch (\Exception $e) {
                LogHelper::error('Plugin activation error', [
                    'plugin' => $pluginName,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return true; // Already active
    }

    /**
     * Deactivate a plugin
     * @param string $pluginName Plugin name to deactivate
     * @return bool Success status
     */
    public function deactivatePlugin(string $pluginName): bool
    {
        $activePlugins = $this->getActivePlugins();

        $key = array_search($pluginName, $activePlugins);
        if ($key !== false) {
            unset($activePlugins[$key]);

            try {
                // Run plugin deactivation hook first
                $this->runPluginHook($pluginName, 'deactivate');

                // Remove plugin menus
                $this->menuManager->unregisterPluginMenus($pluginName);

                // Remove plugin routes
                $this->routesManager->unregisterPluginRoutes($pluginName);
                $this->routesManager->generateRoutesFile();

                // Update active plugins setting
                $result = SystemSettingsHelper::set('ACTIVE_PLUGINS', json_encode(array_values($activePlugins)));

                if ($result) {
                    LogHelper::info('Plugin deactivated successfully', ['plugin' => $pluginName]);
                    return true;
                } else {
                    LogHelper::error('Failed to update active plugins setting', ['plugin' => $pluginName]);
                    return false;
                }
            } catch (\Exception $e) {
                LogHelper::error('Plugin deactivation error', [
                    'plugin' => $pluginName,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return true; // Already inactive
    }

    /**
     * Check if a plugin is valid
     * @param string $pluginName Plugin name to check
     * @return bool True if plugin is valid
     */
    public function isValidPlugin(string $pluginName): bool
    {
        $pluginPath = $this->pluginsPath . $pluginName;

        // Check if directory exists
        if (!is_dir($pluginPath)) {
            return false;
        }

        // Check for main plugin file
        $mainFile = $pluginPath . '/' . $pluginName . '.php';
        return file_exists($mainFile);
    }

    /**
     * Run a plugin hook
     * @param string $pluginName Plugin name
     * @param string $hook Hook name (activate, deactivate, etc.)
     * @return void
     */
    private function runPluginHook(string $pluginName, string $hook): void
    {
        $pluginPath = $this->pluginsPath . $pluginName;
        $mainFile = $pluginPath . '/' . $pluginName . '.php';

        if (file_exists($mainFile)) {
            try {
                include_once $mainFile;

                // Try to call hook function if it exists
                $hookFunction = $pluginName . '_' . $hook;
                if (function_exists($hookFunction)) {
                    $hookFunction();
                }
            } catch (\Exception $e) {
                LogHelper::error("Error running plugin hook", [
                    'plugin' => $pluginName,
                    'hook' => $hook,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Get plugin settings
     * @param string $pluginName Plugin name
     * @return array Plugin settings
     */
    public function getPluginSettings(string $pluginName): array
    {
        $settingsKey = 'PLUGIN_' . strtoupper($pluginName) . '_SETTINGS';
        $settings = SystemSettingsHelper::get($settingsKey);

        return $settings ? json_decode($settings, true) : [];
    }

    /**
     * Save plugin settings
     * @param string $pluginName Plugin name
     * @param array $settings Settings array
     * @return bool Success status
     */
    public function savePluginSettings(string $pluginName, array $settings): bool
    {
        $settingsKey = 'PLUGIN_' . strtoupper($pluginName) . '_SETTINGS';

        try {
            $result = SystemSettingsHelper::set($settingsKey, json_encode($settings));

            if ($result) {
                LogHelper::info('Plugin settings saved', ['plugin' => $pluginName]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            LogHelper::error('Error saving plugin settings', [
                'plugin' => $pluginName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Load active plugins (call this during application bootstrap)
     * @return void
     */
    public function loadActivePlugins(): void
    {
        $activePlugins = $this->getActivePlugins();

        foreach ($activePlugins as $pluginName) {
            $this->loadPlugin($pluginName);
        }
    }

    /**
     * Load a specific plugin
     * @param string $pluginName Plugin name
     * @return bool Success status
     */
    private function loadPlugin(string $pluginName): bool
    {
        $pluginPath = $this->pluginsPath . $pluginName;
        $mainFile = $pluginPath . '/' . $pluginName . '.php';

        if (file_exists($mainFile)) {
            try {
                include_once $mainFile;

                // Load hooks if they exist
                $hooksFile = $pluginPath . '/hooks.php';
                if (file_exists($hooksFile)) {
                    include_once $hooksFile;
                }

                LogHelper::debug('Plugin loaded', ['plugin' => $pluginName]);
                return true;
            } catch (\Exception $e) {
                LogHelper::error('Error loading plugin', [
                    'plugin' => $pluginName,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }

        return false;
    }

    /**
     * Validate plugin dependencies and conflicts
     * @param string $pluginName Plugin name to validate
     * @throws \Exception If validation fails
     */
    private function validatePluginDependencies(string $pluginName): void
    {
        $plugin = $this->getPluginDetails($pluginName);

        if (!$plugin) {
            throw new \Exception("Plugin details not found for '$pluginName'");
        }

        // Check system requirements
        $this->validateSystemRequirements($plugin);

        // Check plugin dependencies
        $this->validatePluginDependencyChain($plugin);

        // Check for conflicts
        $this->validatePluginConflicts($plugin);
    }

    /**
     * Validate system requirements
     * @param array $plugin Plugin data
     * @throws \Exception If requirements not met
     */
    private function validateSystemRequirements(array $plugin): void
    {
        // Check CMS version
        if (!empty($plugin['requires'])) {
            // The VERSION file, not the CMS_VERSION setting: the setting is
            // seeded at install time and nothing rewrites it when the files are
            // upgraded, so a site installed at 1.0.0 and upgraded to 1.1.0 would
            // keep rejecting plugins that require 1.1.0.
            $cmsVersion = Version::current();
            if (!$this->versionCompare($cmsVersion, $plugin['requires'], '>=')) {
                throw new \Exception("Plugin '{$plugin['name']}' requires CMS version {$plugin['requires']} or higher. Current version: $cmsVersion");
            }
        }

        // Check PHP version
        if (!empty($plugin['requires_php'])) {
            $phpVersion = PHP_VERSION;
            if (!$this->versionCompare($phpVersion, $plugin['requires_php'], '>=')) {
                throw new \Exception("Plugin '{$plugin['name']}' requires PHP version {$plugin['requires_php']} or higher. Current version: $phpVersion");
            }
        }

        // Check if tested up to version
        if (!empty($plugin['tested_up_to'])) {
            $cmsVersion = Version::current();
            if (!$this->versionCompare($cmsVersion, $plugin['tested_up_to'], '<=')) {
                LogHelper::warning("Plugin '{$plugin['name']}' has not been tested with CMS version $cmsVersion. Last tested version: {$plugin['tested_up_to']}");
            }
        }
    }

    /**
     * Validate plugin dependency chain
     * @param array $plugin Plugin data
     * @throws \Exception If dependencies not met
     */
    private function validatePluginDependencyChain(array $plugin): void
    {
        if (empty($plugin['depends'])) {
            return;
        }

        $activePlugins = $this->getActivePlugins();
        $availablePlugins = $this->getAvailablePlugins();
        $availablePluginNames = array_column($availablePlugins, 'name');

        foreach ($plugin['depends'] as $dependency) {
            // Parse dependency (can include version requirement like "plugin-name >= 1.0.0")
            $depInfo = $this->parseDependencyString($dependency);
            $depName = $depInfo['name'];
            $depVersion = $depInfo['version'];
            $depOperator = $depInfo['operator'];

            // Check if dependency plugin exists
            if (!in_array($depName, $availablePluginNames)) {
                throw new \Exception("Plugin '{$plugin['name']}' depends on '$depName' which is not available");
            }

            // Check if dependency is active
            if (!in_array($depName, $activePlugins)) {
                throw new \Exception("Plugin '{$plugin['name']}' depends on '$depName' which is not active. Please activate '$depName' first.");
            }

            // Check version requirement if specified
            if ($depVersion && $depOperator) {
                $depPlugin = $this->getPluginDetails($depName);
                if ($depPlugin && !empty($depPlugin['version'])) {
                    if (!$this->versionCompare($depPlugin['version'], $depVersion, $depOperator)) {
                        throw new \Exception("Plugin '{$plugin['name']}' requires '$depName' version $depOperator $depVersion. Current version: {$depPlugin['version']}");
                    }
                }
            }
        }
    }

    /**
     * Validate plugin conflicts
     * @param array $plugin Plugin data
     * @throws \Exception If conflicts found
     */
    private function validatePluginConflicts(array $plugin): void
    {
        if (empty($plugin['conflicts'])) {
            return;
        }

        $activePlugins = $this->getActivePlugins();

        foreach ($plugin['conflicts'] as $conflict) {
            $conflictInfo = $this->parseDependencyString($conflict);
            $conflictName = $conflictInfo['name'];

            if (in_array($conflictName, $activePlugins)) {
                throw new \Exception("Plugin '{$plugin['name']}' conflicts with '$conflictName' which is currently active. Please deactivate '$conflictName' first.");
            }
        }
    }

    /**
     * Parse dependency string (e.g., "plugin-name >= 1.0.0")
     * @param string $dependency Dependency string
     * @return array Parsed dependency info
     */
    private function parseDependencyString(string $dependency): array
    {
        $pattern = '/^([a-zA-Z0-9_-]+)(?:\s*(>=|<=|>|<|=)\s*([0-9]+(?:\.[0-9]+)*(?:-[a-zA-Z0-9]+)?))?$/';

        if (preg_match($pattern, $dependency, $matches)) {
            return [
                'name' => $matches[1],
                'operator' => $matches[2] ?? null,
                'version' => $matches[3] ?? null
            ];
        }

        return [
            'name' => $dependency,
            'operator' => null,
            'version' => null
        ];
    }

    /**
     * Compare versions
     * @param string $version1 First version
     * @param string $version2 Second version
     * @param string $operator Comparison operator
     * @return bool Comparison result
     */
    private function versionCompare(string $version1, string $version2, string $operator): bool
    {
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Get plugin dependency tree
     * @param string $pluginName Plugin name
     * @return array Dependency tree
     */
    public function getPluginDependencyTree(string $pluginName): array
    {
        $tree = [];
        $this->buildDependencyTree($pluginName, $tree, []);
        return $tree;
    }

    /**
     * Build dependency tree recursively
     * @param string $pluginName Current plugin
     * @param array &$tree Dependency tree reference
     * @param array $visited Visited plugins to avoid circular dependencies
     * @throws \Exception If circular dependency found
     */
    private function buildDependencyTree(string $pluginName, array &$tree, array $visited): void
    {
        if (in_array($pluginName, $visited)) {
            throw new \Exception("Circular dependency detected involving plugin '$pluginName'");
        }

        $visited[] = $pluginName;
        $plugin = $this->getPluginDetails($pluginName);

        if (!$plugin || empty($plugin['depends'])) {
            return;
        }

        $tree[$pluginName] = [];

        foreach ($plugin['depends'] as $dependency) {
            $depInfo = $this->parseDependencyString($dependency);
            $depName = $depInfo['name'];

            $tree[$pluginName][] = $depName;
            $this->buildDependencyTree($depName, $tree, $visited);
        }
    }

    /**
     * Get plugins that depend on a specific plugin
     * @param string $pluginName Plugin name
     * @return array List of dependent plugins
     */
    public function getPluginDependents(string $pluginName): array
    {
        $dependents = [];
        $allPlugins = $this->getAvailablePlugins();

        foreach ($allPlugins as $plugin) {
            if (!empty($plugin['depends'])) {
                foreach ($plugin['depends'] as $dependency) {
                    $depInfo = $this->parseDependencyString($dependency);
                    if ($depInfo['name'] === $pluginName) {
                        $dependents[] = $plugin['name'];
                        break;
                    }
                }
            }
        }

        return $dependents;
    }

    /**
     * Check if plugin can be safely deactivated
     * @param string $pluginName Plugin name
     * @return array|true True if safe, array of issues if not
     */
    public function canDeactivatePlugin(string $pluginName)
    {
        $dependents = $this->getPluginDependents($pluginName);
        $activePlugins = $this->getActivePlugins();

        $activeDependents = array_intersect($dependents, $activePlugins);

        if (!empty($activeDependents)) {
            return [
                'error' => 'Cannot deactivate plugin with active dependents',
                'dependents' => $activeDependents
            ];
        }

        return true;
    }

    /**
     * Get plugin activation order based on dependencies
     * @param array $pluginNames List of plugin names to order
     * @return array Ordered plugin names
     */
    public function getPluginActivationOrder(array $pluginNames): array
    {
        $ordered = [];
        $remaining = $pluginNames;
        $iterations = 0;
        $maxIterations = count($pluginNames) * 2; // Prevent infinite loops

        while (!empty($remaining) && $iterations < $maxIterations) {
            $progress = false;

            foreach ($remaining as $key => $pluginName) {
                $plugin = $this->getPluginDetails($pluginName);
                $canActivate = true;

                if (!empty($plugin['depends'])) {
                    foreach ($plugin['depends'] as $dependency) {
                        $depInfo = $this->parseDependencyString($dependency);
                        $depName = $depInfo['name'];

                        // Check if dependency is in our list and not yet ordered
                        if (in_array($depName, $pluginNames) && !in_array($depName, $ordered)) {
                            $canActivate = false;
                            break;
                        }
                    }
                }

                if ($canActivate) {
                    $ordered[] = $pluginName;
                    unset($remaining[$key]);
                    $progress = true;
                }
            }

            if (!$progress) {
                // Circular dependency or missing dependency
                LogHelper::warning('Circular dependency or missing dependency detected in plugins: ' . implode(', ', $remaining));
                $ordered = array_merge($ordered, $remaining);
                break;
            }

            $iterations++;
        }

        return $ordered;
    }

    /**
     * Validate plugin compatibility
     * @param string $pluginName Plugin name
     * @return array Validation results
     */
    public function validatePluginCompatibility(string $pluginName): array
    {
        $plugin = $this->getPluginDetails($pluginName);
        $results = [
            'compatible' => true,
            'warnings' => [],
            'errors' => [],
            'requirements' => []
        ];

        if (!$plugin) {
            $results['compatible'] = false;
            $results['errors'][] = 'Plugin not found';
            return $results;
        }

        try {
            $this->validateSystemRequirements($plugin);
            $results['requirements']['system'] = 'OK';
        } catch (\Exception $e) {
            $results['compatible'] = false;
            $results['errors'][] = $e->getMessage();
            $results['requirements']['system'] = 'FAILED';
        }

        try {
            $this->validatePluginDependencyChain($plugin);
            $results['requirements']['dependencies'] = 'OK';
        } catch (\Exception $e) {
            $results['compatible'] = false;
            $results['errors'][] = $e->getMessage();
            $results['requirements']['dependencies'] = 'FAILED';
        }

        try {
            $this->validatePluginConflicts($plugin);
            $results['requirements']['conflicts'] = 'OK';
        } catch (\Exception $e) {
            $results['compatible'] = false;
            $results['errors'][] = $e->getMessage();
            $results['requirements']['conflicts'] = 'FAILED';
        }

        // Check for warnings
        if (!empty($plugin['tested_up_to'])) {
            $cmsVersion = Version::current();
            if (!$this->versionCompare($cmsVersion, $plugin['tested_up_to'], '<=')) {
                $results['warnings'][] = "Plugin has not been tested with CMS version $cmsVersion";
            }
        }

        return $results;
    }
}

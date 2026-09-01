<?php

namespace App\Services;

use App\Helpers\LogHelper;
use App\Helpers\StringHelper;

/**
 * Plugin Generator Service
 * Generates plugin scaffolding and boilerplate code
 */
class PluginGeneratorService
{
    private $pluginsPath;
    private $templatesPath;

    public function __construct()
    {
        $this->pluginsPath = __DIR__ . '/../../plugins/';
        $this->templatesPath = __DIR__ . '/../../App/Services/templates/plugin/';

        // Create plugins directory if it doesn't exist
        if (!is_dir($this->pluginsPath)) {
            mkdir($this->pluginsPath, 0755, true);
        }
    }

    /**
     * Generate a new plugin
     * @param array $config Plugin configuration
     * @return array Result with success status and messages
     */
    public function generatePlugin(array $config): array
    {
        try {
            // Validate configuration
            $validation = $this->validateConfig($config);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            // Sanitize plugin name
            $pluginName = $this->sanitizePluginName($config['name']);
            $pluginPath = $this->pluginsPath . $pluginName;

            // Check if plugin already exists
            if (is_dir($pluginPath)) {
                return [
                    'success' => false,
                    'errors' => ["Plugin '$pluginName' already exists"]
                ];
            }

            // Create plugin directory
            mkdir($pluginPath, 0755, true);

            // Generate plugin files
            $this->generateMainFile($pluginPath, $pluginName, $config);

            if ($config['include_hooks']) {
                $this->generateHooksFile($pluginPath, $pluginName, $config);
            }

            if ($config['include_settings']) {
                $this->generateSettingsFile($pluginPath, $pluginName, $config);
            }

            if ($config['include_assets']) {
                $this->generateAssetsStructure($pluginPath);
            }

            if ($config['include_readme']) {
                $this->generateReadmeFile($pluginPath, $pluginName, $config);
            }

            if ($config['include_tests']) {
                $this->generateTestFiles($pluginPath, $pluginName, $config);
            }

            LogHelper::info('Plugin generated successfully', ['plugin' => $pluginName]);

            return [
                'success' => true,
                'plugin_name' => $pluginName,
                'plugin_path' => $pluginPath,
                'files_created' => $this->getCreatedFiles($pluginPath)
            ];
        } catch (\Exception $e) {
            LogHelper::error('Plugin generation failed', [
                'error' => $e->getMessage(),
                'config' => $config
            ]);

            return [
                'success' => false,
                'errors' => ['Plugin generation failed: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Validate plugin configuration
     * @param array $config Configuration to validate
     * @return array Validation result
     */
    private function validateConfig(array $config): array
    {
        $errors = [];

        // Required fields
        if (empty($config['name'])) {
            $errors[] = 'Plugin name is required';
        }

        if (empty($config['description'])) {
            $errors[] = 'Plugin description is required';
        }

        if (empty($config['author'])) {
            $errors[] = 'Plugin author is required';
        }

        if (empty($config['version'])) {
            $errors[] = 'Plugin version is required';
        }

        // Validate plugin name format
        if (!empty($config['name']) && !preg_match('/^[a-zA-Z0-9_-]+$/', $config['name'])) {
            $errors[] = 'Plugin name can only contain letters, numbers, hyphens, and underscores';
        }

        // Validate version format
        if (!empty($config['version']) && !preg_match('/^\d+\.\d+\.\d+$/', $config['version'])) {
            $errors[] = 'Version must be in format X.Y.Z (e.g., 1.0.0)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Sanitize plugin name
     * @param string $name Plugin name
     * @return string Sanitized name
     */
    private function sanitizePluginName(string $name): string
    {
        // Convert to lowercase and replace spaces with hyphens
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');

        return $name;
    }

    /**
     * Generate main plugin file
     * @param string $pluginPath Plugin directory path
     * @param string $pluginName Plugin name
     * @param array $config Plugin configuration
     */
    private function generateMainFile(string $pluginPath, string $pluginName, array $config): void
    {
        $className = $this->generateClassName($pluginName);
        $functionPrefix = str_replace('-', '_', $pluginName);

        $template = $this->getMainFileTemplate();
        $content = $this->replacePlaceholders($template, [
            'PLUGIN_NAME' => $config['display_name'] ?? ucwords(str_replace('-', ' ', $pluginName)),
            'PLUGIN_DESCRIPTION' => $config['description'],
            'PLUGIN_VERSION' => $config['version'],
            'PLUGIN_AUTHOR' => $config['author'],
            'PLUGIN_AUTHOR_URI' => $config['author_uri'] ?? '',
            'PLUGIN_URI' => $config['plugin_uri'] ?? '',
            'PLUGIN_REQUIRES' => $config['requires'] ?? '1.0.0',
            'PLUGIN_TESTED_UP_TO' => $config['tested_up_to'] ?? '1.5.0',
            'PLUGIN_REQUIRES_PHP' => $config['requires_php'] ?? '8.0.0',
            'PLUGIN_DEPENDS' => is_array($config['depends'] ?? []) ? implode(', ', $config['depends']) : '',
            'PLUGIN_CONFLICTS' => is_array($config['conflicts'] ?? []) ? implode(', ', $config['conflicts']) : '',
            'PLUGIN_PRIORITY' => $config['priority'] ?? '10',
            'PLUGIN_DIRECTORY_NAME' => $pluginName,
            'PLUGIN_CLASS_NAME' => $className,
            'PLUGIN_FUNCTION_PREFIX' => $functionPrefix,
            'CURRENT_YEAR' => date('Y'),
            'GENERATION_DATE' => date('Y-m-d H:i:s')
        ]);

        file_put_contents($pluginPath . '/' . $pluginName . '.php', $content);
    }

    /**
     * Generate hooks file
     * @param string $pluginPath Plugin directory path
     * @param string $pluginName Plugin name
     * @param array $config Plugin configuration
     */
    private function generateHooksFile(string $pluginPath, string $pluginName, array $config): void
    {
        $functionPrefix = str_replace('-', '_', $pluginName);

        $template = $this->getHooksFileTemplate();
        $content = $this->replacePlaceholders($template, [
            'PLUGIN_FUNCTION_PREFIX' => $functionPrefix,
            'PLUGIN_NAME' => $config['display_name'] ?? ucwords(str_replace('-', ' ', $pluginName)),
            'CURRENT_YEAR' => date('Y')
        ]);

        file_put_contents($pluginPath . '/hooks.php', $content);
    }

    /**
     * Generate settings file
     * @param string $pluginPath Plugin directory path
     * @param string $pluginName Plugin name
     * @param array $config Plugin configuration
     */
    private function generateSettingsFile(string $pluginPath, string $pluginName, array $config): void
    {
        $functionPrefix = str_replace('-', '_', $pluginName);

        $template = $this->getSettingsFileTemplate();
        $content = $this->replacePlaceholders($template, [
            'PLUGIN_FUNCTION_PREFIX' => $functionPrefix,
            'PLUGIN_NAME' => $config['display_name'] ?? ucwords(str_replace('-', ' ', $pluginName)),
            'PLUGIN_DIRECTORY_NAME' => $pluginName,
            'CURRENT_YEAR' => date('Y')
        ]);

        file_put_contents($pluginPath . '/settings.php', $content);
    }

    /**
     * Generate assets structure
     * @param string $pluginPath Plugin directory path
     */
    private function generateAssetsStructure(string $pluginPath): void
    {
        $dirs = ['assets', 'assets/css', 'assets/js', 'assets/img'];

        foreach ($dirs as $dir) {
            $dirPath = $pluginPath . '/' . $dir;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
        }

        // Create basic CSS file
        $cssContent = "/* Plugin Styles */\n.{plugin-name}-wrapper {\n    /* Add your styles here */\n}\n";
        file_put_contents($pluginPath . '/assets/css/style.css', $cssContent);

        // Create basic JS file
        $jsContent = "// Plugin JavaScript\n(function($) {\n    'use strict';\n    \n    // Plugin initialization\n    $(document).ready(function() {\n        // Add your JavaScript here\n    });\n    \n})(jQuery);\n";
        file_put_contents($pluginPath . '/assets/js/script.js', $jsContent);
    }

    /**
     * Generate README file
     * @param string $pluginPath Plugin directory path
     * @param string $pluginName Plugin name
     * @param array $config Plugin configuration
     */
    private function generateReadmeFile(string $pluginPath, string $pluginName, array $config): void
    {
        $template = $this->getReadmeTemplate();
        $content = $this->replacePlaceholders($template, [
            'PLUGIN_NAME' => $config['display_name'] ?? ucwords(str_replace('-', ' ', $pluginName)),
            'PLUGIN_DESCRIPTION' => $config['description'],
            'PLUGIN_VERSION' => $config['version'],
            'PLUGIN_AUTHOR' => $config['author'],
            'PLUGIN_REQUIRES' => $config['requires'] ?? '1.0.0',
            'PLUGIN_TESTED_UP_TO' => $config['tested_up_to'] ?? '1.5.0',
            'CURRENT_YEAR' => date('Y')
        ]);

        file_put_contents($pluginPath . '/README.md', $content);
    }

    /**
     * Generate test files
     * @param string $pluginPath Plugin directory path
     * @param string $pluginName Plugin name
     * @param array $config Plugin configuration
     */
    private function generateTestFiles(string $pluginPath, string $pluginName, array $config): void
    {
        $testDir = $pluginPath . '/tests';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }

        $className = $this->generateClassName($pluginName);

        $template = $this->getTestFileTemplate();
        $content = $this->replacePlaceholders($template, [
            'PLUGIN_CLASS_NAME' => $className,
            'PLUGIN_NAME' => $config['display_name'] ?? ucwords(str_replace('-', ' ', $pluginName)),
            'CURRENT_YEAR' => date('Y')
        ]);

        file_put_contents($testDir . '/' . $className . 'Test.php', $content);
    }

    /**
     * Generate class name from plugin name
     * @param string $pluginName Plugin name
     * @return string Class name
     */
    private function generateClassName(string $pluginName): string
    {
        $parts = explode('-', $pluginName);
        $className = '';

        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }

        return $className . 'Plugin';
    }

    /**
     * Replace placeholders in template
     * @param string $template Template content
     * @param array $replacements Replacement values
     * @return string Processed content
     */
    private function replacePlaceholders(string $template, array $replacements): string
    {
        foreach ($replacements as $placeholder => $value) {
            $template = str_replace('{{' . $placeholder . '}}', $value, $template);
        }

        return $template;
    }

    /**
     * Get created files list
     * @param string $pluginPath Plugin directory path
     * @return array List of created files
     */
    private function getCreatedFiles(string $pluginPath): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pluginPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = str_replace($pluginPath . '/', '', $file->getPathname());
            }
        }

        return $files;
    }

    /**
     * Get main file template
     * @return string Template content
     */
    private function getMainFileTemplate(): string
    {
        return '<?php
/**
 * Plugin Name: {{PLUGIN_NAME}}
 * Description: {{PLUGIN_DESCRIPTION}}
 * Version: {{PLUGIN_VERSION}}
 * Author: {{PLUGIN_AUTHOR}}
 * Author URI: {{PLUGIN_AUTHOR_URI}}
 * Plugin URI: {{PLUGIN_URI}}
 * Requires: {{PLUGIN_REQUIRES}}
 * Tested up to: {{PLUGIN_TESTED_UP_TO}}
 * Requires PHP: {{PLUGIN_REQUIRES_PHP}}
 * Depends: {{PLUGIN_DEPENDS}}
 * Conflicts: {{PLUGIN_CONFLICTS}}
 * Priority: {{PLUGIN_PRIORITY}}
 * 
 * Generated on: {{GENERATION_DATE}}
 */

// Prevent direct access
if (!defined(\'APP_PATH\')) {
    exit(\'Direct access denied\');
}

/**
 * {{PLUGIN_CLASS_NAME}} Main Class
 */
class {{PLUGIN_CLASS_NAME}} {
    
    /**
     * Plugin version
     */
    const VERSION = \'{{PLUGIN_VERSION}}\';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init() {
        // Register hooks
        $this->registerHooks();
        
        // Initialize plugin functionality
        $this->initializeFeatures();
    }
    
    /**
     * Register plugin hooks
     */
    private function registerHooks() {
        if (class_exists(\'\\App\\Core\\HookSystem\')) {
            $hookSystem = \\App\\Core\\HookSystem::getInstance();
            
            // Add your hooks here
            $hookSystem->addAction(\'init\', [__CLASS__, \'onInit\']);
            $hookSystem->addAction(\'admin_init\', [__CLASS__, \'onAdminInit\']);
            
            // Example filter
            $hookSystem->addFilter(\'the_content\', [__CLASS__, \'filterContent\']);
        }
    }
    
    /**
     * Initialize plugin features
     */
    private function initializeFeatures() {
        // Add your initialization code here
    }
    
    /**
     * Plugin initialization hook
     */
    public static function onInit() {
        // Plugin initialization logic
    }
    
    /**
     * Admin initialization hook
     */
    public static function onAdminInit() {
        // Admin-specific initialization
    }
    
    /**
     * Content filter example
     */
    public static function filterContent($content) {
        // Modify content if needed
        return $content;
    }
    
    /**
     * Get plugin settings
     */
    public static function getSettings() {
        $settings = \\App\\Helpers\\SystemSettingsHelper::get(\'PLUGIN_{{PLUGIN_DIRECTORY_NAME|upper}}_SETTINGS\');
        return $settings ? json_decode($settings, true) : self::getDefaultSettings();
    }
    
    /**
     * Get default settings
     */
    private static function getDefaultSettings() {
        return [
            \'enabled\' => true,
            \'version\' => self::VERSION
        ];
    }
}

/**
 * Plugin activation hook
 */
function {{PLUGIN_FUNCTION_PREFIX}}_activate() {
    // Plugin activation logic
    error_log(\'{{PLUGIN_NAME}} activated\');
    
    // Set default settings
    $defaultSettings = {{PLUGIN_CLASS_NAME}}::getDefaultSettings();
    \\App\\Helpers\\SystemSettingsHelper::set(\'PLUGIN_{{PLUGIN_DIRECTORY_NAME|upper}}_SETTINGS\', json_encode($defaultSettings));
}

/**
 * Plugin deactivation hook
 */
function {{PLUGIN_FUNCTION_PREFIX}}_deactivate() {
    // Plugin deactivation logic
    error_log(\'{{PLUGIN_NAME}} deactivated\');
    
    // Clean up if needed (but preserve user data)
}

// Initialize the plugin
{{PLUGIN_CLASS_NAME}}::getInstance();
';
    }

    /**
     * Get hooks file template
     * @return string Template content
     */
    private function getHooksFileTemplate(): string
    {
        return '<?php
/**
 * {{PLUGIN_NAME}} - Hooks Definition
 * 
 * This file defines all the hooks that this plugin uses
 * 
 * @package {{PLUGIN_NAME}}
 * @version 1.0.0
 * @author {{PLUGIN_AUTHOR}}
 * @copyright {{CURRENT_YEAR}}
 */

// Prevent direct access
if (!defined(\'APP_PATH\')) {
    exit(\'Direct access denied\');
}

/**
 * Register all plugin hooks
 */
function {{PLUGIN_FUNCTION_PREFIX}}_register_hooks() {
    if (!class_exists(\'\\App\\Core\\HookSystem\')) {
        return;
    }
    
    $hookSystem = \\App\\Core\\HookSystem::getInstance();
    
    // Action hooks (execute code at specific points)
    $hookSystem->addAction(\'init\', \'{{PLUGIN_FUNCTION_PREFIX}}_on_init\', 10);
    $hookSystem->addAction(\'admin_init\', \'{{PLUGIN_FUNCTION_PREFIX}}_on_admin_init\', 10);
    $hookSystem->addAction(\'cms_head\', \'{{PLUGIN_FUNCTION_PREFIX}}_frontend_head\', 10);
    $hookSystem->addAction(\'admin_head\', \'{{PLUGIN_FUNCTION_PREFIX}}_admin_head\', 10);
    
    // Filter hooks (modify data)
    $hookSystem->addFilter(\'the_content\', \'{{PLUGIN_FUNCTION_PREFIX}}_filter_content\', 10, 1);
    $hookSystem->addFilter(\'admin_title\', \'{{PLUGIN_FUNCTION_PREFIX}}_filter_admin_title\', 10, 1);
}

/**
 * Initialize plugin functionality
 */
function {{PLUGIN_FUNCTION_PREFIX}}_on_init() {
    // Plugin initialization code
    $settings = {{PLUGIN_FUNCTION_PREFIX}}_get_settings();
    
    if ($settings[\'enabled\']) {
        // Plugin is enabled, initialize features
    }
}

/**
 * Admin initialization
 */
function {{PLUGIN_FUNCTION_PREFIX}}_on_admin_init() {
    // Admin-specific initialization
}

/**
 * Add content to frontend head
 */
function {{PLUGIN_FUNCTION_PREFIX}}_frontend_head() {
    $settings = {{PLUGIN_FUNCTION_PREFIX}}_get_settings();
    
    if (!$settings[\'enabled\']) {
        return;
    }
    
    echo \'<meta name="generator" content="{{PLUGIN_NAME}}">\' . "\\n";
}

/**
 * Add content to admin head
 */
function {{PLUGIN_FUNCTION_PREFIX}}_admin_head() {
    echo \'<style>\'
        . \'.{{PLUGIN_FUNCTION_PREFIX}}-admin { background-color: #f0f8ff; }\'
        . \'</style>\' . "\\n";
}

/**
 * Filter page/post content
 */
function {{PLUGIN_FUNCTION_PREFIX}}_filter_content($content) {
    $settings = {{PLUGIN_FUNCTION_PREFIX}}_get_settings();
    
    if (!$settings[\'enabled\']) {
        return $content;
    }
    
    // Modify content here
    return $content;
}

/**
 * Filter admin page titles
 */
function {{PLUGIN_FUNCTION_PREFIX}}_filter_admin_title($title) {
    // Modify admin title if needed
    return $title;
}

/**
 * Get plugin settings
 */
function {{PLUGIN_FUNCTION_PREFIX}}_get_settings() {
    $settings = \\App\\Helpers\\SystemSettingsHelper::get(\'PLUGIN_{{PLUGIN_FUNCTION_PREFIX|upper}}_SETTINGS\');
    return $settings ? json_decode($settings, true) : [
        \'enabled\' => true,
        \'version\' => \'1.0.0\'
    ];
}

// Register all hooks when this file is loaded
{{PLUGIN_FUNCTION_PREFIX}}_register_hooks();
';
    }

    /**
     * Get settings file template
     * @return string Template content
     */
    private function getSettingsFileTemplate(): string
    {
        return '<?php
/**
 * {{PLUGIN_NAME}} - Settings Configuration
 * 
 * This file provides a settings interface for the plugin
 * 
 * @package {{PLUGIN_NAME}}
 * @version 1.0.0
 * @author {{PLUGIN_AUTHOR}}
 * @copyright {{CURRENT_YEAR}}
 */

// Prevent direct access
if (!defined(\'APP_PATH\')) {
    exit(\'Direct access denied\');
}

/**
 * Render plugin settings form
 */
function {{PLUGIN_FUNCTION_PREFIX}}_render_settings() {
    $settings = {{PLUGIN_FUNCTION_PREFIX}}_get_settings();
    ?>
    <div class="plugin-settings-wrapper">
        <h3>{{PLUGIN_NAME}} Settings</h3>
        
        <form method="post" action="/admin/plugins/configure?plugin={{PLUGIN_DIRECTORY_NAME}}">
            <div class="form-group">
                <label>
                    <input type="checkbox" name="settings[enabled]" value="1" <?php echo $settings[\'enabled\'] ? \'checked\' : \'\'; ?>>
                    Enable {{PLUGIN_NAME}}
                </label>
            </div>
            
            <div class="form-group">
                <label for="custom_message">Custom Message:</label>
                <input type="text" 
                       id="custom_message" 
                       name="settings[custom_message]" 
                       value="<?php echo htmlspecialchars($settings[\'custom_message\'] ?? \'\'); ?>"
                       class="form-control">
            </div>
            
            <div class="form-group">
                <label for="display_position">Display Position:</label>
                <select name="settings[display_position]" id="display_position" class="form-control">
                    <option value="top" <?php echo ($settings[\'display_position\'] ?? \'top\') === \'top\' ? \'selected\' : \'\'; ?>>Top</option>
                    <option value="bottom" <?php echo ($settings[\'display_position\'] ?? \'top\') === \'bottom\' ? \'selected\' : \'\'; ?>>Bottom</option>
                </select>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </form>
    </div>
    
    <style>
    .plugin-settings-wrapper {
        max-width: 600px;
        margin: 20px 0;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    </style>
    <?php
}

/**
 * Get plugin settings with defaults
 */
function {{PLUGIN_FUNCTION_PREFIX}}_get_settings() {
    $settings = \\App\\Helpers\\SystemSettingsHelper::get(\'PLUGIN_{{PLUGIN_FUNCTION_PREFIX|upper}}_SETTINGS\');
    $defaults = [
        \'enabled\' => true,
        \'custom_message\' => \'Hello from {{PLUGIN_NAME}}!\',
        \'display_position\' => \'top\',
        \'version\' => \'1.0.0\'
    ];
    
    if ($settings) {
        return array_merge($defaults, json_decode($settings, true));
    }
    
    return $defaults;
}

// Render settings if this file is called directly from settings page
if (isset($_GET[\'action\']) && $_GET[\'action\'] === \'settings\') {
    {{PLUGIN_FUNCTION_PREFIX}}_render_settings();
}
';
    }

    /**
     * Get README template
     * @return string Template content
     */
    private function getReadmeTemplate(): string
    {
        return '# {{PLUGIN_NAME}}

{{PLUGIN_DESCRIPTION}}

## Description

This plugin was generated using the swCMS Plugin Generator. It provides a basic structure for creating custom functionality for your swCMS installation.

## Installation

1. Upload the plugin folder to your `/plugins/` directory
2. Go to your admin dashboard
3. Navigate to Plugins
4. Find "{{PLUGIN_NAME}}" and click Activate

## Requirements

- swCMS {{PLUGIN_REQUIRES}} or higher
- PHP 8.0 or higher

## Configuration

After activation, you can configure the plugin by:

1. Going to Admin > Plugins
2. Finding "{{PLUGIN_NAME}}"
3. Clicking "Settings" (if available)

## Features

- Basic plugin structure
- Hook system integration
- Settings management
- Admin interface integration

## Development

### File Structure

```
{{PLUGIN_DIRECTORY_NAME}}/
├── {{PLUGIN_DIRECTORY_NAME}}.php    # Main plugin file
├── hooks.php                        # Hook definitions
├── settings.php                     # Settings interface
├── README.md                        # This file
├── assets/                          # CSS, JS, images
│   ├── css/
│   ├── js/
│   └── img/
└── tests/                           # Unit tests
```

### Hooks

This plugin registers the following hooks:

- `init` - Plugin initialization
- `admin_init` - Admin initialization
- `cms_head` - Frontend head content
- `admin_head` - Admin head content
- `the_content` - Content filtering

### Settings

Plugin settings are stored in the system settings table with the key:
`PLUGIN_{{PLUGIN_DIRECTORY_NAME|upper}}_SETTINGS`

## Changelog

### Version {{PLUGIN_VERSION}}
- Initial release
- Basic plugin structure
- Hook system integration

## Support

For support and questions, please contact {{PLUGIN_AUTHOR}}.

## License

This plugin is released under the same license as swCMS.

---

Generated by swCMS Plugin Generator on {{GENERATION_DATE}}
Copyright {{CURRENT_YEAR}} {{PLUGIN_AUTHOR}}
';
    }

    /**
     * Get test file template
     * @return string Template content
     */
    private function getTestFileTemplate(): string
    {
        return '<?php
/**
 * {{PLUGIN_NAME}} - Unit Tests
 * 
 * @package {{PLUGIN_NAME}}
 * @version 1.0.0
 * @author {{PLUGIN_AUTHOR}}
 * @copyright {{CURRENT_YEAR}}
 */

use PHPUnit\\Framework\\TestCase;

/**
 * Test class for {{PLUGIN_CLASS_NAME}}
 */
class {{PLUGIN_CLASS_NAME}}Test extends TestCase {
    
    /**
     * Test plugin initialization
     */
    public function testPluginInitialization() {
        $this->assertTrue(class_exists(\'{{PLUGIN_CLASS_NAME}}\'));
        
        $plugin = {{PLUGIN_CLASS_NAME}}::getInstance();
        $this->assertInstanceOf(\'{{PLUGIN_CLASS_NAME}}\', $plugin);
    }
    
    /**
     * Test plugin settings
     */
    public function testPluginSettings() {
        $settings = {{PLUGIN_CLASS_NAME}}::getSettings();
        $this->assertIsArray($settings);
        $this->assertArrayHasKey(\'enabled\', $settings);
        $this->assertArrayHasKey(\'version\', $settings);
    }
    
    /**
     * Test plugin constants
     */
    public function testPluginConstants() {
        $this->assertTrue(defined(\'{{PLUGIN_CLASS_NAME}}::VERSION\'));
        $this->assertIsString({{PLUGIN_CLASS_NAME}}::VERSION);
    }
    
    /**
     * Test plugin activation
     */
    public function testPluginActivation() {
        // Test activation hook
        $this->assertTrue(function_exists(\'{{PLUGIN_FUNCTION_PREFIX}}_activate\'));
    }
    
    /**
     * Test plugin deactivation
     */
    public function testPluginDeactivation() {
        // Test deactivation hook
        $this->assertTrue(function_exists(\'{{PLUGIN_FUNCTION_PREFIX}}_deactivate\'));
    }
}
';
    }
}

<?php

namespace App\Core;

/**
 * Dependency Checker for swCMS
 * Checks for required dependencies and provides fallbacks for shared hosting
 */
class DependencyChecker
{
    private static $dependencies = [
        'smarty' => [
            'required' => true,
            'composer_class' => 'Smarty\\Smarty',
            'vendor_path' => '/vendor/smarty/smarty/src/Smarty.php',
            'description' => 'Smarty Template Engine',
            'fallback_available' => true
        ]
    ];

    /**
     * Check all dependencies and return status
     */
    public static function checkAll()
    {
        $results = [];
        $hasComposer = self::hasComposer();

        foreach (self::$dependencies as $name => $config) {
            $results[$name] = self::checkDependency($name, $config, $hasComposer);
        }

        return [
            'has_composer' => $hasComposer,
            'dependencies' => $results,
            'all_satisfied' => !in_array(false, array_column($results, 'satisfied'))
        ];
    }

    /**
     * Check if Composer is available
     */
    public static function hasComposer()
    {
        return file_exists(\ROOT_PATH . '/vendor/autoload.php');
    }

    /**
     * Check specific dependency
     */
    private static function checkDependency($name, $config, $hasComposer)
    {
        $satisfied = false;
        $method = 'none';
        $path = '';

        // Try Composer first if available
        if ($hasComposer) {
            if (class_exists($config['composer_class'])) {
                $satisfied = true;
                $method = 'composer';
                $path = 'vendor/' . $name;
            }
        }

        // Try direct vendor path
        if (!$satisfied) {
            $vendorPath = \ROOT_PATH . $config['vendor_path'];
            if (file_exists($vendorPath)) {
                $satisfied = true;
                $method = 'vendor_direct';
                $path = $vendorPath;
            }
        }

        // Try bundled version
        if (!$satisfied && $config['fallback_available']) {
            $bundledPath = \ROOT_PATH . '/App/vendor/' . $name;
            if (is_dir($bundledPath)) {
                $satisfied = true;
                $method = 'bundled';
                $path = $bundledPath;
            }
        }

        return [
            'name' => $config['description'],
            'satisfied' => $satisfied,
            'method' => $method,
            'path' => $path,
            'required' => $config['required'],
            'fallback_available' => $config['fallback_available']
        ];
    }

    /**
     * Initialize dependencies without Composer
     */
    public static function initializeWithoutComposer()
    {
        $status = self::checkAll();

        foreach ($status['dependencies'] as $name => $info) {
            if (!$info['satisfied'] && $info['required']) {
                throw new \Exception("Required dependency '{$info['name']}' not found. Please install manually or use bundled version.");
            }

            // Load dependency based on method
            if ($info['satisfied']) {
                self::loadDependency($name, $info);
            }
        }

        return $status;
    }

    /**
     * Load a specific dependency
     */
    private static function loadDependency($name, $info)
    {
        switch ($name) {
            case 'smarty':
                self::loadSmarty($info);
                break;
        }
    }

    /**
     * Load Smarty template engine
     */
    private static function loadSmarty($info)
    {
        switch ($info['method']) {
            case 'composer':
                // Already loaded by Composer autoloader
                break;

            case 'vendor_direct':
                require_once $info['path'];
                break;

            case 'bundled':
                $smartyPath = $info['path'] . '/src/Smarty.php';
                if (file_exists($smartyPath)) {
                    require_once $smartyPath;
                }
                break;
        }
    }

    /**
     * Create bundled dependencies for shared hosting
     */
    public static function createBundledDependencies()
    {
        $bundleDir = \ROOT_PATH . '/App/vendor';

        if (!is_dir($bundleDir)) {
            mkdir($bundleDir, 0755, true);
        }

        // For now, just create the structure
        // In a real scenario, you'd bundle the essential files
        return [
            'bundle_directory' => $bundleDir,
            'instructions' => [
                'smarty' => 'Download Smarty from https://github.com/smarty-php/smarty/releases and extract to App/vendor/smarty/'
            ]
        ];
    }

    /**
     * Get shared hosting installation instructions
     */
    public static function getSharedHostingInstructions()
    {
        return [
            'requirements' => [
                'PHP 7.4 or higher',
                'PDO extension (MySQL or SQLite)',
                'JSON extension',
                'Write permissions on data/, logs/, public/uploads/ directories'
            ],
            'steps' => [
                '1. Upload all files via FTP/File Manager',
                '2. Set directory permissions: chmod 755 data logs public/uploads',
                '3. If Composer not available, download dependencies manually',
                '4. Access your domain - the installer will run automatically',
                '5. Complete the installation wizard'
            ],
            'manual_dependencies' => [
                'smarty' => [
                    'url' => 'https://github.com/smarty-php/smarty/releases',
                    'extract_to' => 'App/vendor/smarty/',
                    'required_files' => ['src/Smarty.php']
                ]
            ]
        ];
    }
}

<?php

namespace App\Services;

use App\Helpers\SystemSettingsHelper;
use App\Helpers\LogHelper;

/**
 * Theme Service
 * Handles theme operations and management
 */
class ThemeService {
    private $themesPath;

    public function __construct() {
        $this->themesPath = __DIR__ . '/../../public/themes/';
    }

    /**
     * Get all available themes
     * @return array Array of theme information
     */
    public function getAvailableThemes(): array {
        $themes = [];
        
        if (!is_dir($this->themesPath)) {
            LogHelper::warning('Themes directory not found', ['path' => $this->themesPath]);
            return $themes;
        }

        $directories = scandir($this->themesPath);
        
        foreach ($directories as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($this->themesPath . $dir)) {
                continue;
            }

            $themeInfo = $this->getThemeDetails($dir);
            if ($themeInfo) {
                $themes[] = $themeInfo;
            }
        }

        return $themes;
    }

    /**
     * Get detailed information about a specific theme
     * @param string $themeName Theme directory name
     * @return array|null Theme details or null if not found
     */
    public function getThemeDetails(string $themeName): ?array {
        // Validate theme name - only alphanumeric, dash, underscore
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $themeName)) {
            throw new \Exception('Invalid theme name');
        }

        $themePath = $this->themesPath . $themeName;

        if (!is_dir($themePath)) {
            return null;
        }

        $theme = [
            'name' => $themeName,
            'display_name' => ucfirst($themeName),
            'path' => $themePath,
            'description' => 'No description available',
            'version' => '1.0.0',
            'author' => 'Unknown',
            'screenshot' => null,
            'templates' => [],
            'assets' => [
                'css' => [],
                'js' => []
            ]
        ];

        // Check for theme configuration file
        $configFile = $themePath . '/theme.conf.php';
        if (file_exists($configFile)) {
            // Use realpath to prevent traversal
            $realConfigPath = realpath($configFile);
            $realThemesPath = realpath($this->themesPath);

            // Verify the config file is within themes directory
            if ($realConfigPath === false || strpos($realConfigPath, $realThemesPath) !== 0) {
                throw new \Exception('Invalid theme configuration path');
            }

            $config = include $realConfigPath;
            if (is_array($config)) {
                $theme = array_merge($theme, $config);
            }
        }

        // Get template files
        $templatesPath = $themePath . '/templates/';
        if (is_dir($templatesPath)) {
            $theme['templates'] = $this->getTemplateFiles($templatesPath);
        }

        // Get CSS files
        $cssPath = $themePath . '/css/';
        if (is_dir($cssPath)) {
            $theme['assets']['css'] = $this->getAssetFiles($cssPath, '.css');
        }

        // Get JS files
        $jsPath = $themePath . '/js/';
        if (is_dir($jsPath)) {
            $theme['assets']['js'] = $this->getAssetFiles($jsPath, '.js');
        }

        // Check for screenshot
        $possibleScreenshots = ['screenshot.png', 'screenshot.jpg', 'screenshot.jpeg', 'preview.png'];
        foreach ($possibleScreenshots as $screenshot) {
            $screenshotPath = $themePath . '/' . $screenshot;
            if (file_exists($screenshotPath)) {
                $theme['screenshot'] = '/themes/' . $themeName . '/' . $screenshot;
                break;
            }
        }

        return $theme;
    }

    /**
     * Activate a theme
     * @param string $themeName Theme name to activate
     * @return bool Success status
     */
    public function activateTheme(string $themeName): bool {
        // Validate theme exists
        if (!$this->isValidTheme($themeName)) {
            throw new \Exception("Theme '$themeName' not found or invalid");
        }

        try {
            // Update the active theme setting
            $result = SystemSettingsHelper::set('THEME_ACTIVE', $themeName);
            
            if ($result) {
                LogHelper::info('Theme activated successfully', ['theme' => $themeName]);
                return true;
            } else {
                LogHelper::error('Failed to update theme setting', ['theme' => $themeName]);
                return false;
            }
        } catch (\Exception $e) {
            LogHelper::error('Theme activation error', [
                'theme' => $themeName,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if a theme is valid
     * @param string $themeName Theme name to check
     * @return bool True if theme is valid
     */
    public function isValidTheme(string $themeName): bool {
        // Validate theme name - only alphanumeric, dash, underscore
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $themeName)) {
            return false;
        }

        $themePath = $this->themesPath . $themeName;

        // Check if directory exists
        if (!is_dir($themePath)) {
            return false;
        }

        // Check for required templates directory
        $templatesPath = $themePath . '/templates/';
        if (!is_dir($templatesPath)) {
            return false;
        }

        // Check for at least a basic template (home.tpl or layout.tpl)
        $requiredTemplates = ['home.tpl', 'layout.tpl'];
        $hasRequiredTemplate = false;
        
        foreach ($requiredTemplates as $template) {
            if (file_exists($templatesPath . $template)) {
                $hasRequiredTemplate = true;
                break;
            }
        }

        return $hasRequiredTemplate;
    }

    /**
     * Get template files from a directory
     * @param string $path Templates directory path
     * @return array List of template files
     */
    private function getTemplateFiles(string $path): array {
        $templates = [];
        
        if (!is_dir($path)) {
            return $templates;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'tpl') {
                $relativePath = str_replace($path, '', $file->getPathname());
                $templates[] = ltrim($relativePath, '/\\');
            }
        }

        sort($templates);
        return $templates;
    }

    /**
     * Get asset files from a directory
     * @param string $path Assets directory path
     * @param string $extension File extension to filter
     * @return array List of asset files
     */
    private function getAssetFiles(string $path, string $extension): array {
        $assets = [];
        
        if (!is_dir($path)) {
            return $assets;
        }

        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $path . $file;
            if (is_file($filePath) && substr($file, -strlen($extension)) === $extension) {
                $assets[] = $file;
            }
        }

        sort($assets);
        return $assets;
    }

    /**
     * Get the currently active theme name
     * @return string Active theme name
     */
    public function getActiveTheme(): string {
        return SystemSettingsHelper::get('THEME_ACTIVE') ?? 'default';
    }

    /**
     * Check if a theme has updates (placeholder for future implementation)
     * @param string $themeName Theme name
     * @return bool True if updates are available
     */
    public function hasUpdates(string $themeName): bool {
        // Placeholder for future update checking functionality
        return false;
    }
}
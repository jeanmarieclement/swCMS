<?php

namespace App\Core;

use App\Helpers\LogHelper;
use Smarty\Smarty;
use App\Helpers\SessionHelper;
use App\Helpers\SystemSettingsHelper;
use App\Core\HookSystem;

/**
 * View Class
 * Handles template rendering using Smarty
 */
class View
{
    private $smarty;
    private $settings;
    private $hookSystem;

    /**
     * Constructor - initializes Smarty
     */
    public function __construct()
    {
        // Path to Smarty library (manually included)
        require_once \ROOT_PATH . '/vendor/autoload.php';

        $this->settings = SystemSettingsHelper::all();
        $this->hookSystem = HookSystem::getInstance();


        $this->smarty = new Smarty();
        // Theme support
        $theme = $this->settings['THEME_ACTIVE'];
        $siteUrl = $this->settings['SITE_URL'];
        $themeTemplates = \PUBLIC_PATH . '/themes/' . $theme . '/templates';
        if (is_dir($themeTemplates)) {
            $this->smarty->setTemplateDir($themeTemplates);
            // Asset URL per il tema (usato nei template)
            $this->smarty->assign('theme_url', '/themes/' . $theme);
        } else {
            // Se la directory non esiste, fallback su default
            $this->smarty->setTemplateDir(\PUBLIC_PATH . '/themes/default/templates');
            $this->smarty->assign('theme_url', '/themes/default');
        }
        $this->smarty->setCompileDir(\VIEWS_PATH . '/compiled');
        $this->smarty->setCacheDir(\VIEWS_PATH . '/cache');
        $this->smarty->setConfigDir(\VIEWS_PATH . '/configs');

        // Create directories if they don't exist
        $this->createDirectoryIfNotExists(\VIEWS_PATH . '/compiled');
        $this->createDirectoryIfNotExists(\VIEWS_PATH . '/cache');
        $this->createDirectoryIfNotExists(\VIEWS_PATH . '/configs');
        $this->createDirectoryIfNotExists(\VIEWS_PATH . '/plugins');

        // Register custom plugins
        $this->registerCustomPlugins();

        // Configure caching and security
        $this->configureCaching();
        $this->configureSecuritySettings();

        // Set global template variables
        $this->smarty->assign('site_name', $this->settings['SITE_NAME']);
        $this->smarty->assign('site_url', $this->settings['SITE_URL']);
        $this->smarty->assign('admin_url', $this->settings['ADMIN_URL']);

        // Pass user authentication data to all templates
        $this->smarty->assign('is_logged_in', SessionHelper::hasValue('user_id'));
        if (SessionHelper::hasValue('user_id')) {
            $this->smarty->assign('user_id', SessionHelper::getValue('user_id'));
            $this->smarty->assign('user_email', SessionHelper::getValue('user_email'));
            $this->smarty->assign('user_role', SessionHelper::getValue('user_role'));
        }

        // Pass CSRF token to all templates for form protection
        $this->smarty->assign('csrf_token', \App\Helpers\CSRFHelper::getToken());
        $this->smarty->assign('csrf_field', \App\Helpers\CSRFHelper::getTokenField());
        $this->smarty->assign('csrf_meta', \App\Helpers\CSRFHelper::getTokenMeta());
    }

    /**
     * Register custom plugins from the plugins directory
     */
    private function registerCustomPlugins()
    {
        // Check if plugins directory exists
        $pluginsDir = \VIEWS_PATH . '/plugins';
        if (!is_dir($pluginsDir)) {
            return;
        }

        // Get all PHP files in the plugins directory
        $pluginFiles = glob($pluginsDir . '/*.php');

        foreach ($pluginFiles as $pluginFile) {
            // Extract plugin name and type from filename
            // Convention: function.plugin_name.php, modifier.plugin_name.php, etc.
            $fileName = basename($pluginFile, '.php');
            $parts = explode('.', $fileName);

            if (count($parts) >= 2) {
                $pluginType = $parts[0]; // function, modifier, block, etc.
                $pluginName = $parts[1]; // actual plugin name

                // Include the plugin file
                require_once $pluginFile;

                // Register the plugin based on naming convention
                $callbackName = "smarty_{$pluginType}_{$pluginName}";

                if (function_exists($callbackName)) {
                    $this->smarty->registerPlugin($pluginType, $pluginName, $callbackName);
                }
            }
        }
    }

    /**
     * Render a template with data
     *
     * @param string $template Template path
     * @param array $data Data to pass to the template
     * @param string $area Area: 'frontend' (default) o 'admin'
     */
    public function render($template, $data = [], $area = 'frontend')
    {
        // Security: Validate template name to prevent path traversal
        if (!$this->isValidTemplateName($template)) {
            throw new \Exception('Invalid template name: ' . htmlspecialchars($template));
        }

        // Fire before_render hook
        $this->hookSystem->doAction('before_render', $template, $data, $area);

        // Allow plugins to modify template name (with validation)
        $filteredTemplate = $this->hookSystem->applyFilters('template_name', $template, $area);
        if ($this->isValidTemplateName($filteredTemplate)) {
            $template = $filteredTemplate;
        }

        // Allow plugins to modify template data
        $data = $this->hookSystem->applyFilters('template_data', $data, $template, $area);
        // Admin and auth pages must never use cached output — they show live DB data
        $isAdminOrAuth = (
            $area === 'admin' || strpos($template, 'admin/') === 0 ||
            $area === 'auth' || strpos($template, 'auth/') === 0
        );
        if ($isAdminOrAuth) {
            $this->smarty->caching = Smarty::CACHING_OFF;
        }

        // Scegli la directory template in base all'area
        if (
            $area === 'admin' || strpos($template, 'admin/') === 0 ||
            $area === 'auth' || strpos($template, 'auth/') === 0 ||
            strpos($template, 'frontend/') === 0 || strpos($template, 'errors/') === 0
        ) {
            // Admin, Auth, Frontend con path, Errors: usa la directory classica
            $this->smarty->setTemplateDir(\VIEWS_PATH);
            $this->smarty->assign('theme_url', $this->settings['SITE_URL'] . '/themes/default/');
        } elseif ($this->isPluginTemplate($template)) {
            // Plugin template: cerca nella directory del plugin con fallback alle directory core
            $pluginName = $this->getPluginNameFromTemplate($template);
            $pluginViewsPath = \ROOT_PATH . '/plugins/' . $pluginName . '/views';
            if (is_dir($pluginViewsPath)) {
                // Imposta directory multipla: prima plugin, poi views core
                $templateDirs = [
                    $pluginViewsPath,
                    \VIEWS_PATH
                ];
                $this->smarty->setTemplateDir($templateDirs);
                $this->smarty->assign('theme_url', $this->settings['SITE_URL'] . '/themes/default/');
            } else {
                // Fallback alla directory views classica
                $this->smarty->setTemplateDir(\VIEWS_PATH);
                $this->smarty->assign('theme_url', $this->settings['SITE_URL'] . '/themes/default/');
            }
        } else {
            // Frontend template name only: usa la directory del tema
            $theme = $this->settings['THEME_ACTIVE'];
            $themeTemplates = \PUBLIC_PATH . '/themes/' . $theme . '/templates';
            if (is_dir($themeTemplates)) {
                $this->smarty->setTemplateDir($themeTemplates);
                $this->smarty->assign('theme_url', '/themes/' . $theme);
            } else {
                $this->smarty->setTemplateDir(\PUBLIC_PATH . '/themes/default/templates');
                $this->smarty->assign('theme_url', '/themes/default');
            }
        }
        // Allow plugins to modify template directory
        $templateDir = $this->smarty->getTemplateDir(0);
        $templateDir = $this->hookSystem->applyFilters('template_directory', $templateDir, $template, $area);
        if ($templateDir !== $this->smarty->getTemplateDir(0)) {
            $this->smarty->setTemplateDir($templateDir);
        }

        // Assign data to Smarty
        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        // Fire template_variables hook to allow plugins to add more variables
        $this->hookSystem->doAction('template_variables', $this->smarty, $template, $data, $area);
        // Display the template con fallback
        // Per i plugin template, Smarty gestisce automaticamente il fallback con l'array di directory
        if ($this->isPluginTemplate($template)) {
            // Lascia che Smarty gestisca il fallback con l'array di directory
            $templateExists = $this->smarty->templateExists($template . '.tpl');
            if (!$templateExists) {
                throw new \Exception("Template '{$template}.tpl' not found in plugin directories");
            }
        } else {
            // Gestione fallback manuale per template non-plugin
            $tplFile = $this->smarty->getTemplateDir(0) . $template . '.tpl';
            $fallbackTried = false;

            if (!file_exists($tplFile)) {
                // Se siamo in frontend, prova fallback su default
                if ($area !== 'admin' && $area !== 'auth' && strpos($template, 'admin/') !== 0 && strpos($template, 'auth/') !== 0) {
                    $defaultTplDir = \PUBLIC_PATH . '/themes/default/templates/';
                    $tplFile = $defaultTplDir . $template . '.tpl';
                    if (file_exists($tplFile)) {
                        $this->smarty->setTemplateDir($defaultTplDir);
                        $fallbackTried = true;
                    }
                }
                // Per admin/auth, fallback su VIEWS_PATH
                if (!$fallbackTried && ($area === 'admin' || $area === 'auth' || strpos($template, 'admin/') === 0 || strpos($template, 'auth/') === 0)) {
                    $tplFile = \VIEWS_PATH . '/' . $template . '.tpl';
                    if (file_exists($tplFile)) {
                        $this->smarty->setTemplateDir(\VIEWS_PATH);
                        $fallbackTried = true;
                    }
                }
            }
        }

        // Render the template
        $canRender = $this->isPluginTemplate($template) ?
            $this->smarty->templateExists($template . '.tpl') :
            (isset($tplFile) && file_exists($tplFile));

        if ($canRender) {
            try {
                // Fire before_template_render hook
                $this->hookSystem->doAction('before_template_render', $template, $area, $this->smarty);

                // Get template content and allow plugins to modify it
                ob_start();
                $this->smarty->display($template . '.tpl');
                $content = ob_get_clean();

                // Apply content filters
                $content = $this->hookSystem->applyFilters('template_content', $content, $template, $data, $area);

                // Fire after_template_render hook before output
                $this->hookSystem->doAction('after_template_render', $template, $area, $content);

                // Output the final content
                echo $content;
            } catch (\Exception $e) {
                LogHelper::error('Template Error: ' . $e->getMessage() . " | Template: $template");
                // Prova error 500
                if (file_exists(\VIEWS_PATH . '/errors/500.php')) {
                    require_once \VIEWS_PATH . '/errors/500.php';
                } else {
                    echo 'Error rendering template. Please check the error log for details.';
                }
            }
        } else {
            // Nessun template trovato: mostra pagina di errore
            LogHelper::error('Template non trovato: ' . $template . ' (area: ' . $area . ')');
            // Se esiste 403.php e l'utente non è autorizzato, mostra 403
            if (isset($data['error_code']) && $data['error_code'] == 403 && file_exists(\VIEWS_PATH . '/errors/403.php')) {
                require_once \VIEWS_PATH . '/errors/403.php';
            } elseif (file_exists(\VIEWS_PATH . '/errors/404.php')) {
                // Altrimenti mostra 404 se esiste
                require_once \VIEWS_PATH . '/errors/404.php';
            } elseif (file_exists(\VIEWS_PATH . '/errors/500.php')) {
                // Altrimenti mostra 500 se esiste
                require_once \VIEWS_PATH . '/errors/500.php';
            } else {
                echo 'Template not found.';
            }
        }
    }


    /**
     * Create a directory if it doesn't exist
     *
     * @param string $dir Directory path
     */
    private function createDirectoryIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Apply content hooks to text content
     * @param string $content Original content
     * @param string $context Context like 'post_content', 'page_content', etc.
     * @param array $data Additional data
     * @return string Modified content
     */
    public function applyContentHooks($content, $context = 'the_content', $data = [])
    {
        // Apply the content filter hook
        $content = $this->hookSystem->applyFilters($context, $content, $data);

        // Apply general content processing
        $content = $this->hookSystem->applyFilters('content_processing', $content, $context, $data);

        return $content;
    }

    /**
     * Fire action hooks for specific template areas
     * @param string $area Area name like 'cms_head', 'cms_footer', etc.
     * @param array $data Additional data
     */
    public function renderHookArea($area, $data = [])
    {
        ob_start();
        $this->hookSystem->doAction($area, $data);
        $content = ob_get_clean();

        // Allow filtering of hook area content
        $content = $this->hookSystem->applyFilters($area . '_content', $content, $data);

        echo $content;
    }

    /**
     * Get Smarty instance for plugins that need direct access
     * @return Smarty
     */
    public function getSmarty()
    {
        return $this->smarty;
    }

    /**
     * Add template variable accessible to all templates
     * @param string $name Variable name
     * @param mixed $value Variable value
     */
    public function addGlobalVariable($name, $value)
    {
        $this->smarty->assign($name, $value);
    }

    /**
     * Check if template exists
     * @param string $template Template name
     * @param string $area Template area
     * @return bool
     */
    public function templateExists($template, $area = 'frontend')
    {
        $currentDir = $this->smarty->getTemplateDir(0);

        if ($area === 'admin' || strpos($template, 'admin/') === 0) {
            $tplFile = \VIEWS_PATH . '/' . $template . '.tpl';
        } else {
            $tplFile = $currentDir . $template . '.tpl';
        }

        return file_exists($tplFile);
    }

    /**
     * Check if a template path belongs to a plugin
     *
     * @param string $template Template path
     * @return bool
     */
    private function isPluginTemplate($template)
    {
        // I template dei plugin seguono il pattern: plugin-name/template-path
        // e NON iniziano con admin/, auth/, frontend/, errors/
        if (
            strpos($template, 'admin/') === 0 ||
            strpos($template, 'auth/') === 0 ||
            strpos($template, 'frontend/') === 0 ||
            strpos($template, 'errors/') === 0
        ) {
            return false;
        }

        // Verifica se il template contiene un plugin name riconoscibile
        $parts = explode('/', $template);
        if (count($parts) >= 2) {
            $potentialPluginName = $parts[0];
            $pluginPath = \ROOT_PATH . '/plugins/' . $potentialPluginName;
            return is_dir($pluginPath);
        }

        return false;
    }

    /**
     * Extract plugin name from template path
     *
     * @param string $template Template path
     * @return string
     */
    private function getPluginNameFromTemplate($template)
    {
        $parts = explode('/', $template);
        return $parts[0] ?? '';
    }

    /**
     * Configure caching settings based on environment
     */
    private function configureCaching()
    {
        $debugMode = $this->settings['DEBUG_MODE'] ?? false;

        if ($debugMode) {
            $this->smarty->caching = Smarty::CACHING_OFF;
            $this->smarty->force_compile = true;
            $this->smarty->compile_check = true;
        } else {
            $this->smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
            $this->smarty->cache_lifetime = 3600;
            $this->smarty->compile_check = false;
        }
    }

    /**
     * Configure security settings for Smarty
     */
    private function configureSecuritySettings()
    {
        // Basic security: restrict template directories
        $this->smarty->use_include_path = false;

        // Disable potentially dangerous features in production
        if (!($this->settings['DEBUG_MODE'] ?? false)) {
            $this->smarty->error_reporting = E_ALL & ~E_NOTICE;
        }
    }

    /**
     * Validate template name for security
     *
     * @param string $template Template name
     * @return bool True if valid
     */
    private function isValidTemplateName($template)
    {
        // Prevent path traversal attacks
        if (strpos($template, '../') !== false || strpos($template, '..\\') !== false) {
            return false;
        }

        // Only allow alphanumeric characters, hyphens, underscores, and forward slashes
        if (!preg_match('/^[a-zA-Z0-9\/_-]+$/', $template)) {
            return false;
        }

        // Prevent extremely long template names
        if (strlen($template) > 255) {
            return false;
        }

        return true;
    }

    /**
     * Clear template cache
     *
     * @param string|null $template Specific template or null for all
     */
    public function clearCache($template = null)
    {
        if ($template) {
            $this->smarty->clearCache($template . '.tpl');
        } else {
            $this->smarty->clearAllCache();
        }
    }
}

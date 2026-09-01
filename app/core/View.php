<?php

namespace App\Core;

use App\Helpers\LogHelper;
use Smarty\Smarty;
use App\Helpers\SessionHelper;
use App\Helpers\SystemSettingsHelper;
use App\Helpers\RequestHelper;
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
                $restoreCaching = $this->suspendCachingForUncacheableRequest();
                try {
                    $this->smarty->display($template . '.tpl', self::cacheIdForRequest());
                } finally {
                    // Without this, a template that throws would leave caching
                    // switched off for every later render on this instance.
                    if ($restoreCaching !== null) {
                        $this->smarty->caching = $restoreCaching;
                    }
                }
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
     * Build the Smarty cache id for the current request
     *
     * Smarty keys a cached page by template name alone, so without this every
     * URL rendered through the same template would share a single cached page.
     *
     * @param string|null $uri Request URI, defaults to the current one
     * @return string
     */
    public static function cacheIdForRequest($uri = null)
    {
        if ($uri === null) {
            $uri = RequestHelper::server('REQUEST_URI', '/');
        }

        $path = parse_url($uri, PHP_URL_PATH);
        $params = self::cacheableParamsFromUri($uri);

        // Sorted, so ?page=2&sort=x and ?sort=x&page=2 are one cache entry
        // rather than two copies of the same page.
        ksort($params);

        $key = ($path === null || $path === false ? '/' : $path)
            . ($params === [] ? '' : '?' . http_build_query($params));

        // Hashed so the id never contains '|', which Smarty reads as the cache
        // group separator, nor anything else awkward in a cache filename.
        return sha1($key);
    }

    /**
     * Query parameters that are allowed to vary a cached page
     *
     * Each entry maps a parameter name to a pattern its value must match. The
     * pattern is not decoration: the parameter is a dimension of the cache key,
     * so anything that accepts arbitrary values lets a visitor mint arbitrary
     * cache files. Pagination is therefore capped at five digits.
     *
     * Plugins that route on their own parameters register them through the
     * page_cache_query_params filter; a parameter with no pattern here makes
     * the request bypass the cache rather than share a page with different
     * input.
     *
     * @return array name => value pattern
     */
    public static function cacheableQueryParams()
    {
        $params = [
            'page' => '/^[1-9][0-9]{0,4}$/',
            'comment_page' => '/^[1-9][0-9]{0,4}$/',
        ];

        $filtered = HookSystem::getInstance()->applyFilters('page_cache_query_params', $params);

        return is_array($filtered) ? $filtered : $params;
    }

    /**
     * Query parameters that are known not to change the page
     *
     * Analytics and ad-click parameters ride along on inbound links without
     * affecting what is rendered. They are dropped from the cache key rather
     * than making the request bypass the cache, so a campaign link is served
     * from cache like any other and still does not mint an entry of its own.
     *
     * @return array List of parameter names
     */
    public static function ignorableQueryParams()
    {
        $params = [
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id',
            'gclid', 'gbraid', 'wbraid', 'dclid', 'fbclid', 'msclkid', 'ttclid', 'twclid',
            'yclid', 'igshid', 'mc_cid', 'mc_eid', '_ga', '_gl', 'ref', 'referrer',
        ];

        $filtered = HookSystem::getInstance()->applyFilters('page_cache_ignored_query_params', $params);

        return is_array($filtered) ? array_values(array_filter($filtered, 'is_string')) : $params;
    }

    /**
     * Parse a URI's query string, dropping the parameters known to be irrelevant
     *
     * @param string $uri
     * @return array name => value
     */
    private static function significantParamsFromUri($uri)
    {
        $query = parse_url($uri, PHP_URL_QUERY);

        if ($query === null || $query === false || $query === '') {
            return [];
        }

        parse_str($query, $parsed);

        return array_diff_key($parsed, array_flip(self::ignorableQueryParams()));
    }

    /**
     * Whether a value is acceptable for an allowlisted parameter
     *
     * @param mixed $value
     * @param string $pattern
     * @return bool
     */
    private static function isAcceptableParamValue($value, $pattern)
    {
        // Array-shaped input (?page[]=1) is neither renderable as a key nor
        // meaningful to the routes that read these parameters.
        if (!is_scalar($value)) {
            return false;
        }

        return is_string($pattern) && preg_match($pattern, (string) $value) === 1;
    }

    /**
     * Extract the allowlisted query parameters from a URI
     *
     * @param string $uri
     * @return array name => value
     */
    private static function cacheableParamsFromUri($uri)
    {
        $allowed = self::cacheableQueryParams();
        $params = [];

        foreach (self::significantParamsFromUri($uri) as $name => $value) {
            if (isset($allowed[$name]) && self::isAcceptableParamValue($value, $allowed[$name])) {
                $params[$name] = $value;
            }
        }

        return $params;
    }

    /**
     * Whether the current request may be served from, or stored in, the page cache
     *
     * The cache id is keyed on the path plus the allowlisted parameters only, so
     * a request carrying anything else would otherwise be answered with a page
     * rendered for different input. Rather than guess, such requests bypass the
     * cache entirely: that, together with the value patterns, is what stops a
     * crawler or a tracking link from minting an unbounded number of cache
     * files, which is what made this a filesystem-growth problem in the first
     * place.
     *
     * @param string|null $uri Request URI, defaults to the current one
     * @param string|null $method Request method, defaults to the current one
     * @return bool
     */
    public static function isRequestCacheable($uri = null, $method = null)
    {
        if ($method === null) {
            $method = RequestHelper::server('REQUEST_METHOD', 'GET');
        }

        // A cached POST response would be replayed to everyone who later GETs
        // the same URL.
        if (strtoupper((string) $method) !== 'GET') {
            return false;
        }

        if ($uri === null) {
            $uri = RequestHelper::server('REQUEST_URI', '/');
        }

        $allowed = self::cacheableQueryParams();

        foreach (self::significantParamsFromUri($uri) as $name => $value) {
            // Unknown to the cache key, or carrying a value the key was never
            // meant to hold.
            if (!isset($allowed[$name]) || !self::isAcceptableParamValue($value, $allowed[$name])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Turn caching off for a request the cache id cannot represent
     *
     * @return int|null The caching mode to restore afterwards, or null if untouched
     */
    private function suspendCachingForUncacheableRequest()
    {
        if ($this->smarty->caching === Smarty::CACHING_OFF) {
            return null;
        }

        if (self::isRequestCacheable()) {
            return null;
        }

        $previous = $this->smarty->caching;
        $this->smarty->caching = Smarty::CACHING_OFF;

        return $previous;
    }

    /**
     * Delete cache files that are past their lifetime
     *
     * Smarty stops *serving* an entry once it expires but never removes the
     * file, so without this the cache directory only ever grows. Run on a small
     * fraction of cached requests, in the spirit of PHP's own session GC.
     *
     * @return void
     */
    private function collectExpiredCache()
    {
        $probability = defined('PAGE_CACHE_GC_PROBABILITY') ? (int) PAGE_CACHE_GC_PROBABILITY : 100;

        if ($probability < 1 || random_int(1, $probability) !== 1) {
            return;
        }

        $lifetime = (int) $this->smarty->cache_lifetime;

        // clearAllCache() deletes entries older than the age it is given, and
        // treats 0 as "older than now" — every entry. A non-positive lifetime
        // means Smarty is not expiring anything on a schedule, so there is
        // nothing for a sweep to collect and wiping the cache would be wrong.
        if ($lifetime <= 0) {
            return;
        }

        try {
            // clearAllCache() with an age deletes only entries older than it.
            $this->smarty->clearAllCache($lifetime);
        } catch (\Exception $e) {
            LogHelper::warning('Page cache garbage collection failed: ' . $e->getMessage());
        }
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
            return;
        }

        $this->smarty->compile_check = false;

        // Full-page caching is opt-in through PAGE_CACHE, which .env.example has
        // always defaulted to false. It bakes whatever the first visitor saw into
        // the page every later visitor gets, and the frontend templates embed the
        // CSRF token and flash messages, so it is only safe once those regions are
        // wrapped in {nocache}.
        $pageCache = defined('PAGE_CACHE') ? PAGE_CACHE : false;

        if (!$pageCache) {
            $this->smarty->caching = Smarty::CACHING_OFF;
            return;
        }

        $this->smarty->caching = Smarty::CACHING_LIFETIME_CURRENT;
        $this->smarty->cache_lifetime = defined('PAGE_CACHE_LIFETIME') ? (int) PAGE_CACHE_LIFETIME : 1800;

        $this->collectExpiredCache();
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
            return $this->smarty->clearCache($template . '.tpl');
        }

        return $this->smarty->clearAllCache();
    }

    /**
     * Delete every compiled template
     *
     * @return int Number of files removed
     */
    public function clearCompiled()
    {
        return $this->smarty->clearCompiledTemplate();
    }
}

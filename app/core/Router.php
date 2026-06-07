<?php

namespace App\Core;
/**
 * Router Class
 * Handles URL routing and dispatches to appropriate controllers
 */

use App\Helpers\LogHelper;
use App\Helpers\SystemSettingsHelper;
use App\Helpers\RequestHelper;

class Router {
    private $routes = [];
    private $params = [];
    private $compiledRoutes = [];
    private static $routeCache = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->loadRoutes();
    }
    
    /**
     * Load routes from configuration
     */
    private function loadRoutes() {
        // Default route
        $this->addRoute('', ['controller' => 'Home', 'action' => 'index']);
        
        // Load plugin routes
        $this->loadPluginRoutes();
        
        // Admin routes
        $this->addRoute('admin', ['controller' => 'Admin', 'action' => 'index']);
        $this->addRoute('admin/dashboard', ['controller' => 'Admin', 'action' => 'dashboard']);
        $this->addRoute('admin/clear-cache', ['controller' => 'Admin', 'action' => 'clearCache']);
        $this->addRoute('admin/edit-content', ['controller' => 'Admin', 'action' => 'editContent']);
        
        // Admin User Management routes
        $this->addRoute('admin/users', ['controller' => 'User', 'action' => 'index']);
        $this->addRoute('admin/users/create', ['controller' => 'User', 'action' => 'create']);
        $this->addRoute('admin/users/edit/([0-9]+)', ['controller' => 'User', 'action' => 'edit']);
        $this->addRoute('admin/users/delete/([0-9]+)', ['controller' => 'User', 'action' => 'delete']);

        // Admin Role Management routes
        $this->addRoute('admin/roles', ['controller' => 'Role', 'action' => 'index']);
        $this->addRoute('admin/roles/create', ['controller' => 'Role', 'action' => 'create']);
        $this->addRoute('admin/roles/edit/([0-9]+)', ['controller' => 'Role', 'action' => 'edit']);
        $this->addRoute('admin/roles/delete/([0-9]+)', ['controller' => 'Role', 'action' => 'delete']);
        
        // Admin Article Management routes
        $this->addRoute('admin/articles', ['controller' => 'Article', 'action' => 'index']);
        $this->addRoute('admin/articles/create', ['controller' => 'Article', 'action' => 'create']);
        $this->addRoute('admin/articles/edit/([0-9]+)', ['controller' => 'Article', 'action' => 'edit']);
        $this->addRoute('admin/articles/delete/([0-9]+)', ['controller' => 'Article', 'action' => 'delete']);
        $this->addRoute('admin/articles/status/([0-9]+)/([a-z]+)', ['controller' => 'Article', 'action' => 'status']);

        // Admin Category Management routes
        $this->addRoute('admin/categories', ['controller' => 'Category', 'action' => 'index']);
        $this->addRoute('admin/categories/create', ['controller' => 'Category', 'action' => 'create']);
        $this->addRoute('admin/categories/store', ['controller' => 'Category', 'action' => 'store']);
        $this->addRoute('admin/categories/edit/([0-9]+)', ['controller' => 'Category', 'action' => 'edit']);
        $this->addRoute('admin/categories/update/([0-9]+)', ['controller' => 'Category', 'action' => 'update']);
        $this->addRoute('admin/categories/delete/([0-9]+)', ['controller' => 'Category', 'action' => 'delete']);
        $this->addRoute('admin/categories/destroy/([0-9]+)', ['controller' => 'Category', 'action' => 'destroy']);
        $this->addRoute('admin/categories/ajax_create', ['controller' => 'Category', 'action' => 'ajaxCreate']);

        // Admin Comments Management routes
        $this->addRoute('admin/comments', ['controller' => 'Comments', 'action' => 'index']);
        $this->addRoute('admin/comments/approve', ['controller' => 'Comments', 'action' => 'approve']);
        $this->addRoute('admin/comments/spam', ['controller' => 'Comments', 'action' => 'spam']);
        $this->addRoute('admin/comments/reply', ['controller' => 'Comments', 'action' => 'reply']);
        $this->addRoute('admin/comments/delete', ['controller' => 'Comments', 'action' => 'delete']);


        // Admin Tag Management routes
        $this->addRoute('admin/tags', ['controller' => 'Tag', 'action' => 'index']);
        $this->addRoute('admin/tags/create', ['controller' => 'Tag', 'action' => 'create']);
        $this->addRoute('admin/tags/store', ['controller' => 'Tag', 'action' => 'store']);
        $this->addRoute('admin/tags/edit', ['controller' => 'Tag', 'action' => 'edit']);
        $this->addRoute('admin/tags/update', ['controller' => 'Tag', 'action' => 'update']);
        $this->addRoute('admin/tags/delete', ['controller' => 'Tag', 'action' => 'delete']);
        $this->addRoute('admin/tags/ajax-create', ['controller' => 'Tag', 'action' => 'ajaxCreate']);
        $this->addRoute('admin/tags/ajax-list', ['controller' => 'Tag', 'action' => 'ajaxList']);
        
        // Admin Profile route
        $this->addRoute('admin/profile', ['controller' => 'Profile', 'action' => 'index']);
        // Admin Media Library routes
        $this->addRoute('admin/media', ['controller' => 'Media', 'action' => 'index']);
        $this->addRoute('admin/media/upload', ['controller' => 'Media', 'action' => 'upload']);
        $this->addRoute('admin/media/delete/([0-9]+)', ['controller' => 'Media', 'action' => 'delete']);
        $this->addRoute('admin/media/edit/([0-9]+)', ['controller' => 'Media', 'action' => 'edit']);
        $this->addRoute('admin/media/update/([0-9]+)', ['controller' => 'Media', 'action' => 'update']);
        $this->addRoute('admin/media/get/([0-9]+)', ['controller' => 'Media', 'action' => 'get']);
        $this->addRoute('admin/media/view/([0-9]+)', ['controller' => 'Media', 'action' => 'view']);
        $this->addRoute('admin/media/api/list', ['controller' => 'Media', 'action' => 'ajaxList']);
        $this->addRoute('admin/media/api/upload', ['controller' => 'Media', 'action' => 'ajaxUpload']);
        
        // Admin Page Management routes
        $this->addRoute('admin/pages', ['controller' => 'Page', 'action' => 'index']);
        $this->addRoute('admin/pages/create', ['controller' => 'Page', 'action' => 'create']);
        $this->addRoute('admin/pages/edit/([0-9]+)', ['controller' => 'Page', 'action' => 'edit']);
        $this->addRoute('admin/pages/store', ['controller' => 'Page', 'action' => 'store']);
        $this->addRoute('admin/pages/update/([0-9]+)', ['controller' => 'Page', 'action' => 'update']);
        $this->addRoute('admin/pages/delete/([0-9]+)', ['controller' => 'Page', 'action' => 'delete']);
        $this->addRoute('admin/pages/status', ['controller' => 'Page', 'action' => 'status']);
        
        // Admin Page Revision routes
        $this->addRoute('admin/pages/revisions/([0-9]+)', ['controller' => 'Page', 'action' => 'revisions']);
        $this->addRoute('admin/pages/view-revision/([0-9]+)/([0-9]+)', ['controller' => 'Page', 'action' => 'viewRevision']);
        $this->addRoute('admin/pages/compare-revisions/([0-9]+)/([0-9]+)/([0-9]+)', ['controller' => 'Page', 'action' => 'compareRevisions']);
        $this->addRoute('admin/pages/restore-revision/([0-9]+)/([0-9]+)', ['controller' => 'Page', 'action' => 'restoreRevision']);
        $this->addRoute('admin/pages/delete-revision/([0-9]+)/([0-9]+)', ['controller' => 'Page', 'action' => 'deleteRevision']);
        
        // Admin Settings routes
        $this->addRoute('admin/settings', ['controller' => 'Settings', 'action' => 'index']);
        $this->addRoute('admin/settings/save', ['controller' => 'Settings', 'action' => 'save']);
        
        // Admin Theme Management routes
        $this->addRoute('admin/themes', ['controller' => 'Theme', 'action' => 'index']);
        $this->addRoute('admin/themes/details', ['controller' => 'Theme', 'action' => 'details']);
        $this->addRoute('admin/themes/activate', ['controller' => 'Theme', 'action' => 'activate']);
        $this->addRoute('admin/themes/install', ['controller' => 'Theme', 'action' => 'install']);
        $this->addRoute('admin/themes/delete', ['controller' => 'Theme', 'action' => 'delete']);
        
        // Admin Plugin Management routes
        $this->addRoute('admin/plugins', ['controller' => 'Plugin', 'action' => 'index']);
        $this->addRoute('admin/plugins/details', ['controller' => 'Plugin', 'action' => 'details']);
        $this->addRoute('admin/plugins/activate', ['controller' => 'Plugin', 'action' => 'activate']);
        $this->addRoute('admin/plugins/deactivate', ['controller' => 'Plugin', 'action' => 'deactivate']);
        $this->addRoute('admin/plugins/configure', ['controller' => 'Plugin', 'action' => 'configure']);
        $this->addRoute('admin/plugins/install', ['controller' => 'Plugin', 'action' => 'install']);
        $this->addRoute('admin/plugins/delete', ['controller' => 'Plugin', 'action' => 'delete']);


        // Admin Menu Management routes
        $this->addRoute('admin/menus', ['controller' => 'Menu', 'action' => 'index']);
        $this->addRoute('admin/menus/create', ['controller' => 'Menu', 'action' => 'create']);
        $this->addRoute('admin/menus/store', ['controller' => 'Menu', 'action' => 'store']);
        $this->addRoute('admin/menus/edit/([0-9]+)', ['controller' => 'Menu', 'action' => 'edit']);
        $this->addRoute('admin/menus/update/([0-9]+)', ['controller' => 'Menu', 'action' => 'update']);
        $this->addRoute('admin/menus/delete/([0-9]+)', ['controller' => 'Menu', 'action' => 'delete']);
        
        // Auth routes
        $this->addRoute('auth/login', ['controller' => 'Auth', 'action' => 'login']);
        $this->addRoute('auth/logout', ['controller' => 'Auth', 'action' => 'logout']);
        $this->addRoute('auth/register', ['controller' => 'Auth', 'action' => 'register']);
        // Admin logout route (redirects to auth/logout)
        $this->addRoute('admin/logout', ['controller' => 'Auth', 'action' => 'logout']);
        // unauthorized
        $this->addRoute('unauthorized', ['controller' => 'Auth', 'action' => 'unauthorized']);
        
        // Comment routes
        $this->addRoute('comments/store', ['controller' => 'Comment', 'action' => 'store']);
        $this->addRoute('comments/get', ['controller' => 'Comment', 'action' => 'getComments']);
        
        // Frontend content routes - slug-based
        $this->addRoute('article/([a-zA-Z0-9\-]+)', ['controller' => 'Article', 'action' => 'show']);
        $this->addRoute('page/([a-zA-Z0-9\-]+)', ['controller' => 'Page', 'action' => 'show']);
        
        // Plugin routes are loaded dynamically via PluginRoutesManager (app/services/PluginRoutesManager.php)
    }
    
    /**
     * Add a route to the routing table
     * 
     * @param string $route The route URL
     * @param array $params Parameters (controller, action, etc.)
     */
    public function addRoute($route, $params = []) {
        // Convert the route to a regular expression for matching
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        
        $this->routes[$route] = $params;
    }
    
    /**
     * Match the route to the routes in the routing table
     * 
     * @param string $url The route URL
     * @return boolean True if a match was found, false otherwise
     */
    private function match($url) {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
             
                // Get named capture group values
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                
                // Store purely numeric capture groups (unnamed groups from patterns like ([0-9]+)).
                // Named groups already handled above; count only integer-keyed entries to avoid
                // going out of bounds (preg_match puts named groups at both string and int keys).
                $numericMatches = array_filter($matches, 'is_int', ARRAY_FILTER_USE_KEY);
                if (count($numericMatches) > 1) {
                    foreach (array_slice(array_values($numericMatches), 1) as $idx => $val) {
                        $params[$idx] = $val;
                    }
                }

                $this->params = $params;

                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Dispatch the route to the appropriate controller and action
     */
    public function dispatch() {
        $url = $this->getUrl();
        if ($this->match($url)) {
      
            $controller = $this->getControllerName();
            $isAdminRoute = strpos($url, 'admin/') === 0;
            $frontendFile = \FRONTEND_CONTROLLERS_PATH . '/' . $controller . 'Controller.php';
            $adminFile = \ADMIN_CONTROLLERS_PATH . '/' . $controller . 'Controller.php';

            // Try to find controller in plugins first, then core directories
            $pluginControllerFile = $this->findPluginController($controller, $isAdminRoute);
            
            // Determine which controller to load based on route type
            if ($pluginControllerFile) {
                // Plugin controller found
                require_once $pluginControllerFile;
                $controllerClass = 'App\\Controllers\\Admin\\' . $controller . 'Controller';
                $controllerObject = new $controllerClass($this->params);
            } elseif ($isAdminRoute) {
                // Admin route - check admin first, then frontend as fallback
                if (file_exists($adminFile)) {
                    require_once $adminFile;
                    $controllerClass = 'App\\Controllers\\Admin\\' . $controller . 'Controller';
                    $controllerObject = new $controllerClass($this->params);
                } elseif (file_exists($frontendFile)) {
                    require_once $frontendFile;
                    $controllerClass = 'App\\Controllers\\Frontend\\' . $controller . 'Controller';
                    $controllerObject = new $controllerClass($this->params);
                } else {
                    $this->handleError(404, "Controller '$controller' not found");
                    return;
                }
            } else {
                // Frontend route - check frontend first, then admin as fallback
                if (file_exists($frontendFile)) {
                    require_once $frontendFile;
                    $controllerClass = 'App\\Controllers\\Frontend\\' . $controller . 'Controller';
                    $controllerObject = new $controllerClass($this->params);
                } elseif (file_exists($adminFile)) {
                    require_once $adminFile;
                    $controllerClass = 'App\\Controllers\\Admin\\' . $controller . 'Controller';
                    $controllerObject = new $controllerClass($this->params);
                } else {
                    $this->handleError(404, "Controller '$controller' not found");
                    return;
                }
            }
                
            $action = $this->params['action'] ?? 'index';
            $action = $this->convertToCamelCase($action) . 'Action';
                
            if (method_exists($controllerObject, $action)) {
                $controllerObject->$action();
            } else {
                // Action not found
                $this->handleError(404, "Action '$action' not found in controller '$controllerClass'");
            }
        } else {
            // No route match
            $this->handleError(404, "No route matched for URL '$url'");
        }
       
    }
    
    /**
     * Get the URL from the query parameters
     * 
     * @return string The URL
     */
private function getUrl() {
    $uri = RequestHelper::server('REQUEST_URI', '');
    $uri = explode('?', $uri, 2)[0]; // Only the path, without query string
    $uri = trim($uri, '/');
    return $uri;
}
    
    /**
     * Get the controller name
     * 
     * @return string The controller name
     */
    private function getControllerName() {
        return $this->convertToStudlyCaps($this->params['controller'] ?? 'Home');
    }
    
    /**
     * Convert string to StudlyCaps
     * e.g. post-authors => PostAuthors
     * 
     * @param string $string The string to convert
     * @return string The converted string
     */
    private function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }
    
    /**
     * Convert string to camelCase
     * e.g. add-new => addNew
     * 
     * @param string $string The string to convert
     * @return string The converted string
     */
    private function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }
    
    /**
     * Load plugin routes from generated file
     */
    private function loadPluginRoutes() {
        $pluginRoutesFile = __DIR__ . '/plugin_routes_include.php';
        
        if (file_exists($pluginRoutesFile)) {
            // Include the generated routes file
            // The file contains $this->addRoute() calls
            include $pluginRoutesFile;
        }
    }
    
    /**
     * Handle errors
     * 
     * @param int $code HTTP status code
     * @param string $message Error message
     */
    private function handleError($code, $message) {
        http_response_code($code);
        
        // Log the error with appropriate level based on error code
        $level = ($code >= 500) ? 'error' : 'warning';
        
        LogHelper::logError("Router error: $message", $level, [
            'code' => $code,
            'url' => $this->getUrl(),
            'request_uri' => RequestHelper::server('REQUEST_URI', 'unknown'),
            'http_referer' => RequestHelper::server('HTTP_REFERER', 'none'),
            'method' => RequestHelper::method(),
            'params' => $this->params
        ]);
        
        if (SystemSettingsHelper::get('DEBUG_MODE')) {
            echo "<h1>Error $code</h1>";
            echo "<p>$message</p>";
            echo "<p>URL: {$this->getUrl()}</p>";
            echo "<pre>" . print_r($this->params, true) . "</pre>";
        } else {
            // In production, show appropriate error page
            if (file_exists(\VIEWS_PATH . '/errors/' . $code . '.php')) {
                require_once \VIEWS_PATH . '/errors/' . $code . '.php';
            } else {
                // Fallback to 500 error page if specific error page doesn't exist
                require_once \VIEWS_PATH . '/errors/500.php';
            }
        }
        
        exit;
    }
    
    /**
     * Find controller file in plugins
     * 
     * @param string $controller Controller name
     * @param bool $isAdminRoute Whether this is an admin route
     * @return string|null Path to controller file or null if not found
     */
    private function findPluginController($controller, $isAdminRoute = true) {
        if (!$isAdminRoute) {
            return null; // For now, only support admin plugin controllers
        }
        
        // Check common plugin directories that might contain this controller
        $pluginsDir = \ROOT_PATH . '/plugins';
        if (!is_dir($pluginsDir)) {
            return null;
        }
        
        // Scan all plugin directories for the controller
        $plugins = scandir($pluginsDir);
        foreach ($plugins as $plugin) {
            if ($plugin === '.' || $plugin === '..') {
                continue;
            }
            
            $pluginPath = $pluginsDir . '/' . $plugin;
            if (!is_dir($pluginPath)) {
                continue;
            }
            
            $controllerFile = $pluginPath . '/controllers/' . $controller . 'Controller.php';
            if (file_exists($controllerFile)) {
                return $controllerFile;
            }
        }
        
        return null;
    }
}

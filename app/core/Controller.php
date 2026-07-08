<?php

namespace App\Core;

use App\Core\View;
use App\Helpers\CSRFHelper;
use App\Helpers\LogHelper;
use App\Helpers\RedirectHelper;
use App\Helpers\SecurityHelper;
use App\Services\RoleService;
use App\Helpers\SessionHelper;
use App\Helpers\SystemSettingsHelper;
use App\Helpers\RequestHelper;

/**
 * Base Controller Class
 * All controllers will extend this class
 */
abstract class Controller
{
    protected $params = [];
    protected $view;
    protected $roleService;
    protected $settings;
    protected $commonData = [];

    /**
     * Constructor
     *
     * @param array $params Route parameters
     */
    public function __construct($params = [])
    {
        $this->params = is_array($params) ? $params : [];
        $this->view = new View();
        $this->roleService = new RoleService();
        $this->settings = SystemSettingsHelper::all();

        $this->initializeCommonData();
    }

    /**
     * Default action method
     */
    abstract public function indexAction();

    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods.
     *
     * @param string $name Method name
     * @param array $arguments Arguments passed to the method
     * @return void
     */
    public function __call($name, $arguments)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $arguments);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter - called before an action method
     *
     * @return boolean
     */
    protected function before()
    {
        return true;
    }

    /**
     * After filter - called after an action method
     *
     * @return void
     */
    protected function after()
    {
        // Can be overridden in child classes
    }

    /**
     * Render a view template with the given data
     *
     * @param string $template The template file to render
     * @param array $data Data to pass to the view
     * @return void
     */
    /**
     * Initialize common data used across all controllers
     */
    private function initializeCommonData()
    {
        $this->commonData = [
            'site_name' => $this->settings['SITE_NAME'] ?? '',
            'site_url' => $this->settings['SITE_URL'] ?? '',
            'admin_url' => $this->settings['ADMIN_URL'] ?? '',
            'settings' => $this->settings,
            'roleService' => $this->roleService,
            'is_logged_in' => SessionHelper::hasValue('user_id')
        ];

        if (SessionHelper::hasValue('user_id')) {
            $this->commonData['user'] = [
                'id' => SessionHelper::getValue('user_id'),
                'username' => SessionHelper::getValue('user_username') ?? '',
                'email' => SessionHelper::getValue('user_email') ?? '',
                'display_name' => SessionHelper::getValue('user_display_name') ?? '',
                'role' => SessionHelper::getValue('user_role') ?? ''
            ];
        }
    }

    /**
     * Render a view template with the given data
     *
     * @param string $template The template file to render
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function render($template, $data = [])
    {
        // Merge common data with provided data, prioritizing provided data
        $data = array_merge($this->commonData, $data);

        // Add flash message if available
        $flashMessage = SessionHelper::getFlashMessage();
        if ($flashMessage) {
            $data['flash'] = $flashMessage;
        }

        // Render the template with merged data
        try {
            $this->view->render($template, $data);
        } catch (\Exception $e) {
            LogHelper::error("Template error: " . $e->getMessage());
            $this->handleRenderError($e, $template);
        }
    }

    /**
     * Redirect to a different page
     *
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect($url)
    {
        RedirectHelper::redirect($url);
    }

    /**
     * Validate the CSRF token of the current request.
     * On failure: flash message + security log + redirect (terminates the request).
     *
     * @param string $redirectUrl Where to send the user when the token is invalid
     * @param string $context Short label for the security log (e.g. "plugin activation")
     * @return bool True when the token is valid
     */
    protected function requireCsrf(string $redirectUrl, string $context = 'request'): bool
    {
        if (CSRFHelper::validateRequest()) {
            return true;
        }

        SessionHelper::setFlashMessage('Invalid CSRF token. Please try again.', 'error');
        LogHelper::warning(
            'CSRF validation failed for ' . $context . ' from IP: ' . RequestHelper::server('REMOTE_ADDR', 'unknown')
        );
        RedirectHelper::redirect($redirectUrl);
        return false;
    }

    /**
     * Set a flash message to be displayed on the next page
     *
     * @param string $type The type of message (success, error, warning, info)
     * @param string $message The message text
     * @return void
     */
    protected function setFlashMessage($type, $message)
    {
        SessionHelper::setFlashMessage($message, $type);
    }

    /**
     * Handle rendering errors
     *
     * @param \Exception $e The exception
     * @param string $template The template that failed to render
     */
    private function handleRenderError(\Exception $e, $template)
    {
        if (!headers_sent()) {
            http_response_code(500);
        }

        // Determine if we're in production mode
        $isProduction = $this->isProductionEnvironment();

        if (!$isProduction) {
            // Debug mode - show detailed error information for developers
            echo "<h1>Template Error</h1>";
            echo "<p>Template: {$template}</p>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            // Production mode - show generic error page, no sensitive details
            if (file_exists(\VIEWS_PATH . '/errors/500.php')) {
                require_once \VIEWS_PATH . '/errors/500.php';
            } else {
                echo '<!DOCTYPE html>';
                echo '<html><head><title>Error</title></head><body>';
                echo '<h1>Something went wrong</h1>';
                echo '<p>We\'re sorry, but something went wrong. Please try again later.</p>';
                echo '<p><a href="/">Return to homepage</a></p>';
                echo '</body></html>';
            }
        }
    }

    /**
     * Determine if the application is running in production environment
     *
     * @return bool
     */
    private function isProductionEnvironment()
    {
        // Check APP_ENV environment variable first
        $appEnv = getenv('APP_ENV');
        if ($appEnv !== false) {
            return $appEnv === 'production';
        }

        // Check DEBUG_MODE setting
        if (isset($this->settings['DEBUG_MODE'])) {
            return !$this->settings['DEBUG_MODE'];
        }

        // Check display_errors INI setting
        if (!ini_get('display_errors')) {
            return true;
        }

        // Default to production (safe mode) if uncertain
        return true;
    }

    /**
     * Get sanitized request parameter
     *
     * @deprecated Use RequestHelper::get() or RequestHelper::post() instead
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @param string $method Request method (GET, POST)
     * @param bool $sanitize Whether to apply HTML sanitization
     * @return mixed Sanitized parameter value
     */
    protected function getParam($key, $default = null, $method = 'GET', $sanitize = true)
    {
        // Use RequestHelper for secure parameter retrieval
        $filter = $sanitize ? 'string' : 'raw';

        switch (strtoupper($method)) {
            case 'POST':
                return RequestHelper::post($key, $default, $filter);
            case 'GET':
            default:
                return RequestHelper::get($key, $default, $filter);
        }
    }

    /**
     * Validate required parameters
     *
     * @param array $required Required parameter keys
     * @param string $method Request method
     * @return array Array of missing parameters
     */
    protected function validateRequiredParams(array $required, $method = 'POST')
    {
        $missing = [];
        foreach ($required as $param) {
            if (empty($this->getParam($param, null, $method))) {
                $missing[] = $param;
            }
        }
        return $missing;
    }
}

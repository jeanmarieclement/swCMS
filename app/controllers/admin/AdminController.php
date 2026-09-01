<?php

namespace App\Controllers\Admin;

use App\Helpers\RedirectHelper;
use App\Helpers\LogHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Core\Controller;
use App\Helpers\SessionHelper;
use App\Helpers\TinyMCEHelper;
use App\middlewares\AuthMiddleware;
use App\Services\AdminMenuService;
use App\Services\DashboardService;

/**
 * Admin Controller
 * Handles requests to the admin area
 */

class AdminController extends Controller
{
    protected $adminMenuService;
    protected $dashboardService;



    /**
     * AdminController constructor.
     * Requires authentication and sets up admin access.
     *
     * @param array $params Optional parameters for the controller
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        // Require authentication for all admin pages
        AuthMiddleware::requireAuthor();

        // Istanzia AdminMenuService (adatta la connessione PDO secondo la tua architettura)

        $this->adminMenuService = new AdminMenuService($this->roleService);
        $this->dashboardService = new DashboardService();
        // For specific admin sections that require higher privileges,
        // you can use these in the specific action methods:
        // AuthMiddleware::requireEditor(); // For editor or admin only
        // AuthMiddleware::requireAdmin(); // For admin only
    }

    /**
     * Checks if the current user role can access the given template.
     * Redirects if access is denied.
     *
     * @param string $template Template name
     * @return void
     */
    protected function checkTemplateAccess(string $template)
    {


        if (!$this->roleService->canAccessTemplate(SessionHelper::getValue('user_role'), $template)) {
            LogHelper::warning('Template access denied', [
                'template' => $template,
                'role' => SessionHelper::getValue('user_role'),
                'request_uri' => RequestHelper::server('REQUEST_URI', 'unknown')
            ]);
            $this->setFlashMessage('error', 'You don\'t have permission to access this section');

            // Avoid redirect loop - if we're already on the dashboard, show the error page
            if ($template === 'admin/dashboard') {
                http_response_code(403);
                // parent::render skips checkTemplateAccess (would deny recursively)
                parent::render('errors/unauthorized', [
                    'title' => 'Accesso Negato',
                    'error_code' => 403
                ]);
                exit;
            } else {
                RedirectHelper::redirect('/admin/dashboard');
            }
        }
    }

    /**
     * Renders a template after checking access and passing flash messages.
     *
     * @param string $template Template name
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function render($template, $data = [])
    {
        $this->checkTemplateAccess($template);
        // Passa il menu admin dinamico
        $userRole = SessionHelper::getValue('user_role');
        $data['admin_menu'] = $this->adminMenuService->getMenu($userRole);
        parent::render($template, $data);
    }

    /**
     * Displays the admin dashboard.
     *
     * @return void
     */
    public function indexAction()
    {
        // Get dashboard data from the service
        $stats = $this->dashboardService->getStats();
        $recentContent = $this->dashboardService->getRecentContent(5);
        $recentActivity = $this->dashboardService->getRecentActivity(5);
        $systemInfo = $this->dashboardService->getSystemInfo();

        // Set admin and site URLs for template
        $adminUrl = '/admin';
        $siteUrl = $this->settings['SITE_URL'] ?? '';

        $this->render('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'page_name' => 'dashboard',
            'stats' => $stats,
            'recent_content' => $recentContent,
            'recent_activity' => $recentActivity,
            'system_info' => $systemInfo,
            'admin_url' => $adminUrl,
            'site_url' => $siteUrl
        ]);
    }

    /**
     * Alias for indexAction, displays the admin dashboard for /admin/dashboard route.
     *
     * @return void
     */
    public function dashboardAction()
    {
        $this->indexAction();
    }

    /**
     * Clears the Smarty compiled templates cache.
     *
     * @return void
     */
    public function clearCacheAction()
    {
        try {
            // Both halves matter: compiled templates are the code, the page cache
            // holds rendered pages. Clearing only the former left stale pages
            // being served after an edit, and left expired cache files behind.
            $compiledDeleted = (int) $this->view->clearCompiled();
            $cachedDeleted = (int) $this->view->clearCache();

            LogHelper::info('Cache cleared', [
                'compiled_deleted' => $compiledDeleted,
                'cached_pages_deleted' => $cachedDeleted,
                'user_id' => SessionHelper::getValue('user_id'),
                'user_role' => SessionHelper::getValue('user_role')
            ]);

            $this->setFlashMessage(
                'success',
                "Cache cleared successfully! {$compiledDeleted} compiled templates and "
                . "{$cachedDeleted} cached pages deleted."
            );
        } catch (Exception $e) {
            LogHelper::error('Failed to clear cache', [
                'error' => $e->getMessage(),
                'user_id' => SessionHelper::getValue('user_id')
            ]);

            $this->setFlashMessage('error', 'Failed to clear cache: ' . $e->getMessage());
        }

        // Redirect back to dashboard
        RedirectHelper::redirect('/admin/dashboard');
    }
}

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

/**
 * Admin Controller
 * Handles requests to the admin area
 */
use App\Services\AdminMenuService;
use App\Services\DashboardService;

class AdminController extends Controller {
    protected $adminMenuService;
    protected $dashboardService;



    /**
     * AdminController constructor.
     * Requires authentication and sets up admin access.
     *
     * @param array $params Optional parameters for the controller
     */
    public function __construct($params = []) {
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
            
            // Avoid redirect loop - if we're already on the dashboard, redirect to a different page
            if ($template === 'admin/dashboard') {
                // Accesso non autorizzato: mostra pagina 403 personalizzata
                $this->render('errors/403', [
                    'title' => 'Accesso Negato',
                    'error_code' => 403
                ], 'admin');
                return;
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
    public function indexAction() {
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
    public function dashboardAction() {
        $this->indexAction();
    }
    
    /**
     * Clears the Smarty compiled templates cache.
     *
     * @return void
     */
    public function clearCacheAction() {
        try {
            // Get the compiled directory path
            $compiledDir = __DIR__ . '/../../views/compiled/';
            
            if (is_dir($compiledDir)) {
                // Clear all compiled template files
                $files = glob($compiledDir . '*');
                $deletedCount = 0;
                
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        $deletedCount++;
                    }
                }
                
                LogHelper::info('Cache cleared', [
                    'files_deleted' => $deletedCount,
                    'user_id' => SessionHelper::getValue('user_id'),
                    'user_role' => SessionHelper::getValue('user_role')
                ]);
                
                $this->setFlashMessage('success', "Cache cleared successfully! {$deletedCount} compiled files deleted.");
            } else {
                $this->setFlashMessage('warning', 'Compiled cache directory not found.');
            }
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

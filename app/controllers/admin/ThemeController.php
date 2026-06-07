<?php

namespace App\Controllers\Admin;

use App\Helpers\RedirectHelper;
use App\Helpers\LogHelper;
use App\Helpers\SystemSettingsHelper;
use App\Services\ThemeService;

/**
 * Theme Controller
 * Handles admin theme management functionality
 */
class ThemeController extends AdminController {
    protected $themeService;

    /**
     * ThemeController constructor.
     */
    public function __construct($params = []) {
        parent::__construct($params);
        $this->themeService = new ThemeService();
    }

    /**
     * Display themes list
     */
    public function indexAction() {
        $themes = $this->themeService->getAvailableThemes();
        $activeTheme = SystemSettingsHelper::get('THEME_ACTIVE');
        
        $this->render('admin/themes/index', [
            'title' => 'Themes',
            'page_name' => 'themes',
            'themes' => $themes,
            'active_theme' => $activeTheme
        ]);
    }

    /**
     * Activate a theme
     */
    public function activateAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setFlashMessage('error', 'Invalid request method');
            RedirectHelper::redirect('/admin/themes');
            return;
        }

        $themeName = $_POST['theme'] ?? '';
        
        if (empty($themeName)) {
            $this->setFlashMessage('error', 'No theme specified');
            RedirectHelper::redirect('/admin/themes');
            return;
        }

        try {
            $result = $this->themeService->activateTheme($themeName);
            
            if ($result) {
                LogHelper::info('Theme activated', ['theme' => $themeName, 'user_id' => $_SESSION['user_id'] ?? null]);
                $this->setFlashMessage('success', "Theme '$themeName' has been activated successfully");
            } else {
                $this->setFlashMessage('error', "Failed to activate theme '$themeName'");
            }
        } catch (\Exception $e) {
            LogHelper::error('Theme activation failed', ['theme' => $themeName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error activating theme: ' . $e->getMessage());
        }

        RedirectHelper::redirect('/admin/themes');
    }

    /**
     * Show theme details
     */
    public function detailsAction() {
        $themeName = $_GET['theme'] ?? '';
        
        if (empty($themeName)) {
            $this->setFlashMessage('error', 'No theme specified');
            RedirectHelper::redirect('/admin/themes');
            return;
        }

        try {
            $theme = $this->themeService->getThemeDetails($themeName);
            
            if (!$theme) {
                $this->setFlashMessage('error', 'Theme not found');
                RedirectHelper::redirect('/admin/themes');
                return;
            }

            $this->render('admin/themes/details', [
                'title' => "Theme Details - {$theme['name']}",
                'page_name' => 'theme_details',
                'theme' => $theme,
                'active_theme' => SystemSettingsHelper::get('THEME_ACTIVE')
            ]);

        } catch (\Exception $e) {
            LogHelper::error('Error loading theme details', ['theme' => $themeName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error loading theme details: ' . $e->getMessage());
            RedirectHelper::redirect('/admin/themes');
        }
    }

    /**
     * Install a new theme (placeholder for future implementation)
     */
    public function installAction() {
        $this->setFlashMessage('info', 'Theme installation feature will be available in a future version');
        RedirectHelper::redirect('/admin/themes');
    }

    /**
     * Delete a theme (placeholder for future implementation)
     */
    public function deleteAction() {
        $this->setFlashMessage('info', 'Theme deletion feature will be available in a future version');
        RedirectHelper::redirect('/admin/themes');
    }
}
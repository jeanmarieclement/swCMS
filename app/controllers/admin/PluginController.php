<?php

namespace App\Controllers\Admin;

use App\Helpers\RedirectHelper;
use App\Helpers\LogHelper;
use App\Helpers\SystemSettingsHelper;
use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\SessionHelper;
use App\Services\PluginService;
use App\Services\PluginGeneratorService;

/**
 * Plugin Controller
 * Handles admin plugin management functionality
 */
class PluginController extends AdminController
{
    protected $pluginService;
    protected $pluginGeneratorService;

    /**
     * PluginController constructor.
     */
    public function __construct($params = [])
    {
        parent::__construct($params);
        $this->pluginService = new PluginService();
        $this->pluginGeneratorService = new PluginGeneratorService();
    }

    /**
     * Display plugins list
     */
    public function indexAction()
    {
        $plugins = $this->pluginService->getAvailablePlugins();
        $activePlugins = $this->pluginService->getActivePlugins();

        $this->render('admin/plugins/index', [
            'title' => 'Plugins',
            'page_name' => 'plugins',
            'plugins' => $plugins,
            'active_plugins' => $activePlugins
        ]);
    }

    /**
     * Activate a plugin
     */
    public function activateAction()
    {
        if (!RequestHelper::isPost()) {
            $this->setFlashMessage('error', 'Invalid request method');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        $this->requireCsrf('/admin/plugins', 'plugin activation');

        $pluginName = RequestHelper::post('plugin', '');

        if (empty($pluginName)) {
            $this->setFlashMessage('error', 'No plugin specified');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        try {
            $result = $this->pluginService->activatePlugin($pluginName);

            if ($result) {
                LogHelper::info('Plugin activated', ['plugin' => $pluginName, 'user_id' => SessionHelper::getValue('user_id')]);
                $this->setFlashMessage('success', "Plugin '$pluginName' has been activated successfully");
            } else {
                $this->setFlashMessage('error', "Failed to activate plugin '$pluginName'");
            }
        } catch (\Exception $e) {
            LogHelper::error('Plugin activation failed', ['plugin' => $pluginName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error activating plugin: ' . $e->getMessage());
        }

        RedirectHelper::redirect('/admin/plugins');
    }

    /**
     * Deactivate a plugin
     */
    public function deactivateAction()
    {
        if (!RequestHelper::isPost()) {
            $this->setFlashMessage('error', 'Invalid request method');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        $this->requireCsrf('/admin/plugins', 'plugin deactivation');

        $pluginName = RequestHelper::post('plugin', '');

        if (empty($pluginName)) {
            $this->setFlashMessage('error', 'No plugin specified');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        try {
            // Check if plugin can be safely deactivated
            $canDeactivate = $this->pluginService->canDeactivatePlugin($pluginName);

            if ($canDeactivate !== true) {
                $dependents = implode(', ', $canDeactivate['dependents']);
                $this->setFlashMessage('error', "Cannot deactivate '$pluginName' because it's required by: $dependents");
                RedirectHelper::redirect('/admin/plugins');
                return;
            }

            $result = $this->pluginService->deactivatePlugin($pluginName);

            if ($result) {
                LogHelper::info('Plugin deactivated', ['plugin' => $pluginName, 'user_id' => SessionHelper::getValue('user_id')]);
                $this->setFlashMessage('success', "Plugin '$pluginName' has been deactivated successfully");
            } else {
                $this->setFlashMessage('error', "Failed to deactivate plugin '$pluginName'");
            }
        } catch (\Exception $e) {
            LogHelper::error('Plugin deactivation failed', ['plugin' => $pluginName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error deactivating plugin: ' . $e->getMessage());
        }

        RedirectHelper::redirect('/admin/plugins');
    }

    /**
     * Show plugin details
     */
    public function detailsAction()
    {
        $pluginName = RequestHelper::get('plugin', '');

        if (empty($pluginName)) {
            $this->setFlashMessage('error', 'No plugin specified');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        try {
            $plugin = $this->pluginService->getPluginDetails($pluginName);

            if (!$plugin) {
                $this->setFlashMessage('error', 'Plugin not found');
                RedirectHelper::redirect('/admin/plugins');
                return;
            }

            $isActive = $this->pluginService->isPluginActive($pluginName);
            $compatibility = $this->pluginService->validatePluginCompatibility($pluginName);
            $dependents = $this->pluginService->getPluginDependents($pluginName);

            $this->render('admin/plugins/details', [
                'title' => "Plugin Details - {$plugin['name']}",
                'page_name' => 'plugin_details',
                'plugin' => $plugin,
                'is_active' => $isActive,
                'compatibility' => $compatibility,
                'dependents' => $dependents
            ]);
        } catch (\Exception $e) {
            LogHelper::error('Error loading plugin details', ['plugin' => $pluginName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error loading plugin details: ' . $e->getMessage());
            RedirectHelper::redirect('/admin/plugins');
        }
    }

    /**
     * Show plugin generator form
     */
    public function generateAction()
    {
        if (RequestHelper::isPost()) {
            $this->requireCsrf('/admin/plugins/generate', 'plugin generation');

            $config = [
                'name' => RequestHelper::post('name', ''),
                'display_name' => RequestHelper::post('display_name', ''),
                'description' => RequestHelper::post('description', ''),
                'author' => RequestHelper::post('author', ''),
                'author_uri' => RequestHelper::post('author_uri', ''),
                'plugin_uri' => RequestHelper::post('plugin_uri', ''),
                'version' => RequestHelper::post('version', '1.0.0'),
                'requires' => RequestHelper::post('requires', '1.0.0'),
                'tested_up_to' => RequestHelper::post('tested_up_to', '1.5.0'),
                'requires_php' => RequestHelper::post('requires_php', '7.4.0'),
                'priority' => RequestHelper::post('priority', '10'),
                'depends' => array_filter(explode(',', RequestHelper::post('depends', ''))),
                'conflicts' => array_filter(explode(',', RequestHelper::post('conflicts', ''))),
                'include_hooks' => RequestHelper::post('include_hooks', null) !== null,
                'include_settings' => RequestHelper::post('include_settings', null) !== null,
                'include_assets' => RequestHelper::post('include_assets', null) !== null,
                'include_readme' => RequestHelper::post('include_readme', null) !== null,
                'include_tests' => RequestHelper::post('include_tests', null) !== null
            ];

            try {
                $result = $this->pluginGeneratorService->generatePlugin($config);

                if ($result['success']) {
                    LogHelper::info('Plugin generated successfully', [
                        'plugin' => $result['plugin_name'],
                        'user_id' => SessionHelper::getValue('user_id')
                    ]);

                    $this->setFlashMessage('success', "Plugin '{$result['plugin_name']}' has been generated successfully!");
                    RedirectHelper::redirect("/admin/plugins/details?plugin={$result['plugin_name']}");
                    return;
                } else {
                    $errors = implode('<br>', $result['errors']);
                    $this->setFlashMessage('error', "Plugin generation failed:<br>$errors");
                }
            } catch (\Exception $e) {
                LogHelper::error('Plugin generation error', [
                    'error' => $e->getMessage(),
                    'config' => $config
                ]);
                $this->setFlashMessage('error', 'Plugin generation failed: ' . $e->getMessage());
            }
        }

        $this->render('admin/plugins/generate', [
            'title' => 'Generate New Plugin',
            'page_name' => 'plugin_generate'
        ]);
    }

    /**
     * Install a new plugin (placeholder for future implementation)
     */
    public function installAction()
    {
        $this->setFlashMessage('info', 'Plugin installation feature will be available in a future version');
        RedirectHelper::redirect('/admin/plugins');
    }

    /**
     * Delete a plugin (placeholder for future implementation)
     */
    public function deleteAction()
    {
        $this->setFlashMessage('info', 'Plugin deletion feature will be available in a future version');
        RedirectHelper::redirect('/admin/plugins');
    }

    /**
     * Configure plugin settings
     */
    public function configureAction()
    {
        $pluginName = RequestHelper::get('plugin', '');

        if (empty($pluginName)) {
            $this->setFlashMessage('error', 'No plugin specified');
            RedirectHelper::redirect('/admin/plugins');
            return;
        }

        try {
            $plugin = $this->pluginService->getPluginDetails($pluginName);

            if (!$plugin) {
                $this->setFlashMessage('error', 'Plugin not found');
                RedirectHelper::redirect('/admin/plugins');
                return;
            }

            // Handle form submission
            if (RequestHelper::isPost()) {
                $this->requireCsrf("/admin/plugins/configure?plugin=$pluginName", 'plugin configuration');

                $settings = RequestHelper::post('settings', [], 'raw');
                $result = $this->pluginService->savePluginSettings($pluginName, $settings);

                if ($result) {
                    LogHelper::info('Plugin settings updated', ['plugin' => $pluginName, 'user_id' => SessionHelper::getValue('user_id')]);
                    $this->setFlashMessage('success', "Settings for '$pluginName' have been updated successfully");
                } else {
                    $this->setFlashMessage('error', "Failed to update settings for '$pluginName'");
                }

                RedirectHelper::redirect("/admin/plugins/configure?plugin=$pluginName");
                return;
            }

            $settings = $this->pluginService->getPluginSettings($pluginName);

            $this->render('admin/plugins/configure', [
                'title' => "Configure Plugin - {$plugin['name']}",
                'page_name' => 'plugin_configure',
                'plugin' => $plugin,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            LogHelper::error('Error configuring plugin', ['plugin' => $pluginName, 'error' => $e->getMessage()]);
            $this->setFlashMessage('error', 'Error configuring plugin: ' . $e->getMessage());
            RedirectHelper::redirect('/admin/plugins');
        }
    }
}

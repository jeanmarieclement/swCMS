<?php
/**
 * Plugin Name: Example Plugin
 * Description: This is an example plugin that demonstrates the swCMS plugin system with hooks, dependencies, and advanced features
 * Version: 1.2.0
 * Author: swCMS Development Team
 * Author URI: https://swcms.example.com
 * Plugin URI: https://swcms.example.com/plugins/example-plugin
 * Requires: 1.0.0
 * Tested up to: 1.5.0
 * Requires PHP: 7.4.0
 * API Version: 1.0
 * Priority: 10
 * Network: false
 * Depends: 
 * Conflicts: 
 * 
 * Menu Config: {
 *   "items": [
 *     {
 *       "block_key": "plugins",
 *       "label": "Example Plugin",
 *       "url": "/admin/example-plugin",
 *       "icon": "fas fa-puzzle-piece",
 *       "permission_key": "manage_plugins",
 *       "position": 50
 *     }
 *   ]
 * }
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    exit('Direct access denied');
}

/**
 * Plugin activation hook
 * Called when the plugin is activated
 */
function example_plugin_activate() {
    // Plugin activation logic
    error_log('Example Plugin activated');
    
    // You could create database tables, set default options, etc.
    // Example: Create plugin options
    \App\Helpers\SystemSettingsHelper::set('PLUGIN_EXAMPLE_PLUGIN_WELCOME_MESSAGE', 'Hello from Example Plugin!');
}

/**
 * Plugin deactivation hook
 * Called when the plugin is deactivated
 */
function example_plugin_deactivate() {
    // Plugin deactivation logic
    error_log('Example Plugin deactivated');
    
    // Clean up temporary data, but leave user data intact
    // Don't delete user settings/data on deactivation
}

/**
 * Main plugin functionality
 * This function demonstrates how to hook into the CMS
 */
function example_plugin_init() {
    // Register hooks with the CMS hook system
    if (class_exists('\\App\\Core\\HookSystem')) {
        $hookSystem = \App\Core\HookSystem::getInstance();
        
        // Add a hook to modify the admin dashboard
        $hookSystem->addAction('admin_dashboard_widgets', 'example_plugin_dashboard_widget');
        
        // Add a hook to modify page content
        $hookSystem->addFilter('page_content', 'example_plugin_modify_content');
        
        // Add CSS to admin pages
        $hookSystem->addAction('admin_head', 'example_plugin_admin_css');
    }
}

/**
 * Add a widget to the admin dashboard
 */
function example_plugin_dashboard_widget() {
    echo '<div class="col-md-6 mb-4">';
    echo '<div class="card border-info">';
    echo '<div class="card-header bg-info text-white">';
    echo '<h6 class="mb-0"><i class="fas fa-puzzle-piece me-2"></i>Example Plugin Widget</h6>';
    echo '</div>';
    echo '<div class="card-body">';
    echo '<p>This widget was added by the Example Plugin!</p>';
    echo '<p class="mb-0">Plugin version: 1.0.0</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

/**
 * Modify page content
 */
function example_plugin_modify_content($content) {
    // Add a signature to all page content
    $signature = '<p><em>-- Enhanced by Example Plugin</em></p>';
    return $content . $signature;
}

/**
 * Add CSS to admin pages
 */
function example_plugin_admin_css() {
    echo '<style>';
    echo '.example-plugin-highlight { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }';
    echo '</style>';
}

/**
 * Get plugin settings
 */
function example_plugin_get_settings() {
    $settings = \App\Helpers\SystemSettingsHelper::get('PLUGIN_EXAMPLE_PLUGIN_SETTINGS');
    return $settings ? json_decode($settings, true) : [
        'enabled' => true,
        'welcome_message' => 'Hello from Example Plugin!',
        'show_signature' => true,
        'debug_mode' => false
    ];
}

// Initialize the plugin
example_plugin_init();
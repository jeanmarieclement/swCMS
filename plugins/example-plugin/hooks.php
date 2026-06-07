<?php
/**
 * Plugin Hooks Definition
 * This file defines all the hooks that this plugin uses
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    exit('Direct access denied');
}

/**
 * Register all plugin hooks
 * This function is called when the plugin is loaded
 */
function example_plugin_register_hooks() {
    if (!class_exists('\\App\\Core\\HookSystem')) {
        return;
    }
    
    $hookSystem = \App\Core\HookSystem::getInstance();
    
    // Action hooks (execute code at specific points)
    $hookSystem->addAction('init', 'example_plugin_on_init', 10);
    $hookSystem->addAction('admin_menu', 'example_plugin_admin_menu', 10);
    $hookSystem->addAction('cms_head', 'example_plugin_frontend_head', 10);
    $hookSystem->addAction('admin_head', 'example_plugin_admin_head', 10);
    
    // Filter hooks (modify data)
    $hookSystem->addFilter('the_content', 'example_plugin_filter_content', 10, 1);
    $hookSystem->addFilter('admin_title', 'example_plugin_filter_admin_title', 10, 1);
    $hookSystem->addFilter('page_template', 'example_plugin_custom_template', 10, 1);
}

/**
 * Initialize plugin functionality
 */
function example_plugin_on_init() {
    // Plugin initialization code
    $settings = example_plugin_get_settings();
    
    if ($settings['debug_mode']) {
        error_log('Example Plugin: Initialization complete');
    }
}

/**
 * Add custom admin menu items
 */
function example_plugin_admin_menu() {
    // This would add custom menu items to the admin
    // For now, just log that it was called
    error_log('Example Plugin: Admin menu hook called');
}

/**
 * Add content to frontend head
 */
function example_plugin_frontend_head() {
    $settings = example_plugin_get_settings();
    
    if (!$settings['enabled']) {
        return;
    }
    
    echo '<meta name="generator" content="swCMS with Example Plugin">' . "\n";
    echo '<style>';
    echo '.example-plugin-enhanced { border-left: 3px solid #007cba; padding-left: 15px; }';
    echo '</style>' . "\n";
}

/**
 * Add content to admin head
 */
function example_plugin_admin_head() {
    echo '<style>';
    echo '.example-plugin-admin { background-color: #f0f8ff; }';
    echo '.example-plugin-notice { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }';
    echo '</style>' . "\n";
}

/**
 * Filter page/post content
 */
function example_plugin_filter_content($content) {
    $settings = example_plugin_get_settings();
    
    if (!$settings['enabled'] || !$settings['show_signature']) {
        return $content;
    }
    
    // Add a wrapper div with plugin class
    $enhanced_content = '<div class="example-plugin-enhanced">';
    $enhanced_content .= $content;
    $enhanced_content .= '</div>';
    
    // Add plugin signature if enabled
    if ($settings['show_signature']) {
        $enhanced_content .= '<div class="example-plugin-signature">';
        $enhanced_content .= '<small><em>' . $settings['welcome_message'] . '</em></small>';
        $enhanced_content .= '</div>';
    }
    
    return $enhanced_content;
}

/**
 * Filter admin page titles
 */
function example_plugin_filter_admin_title($title) {
    // Add plugin indicator to admin titles
    return $title . ' | Example Plugin Active';
}

/**
 * Custom template override
 */
function example_plugin_custom_template($template) {
    // This could override page templates
    // For demonstration, we'll just return the original template
    return $template;
}

// Register all hooks when this file is loaded
example_plugin_register_hooks();
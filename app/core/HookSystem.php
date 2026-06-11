<?php

namespace App\Core;

/**
 * Hook System for swCMS
 * Provides extensible hooks and filters system for plugins
 */
class HookSystem
{
    private static $instance = null;
    private $actions = [];
    private $filters = [];
    private $current_filter = '';

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Private constructor for singleton
    }

    /**
     * Add an action hook
     * @param string $tag Action name
     * @param callable $callback Function to call
     * @param int $priority Priority (lower numbers = earlier execution)
     * @param int $accepted_args Number of arguments the callback accepts
     */
    public function addAction($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return $this->addHook('actions', $tag, $callback, $priority, $accepted_args);
    }

    /**
     * Add a filter hook
     * @param string $tag Filter name
     * @param callable $callback Function to call
     * @param int $priority Priority (lower numbers = earlier execution)
     * @param int $accepted_args Number of arguments the callback accepts
     */
    public function addFilter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        return $this->addHook('filters', $tag, $callback, $priority, $accepted_args);
    }

    /**
     * Add a hook (internal method)
     */
    private function addHook($type, $tag, $callback, $priority, $accepted_args)
    {
        if (!isset($this->{$type}[$tag])) {
            $this->{$type}[$tag] = [];
        }

        if (!isset($this->{$type}[$tag][$priority])) {
            $this->{$type}[$tag][$priority] = [];
        }

        $this->{$type}[$tag][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $accepted_args
        ];

        // Sort by priority
        ksort($this->{$type}[$tag]);

        return true;
    }

    /**
     * Execute an action
     * @param string $tag Action name
     * @param mixed ...$args Arguments to pass to callbacks
     */
    public function doAction($tag, ...$args)
    {
        if (!isset($this->actions[$tag])) {
            return;
        }

        $this->current_filter = $tag;

        foreach ($this->actions[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                if (is_callable($callback_data['callback'])) {
                    try {
                        $callback_args = array_slice($args, 0, $callback_data['accepted_args']);
                        call_user_func_array($callback_data['callback'], $callback_args);
                    } catch (\Exception $e) {
                        error_log("Hook System Error in action '$tag': " . $e->getMessage());
                    }
                }
            }
        }

        $this->current_filter = '';
    }

    /**
     * Apply filters
     * @param string $tag Filter name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed Filtered value
     */
    public function applyFilters($tag, $value, ...$args)
    {
        if (!isset($this->filters[$tag])) {
            return $value;
        }

        $this->current_filter = $tag;

        foreach ($this->filters[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                if (is_callable($callback_data['callback'])) {
                    try {
                        $callback_args = array_merge([$value], array_slice($args, 0, $callback_data['accepted_args'] - 1));
                        $value = call_user_func_array($callback_data['callback'], $callback_args);
                    } catch (\Exception $e) {
                        error_log("Hook System Error in filter '$tag': " . $e->getMessage());
                    }
                }
            }
        }

        $this->current_filter = '';

        return $value;
    }

    /**
     * Remove an action
     * @param string $tag Action name
     * @param callable $callback Function to remove
     * @param int $priority Priority level
     * @return bool Success status
     */
    public function removeAction($tag, $callback, $priority = 10)
    {
        return $this->removeHook('actions', $tag, $callback, $priority);
    }

    /**
     * Remove a filter
     * @param string $tag Filter name
     * @param callable $callback Function to remove
     * @param int $priority Priority level
     * @return bool Success status
     */
    public function removeFilter($tag, $callback, $priority = 10)
    {
        return $this->removeHook('filters', $tag, $callback, $priority);
    }

    /**
     * Remove a hook (internal method)
     */
    private function removeHook($type, $tag, $callback, $priority)
    {
        if (!isset($this->{$type}[$tag][$priority])) {
            return false;
        }

        foreach ($this->{$type}[$tag][$priority] as $key => $callback_data) {
            if ($callback_data['callback'] === $callback) {
                unset($this->{$type}[$tag][$priority][$key]);

                // Clean up empty arrays
                if (empty($this->{$type}[$tag][$priority])) {
                    unset($this->{$type}[$tag][$priority]);

                    if (empty($this->{$type}[$tag])) {
                        unset($this->{$type}[$tag]);
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Check if a hook has callbacks
     * @param string $tag Hook name
     * @param string $type Type: 'action' or 'filter'
     * @return bool True if has callbacks
     */
    public function hasHook($tag, $type = 'action')
    {
        $hooks = $type === 'filter' ? $this->filters : $this->actions;
        return !empty($hooks[$tag]);
    }

    /**
     * Get current filter being processed
     * @return string Current filter name
     */
    public function currentFilter()
    {
        return $this->current_filter;
    }

    /**
     * Get all registered hooks for debugging
     * @param string $type Type: 'actions' or 'filters'
     * @return array All hooks
     */
    public function getHooks($type = 'actions')
    {
        return $this->{$type} ?? [];
    }

    /**
     * Remove all hooks for a specific tag
     * @param string $tag Hook name
     * @param string $type Type: 'actions' or 'filters'
     * @return bool Success status
     */
    public function removeAllHooks($tag, $type = 'actions')
    {
        if (isset($this->{$type}[$tag])) {
            unset($this->{$type}[$tag]);
            return true;
        }
        return false;
    }

    /**
     * Get hook priority for callback
     * @param string $tag Hook name
     * @param callable $callback Callback function
     * @param string $type Type: 'actions' or 'filters'
     * @return int|false Priority or false if not found
     */
    public function getHookPriority($tag, $callback, $type = 'actions')
    {
        if (!isset($this->{$type}[$tag])) {
            return false;
        }

        foreach ($this->{$type}[$tag] as $priority => $callbacks) {
            foreach ($callbacks as $callback_data) {
                if ($callback_data['callback'] === $callback) {
                    return $priority;
                }
            }
        }

        return false;
    }

    /**
     * Initialize common CMS hooks
     * This method sets up standard hooks that plugins can use
     */
    public function initializeCoreHooks()
    {
        // These are placeholders for common hooks that the CMS can trigger
        $core_hooks = [
            // Initialization hooks
            'init',
            'plugins_loaded',
            'after_setup_theme',
            'setup_complete',

            // Frontend hooks
            'cms_head',
            'cms_footer',
            'the_content',
            'the_title',
            'the_excerpt',
            'before_content',
            'after_content',
            'sidebar_widgets',
            'navigation_menu',

            // Admin hooks
            'admin_head',
            'admin_footer',
            'admin_menu',
            'admin_init',
            'admin_dashboard_widgets',
            'admin_notices',
            'admin_enqueue_scripts',
            'admin_bar_menu',

            // Database hooks
            'save_post',
            'before_save_post',
            'after_save_post',
            'delete_post',
            'before_delete_post',
            'after_delete_post',
            'save_user',
            'before_save_user',
            'after_save_user',
            'delete_user',
            'before_delete_user',
            'after_delete_user',
            'save_category',
            'delete_category',
            'save_tag',
            'delete_tag',

            // Authentication hooks
            'user_login',
            'user_logout',
            'user_register',
            'login_failed',
            'password_reset',

            // Template hooks
            'template_redirect',
            'page_template',
            'single_template',
            'category_template',
            'tag_template',
            'search_template',
            'archive_template',
            '404_template',

            // Media hooks
            'upload_file',
            'delete_file',
            'image_resize',

            // Cache hooks
            'cache_clear',
            'cache_write',
            'cache_read',

            // Security hooks
            'login_attempt',
            'security_check',
            'permission_check',

            // System hooks
            'system_error',
            'maintenance_mode',
            'cron_job',

            // Theme hooks
            'theme_activated',
            'theme_deactivated',
            'theme_customizer',

            // Comment hooks
            'comment_post',
            'comment_approved',
            'comment_spam',
            'comment_deleted'
        ];

        // Log that core hooks are available
        foreach ($core_hooks as $hook) {
            if (!isset($this->actions[$hook])) {
                $this->actions[$hook] = [];
            }
            if (!isset($this->filters[$hook])) {
                $this->filters[$hook] = [];
            }
        }
    }

    /**
     * Get list of all available core hooks
     * @return array List of core hooks
     */
    public function getCoreHooks(): array
    {
        return [
            'initialization' => ['init', 'plugins_loaded', 'after_setup_theme', 'setup_complete'],
            'frontend' => ['cms_head', 'cms_footer', 'the_content', 'the_title', 'the_excerpt', 'before_content', 'after_content', 'sidebar_widgets', 'navigation_menu'],
            'admin' => ['admin_head', 'admin_footer', 'admin_menu', 'admin_init', 'admin_dashboard_widgets', 'admin_notices', 'admin_enqueue_scripts', 'admin_bar_menu'],
            'database' => ['save_post', 'before_save_post', 'after_save_post', 'delete_post', 'before_delete_post', 'after_delete_post', 'save_user', 'before_save_user', 'after_save_user', 'delete_user', 'before_delete_user', 'after_delete_user', 'save_category', 'delete_category', 'save_tag', 'delete_tag'],
            'authentication' => ['user_login', 'user_logout', 'user_register', 'login_failed', 'password_reset'],
            'templates' => ['template_redirect', 'page_template', 'single_template', 'category_template', 'tag_template', 'search_template', 'archive_template', '404_template'],
            'media' => ['upload_file', 'delete_file', 'image_resize'],
            'cache' => ['cache_clear', 'cache_write', 'cache_read'],
            'security' => ['login_attempt', 'security_check', 'permission_check'],
            'system' => ['system_error', 'maintenance_mode', 'cron_job'],
            'theme' => ['theme_activated', 'theme_deactivated', 'theme_customizer'],
            'comments' => ['comment_post', 'comment_approved', 'comment_spam', 'comment_deleted']
        ];
    }

    /**
     * Check if hook is a core hook
     * @param string $hook Hook name
     * @return bool True if it's a core hook
     */
    public function isCoreHook(string $hook): bool
    {
        $coreHooks = $this->getCoreHooks();
        foreach ($coreHooks as $category => $hooks) {
            if (in_array($hook, $hooks)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get hooks by category
     * @param string $category Category name
     * @return array Hooks in category
     */
    public function getHooksByCategory(string $category): array
    {
        $coreHooks = $this->getCoreHooks();
        return $coreHooks[$category] ?? [];
    }

    /**
     * Add multiple hooks at once
     * @param array $hooks Array of hook definitions
     * @return bool Success status
     */
    public function addHooks(array $hooks): bool
    {
        foreach ($hooks as $hook) {
            $type = $hook['type'] ?? 'action';
            $tag = $hook['tag'] ?? '';
            $callback = $hook['callback'] ?? null;
            $priority = $hook['priority'] ?? 10;
            $accepted_args = $hook['accepted_args'] ?? 1;

            if (empty($tag) || !is_callable($callback)) {
                continue;
            }

            if ($type === 'filter') {
                $this->addFilter($tag, $callback, $priority, $accepted_args);
            } else {
                $this->addAction($tag, $callback, $priority, $accepted_args);
            }
        }
        return true;
    }

    /**
     * Get hook statistics for debugging
     * @return array Hook statistics
     */
    public function getHookStats(): array
    {
        $stats = [
            'total_actions' => count($this->actions),
            'total_filters' => count($this->filters),
            'action_callbacks' => 0,
            'filter_callbacks' => 0,
            'core_hooks_used' => 0,
            'custom_hooks_used' => 0
        ];

        // Count action callbacks
        foreach ($this->actions as $tag => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                $stats['action_callbacks'] += count($callbacks);
            }
            if ($this->isCoreHook($tag)) {
                $stats['core_hooks_used']++;
            } else {
                $stats['custom_hooks_used']++;
            }
        }

        // Count filter callbacks
        foreach ($this->filters as $tag => $priorities) {
            foreach ($priorities as $priority => $callbacks) {
                $stats['filter_callbacks'] += count($callbacks);
            }
            if (!isset($this->actions[$tag])) {
                if ($this->isCoreHook($tag)) {
                    $stats['core_hooks_used']++;
                } else {
                    $stats['custom_hooks_used']++;
                }
            }
        }

        return $stats;
    }
}

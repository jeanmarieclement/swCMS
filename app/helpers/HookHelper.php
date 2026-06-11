<?php

namespace App\Helpers;

use App\Core\HookSystem;

/**
 * Hook Helper
 * Provides convenient functions for plugin and hook management
 */
class HookHelper
{
    /**
     * Add an action hook
     * @param string $tag Action name
     * @param callable $callback Function to call
     * @param int $priority Priority (lower numbers = earlier execution)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return bool
     */
    public static function addAction($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->addAction($tag, $callback, $priority, $accepted_args);
    }

    /**
     * Add a filter hook
     * @param string $tag Filter name
     * @param callable $callback Function to call
     * @param int $priority Priority (lower numbers = earlier execution)
     * @param int $accepted_args Number of arguments the callback accepts
     * @return bool
     */
    public static function addFilter($tag, $callback, $priority = 10, $accepted_args = 1)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->addFilter($tag, $callback, $priority, $accepted_args);
    }

    /**
     * Execute an action
     * @param string $tag Action name
     * @param mixed ...$args Arguments to pass to callbacks
     */
    public static function doAction($tag, ...$args)
    {
        $hookSystem = HookSystem::getInstance();
        $hookSystem->doAction($tag, ...$args);
    }

    /**
     * Apply filters
     * @param string $tag Filter name
     * @param mixed $value Value to filter
     * @param mixed ...$args Additional arguments
     * @return mixed Filtered value
     */
    public static function applyFilters($tag, $value, ...$args)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->applyFilters($tag, $value, ...$args);
    }

    /**
     * Remove an action
     * @param string $tag Action name
     * @param callable $callback Function to remove
     * @param int $priority Priority level
     * @return bool Success status
     */
    public static function removeAction($tag, $callback, $priority = 10)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->removeAction($tag, $callback, $priority);
    }

    /**
     * Remove a filter
     * @param string $tag Filter name
     * @param callable $callback Function to remove
     * @param int $priority Priority level
     * @return bool Success status
     */
    public static function removeFilter($tag, $callback, $priority = 10)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->removeFilter($tag, $callback, $priority);
    }

    /**
     * Check if a hook has callbacks
     * @param string $tag Hook name
     * @param string $type Type: 'action' or 'filter'
     * @return bool True if has callbacks
     */
    public static function hasHook($tag, $type = 'action')
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->hasHook($tag, $type);
    }

    /**
     * Get current filter being processed
     * @return string Current filter name
     */
    public static function currentFilter()
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->currentFilter();
    }

    /**
     * Get all registered hooks for debugging
     * @param string $type Type: 'actions' or 'filters'
     * @return array All hooks
     */
    public static function getHooks($type = 'actions')
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->getHooks($type);
    }

    /**
     * Get list of all available core hooks
     * @return array List of core hooks
     */
    public static function getCoreHooks()
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->getCoreHooks();
    }

    /**
     * Check if hook is a core hook
     * @param string $hook Hook name
     * @return bool True if it's a core hook
     */
    public static function isCoreHook($hook)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->isCoreHook($hook);
    }

    /**
     * Get hooks by category
     * @param string $category Category name
     * @return array Hooks in category
     */
    public static function getHooksByCategory($category)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->getHooksByCategory($category);
    }

    /**
     * Get hook statistics for debugging
     * @return array Hook statistics
     */
    public static function getHookStats()
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->getHookStats();
    }

    /**
     * Add multiple hooks at once
     * @param array $hooks Array of hook definitions
     * @return bool Success status
     */
    public static function addHooks(array $hooks)
    {
        $hookSystem = HookSystem::getInstance();
        return $hookSystem->addHooks($hooks);
    }

    /**
     * Render action hook area (useful for templates)
     * @param string $area Hook name
     * @param array $data Additional data to pass
     */
    public static function renderHookArea($area, $data = [])
    {
        ob_start();
        self::doAction($area, $data);
        $content = ob_get_clean();

        // Allow filtering of hook area content
        $content = self::applyFilters($area . '_content', $content, $data);

        echo $content;
    }

    /**
     * Apply content filters (helper for common content filtering)
     * @param string $content Content to filter
     * @param string $context Context (post_content, page_content, etc.)
     * @param array $data Additional data
     * @return string Filtered content
     */
    public static function applyContentFilters($content, $context = 'the_content', $data = [])
    {
        // Apply specific context filter
        $content = self::applyFilters($context, $content, $data);

        // Apply general content processing
        $content = self::applyFilters('content_processing', $content, $context, $data);

        return $content;
    }

    /**
     * Check if current request is admin area
     * @return bool
     */
    public static function isAdmin()
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') === 0;
    }

    /**
     * Check if current request is frontend
     * @return bool
     */
    public static function isFrontend()
    {
        return !self::isAdmin();
    }

    /**
     * Fire hooks based on current context
     * @param string $baseHook Base hook name
     * @param mixed ...$args Arguments
     */
    public static function fireContextualHooks($baseHook, ...$args)
    {
        // Fire general hook
        self::doAction($baseHook, ...$args);

        // Fire context-specific hook
        if (self::isAdmin()) {
            self::doAction('admin_' . $baseHook, ...$args);
        } else {
            self::doAction('frontend_' . $baseHook, ...$args);
        }
    }
}

<?php
/**
 * Smarty Plugin: Hook Filter
 * 
 * Usage in templates:
 * {hook_filter name="the_content" content=$post.content}
 * {hook_filter name="page_title" content=$page.title data=$page}
 */

use App\Helpers\HookHelper;

function smarty_function_hook_filter($params, $smarty) {
    $hook_name = $params['name'] ?? '';
    $content = $params['content'] ?? '';
    $data = $params['data'] ?? [];
    
    if (empty($hook_name)) {
        return $content;
    }
    
    // Apply the filter
    $filtered_content = HookHelper::applyFilters($hook_name, $content, $data);
    
    return $filtered_content;
}
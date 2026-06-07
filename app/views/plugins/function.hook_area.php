<?php
/**
 * Smarty Plugin: Hook Area
 * 
 * Usage in templates:
 * {hook_area name="cms_head"}
 * {hook_area name="sidebar_widgets" data=$sidebar_data}
 */

use App\Helpers\HookHelper;

function smarty_function_hook_area($params, $smarty) {
    $area_name = $params['name'] ?? '';
    $data = $params['data'] ?? [];
    
    if (empty($area_name)) {
        return '<!-- Hook area: no name specified -->';
    }
    
    // Start output buffering
    ob_start();
    
    // Render the hook area
    HookHelper::renderHookArea($area_name, $data);
    
    // Get the output
    $output = ob_get_clean();
    
    return $output;
}
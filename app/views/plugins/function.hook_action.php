<?php

/**
 * Smarty Plugin: Hook Action
 *
 * Usage in templates:
 * {hook_action name="cms_head"}
 * {hook_action name="admin_notices" data=$data}
 */

use App\Helpers\HookHelper;

function smarty_function_hook_action($params, $smarty)
{
    $hook_name = $params['name'] ?? '';
    $data = $params['data'] ?? [];

    if (empty($hook_name)) {
        return '<!-- Hook action: no name specified -->';
    }

    // Start output buffering
    ob_start();

    // Execute the hook
    HookHelper::doAction($hook_name, $data);

    // Get the output
    $output = ob_get_clean();

    // Allow filtering of the output
    $output = HookHelper::applyFilters($hook_name . '_output', $output, $data);

    return $output;
}

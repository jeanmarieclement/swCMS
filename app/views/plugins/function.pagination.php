<?php
/**
 * Smarty plugin
 * 
 * @package    Smarty
 * @subpackage PluginsFunction
 */

/**
 * Smarty {pagination} function plugin
 *
 * Type:     function
 * Name:     pagination
 * Purpose:  renders pagination controls
 *
 * @param array  $params   parameters
 * @param object $smarty   Smarty instance
 * @return string HTML output
 */
function smarty_function_pagination($params, $smarty) {
    if (!isset($params['data']) || !is_array($params['data'])) {
        trigger_error("pagination: missing 'data' parameter", E_USER_WARNING);
        return '';
    }
    
    if (!isset($params['url'])) {
        trigger_error("pagination: missing 'url' parameter", E_USER_WARNING);
        return '';
    }
    
    $pagination = $params['data'];
    $baseUrl = $params['url'];
    $status = isset($params['status']) ? $params['status'] : 'all';
    
    // If there's only one page, don't show pagination
    if (!isset($pagination['total_pages']) || $pagination['total_pages'] <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination justify-content-center">';
    
    // Previous button
    if ($pagination['page'] > 1) {
        $prevUrl = $baseUrl . '?page=' . ($pagination['page'] - 1);
        if ($status != 'all') {
            $prevUrl .= '&status=' . htmlspecialchars($status);
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($prevUrl) . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<a class="page-link" href="#" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $pageUrl = $baseUrl . '?page=' . $i;
        if ($status != 'all') {
            $pageUrl .= '&status=' . htmlspecialchars($status);
        }
        
        $activeClass = ($i == $pagination['page']) ? 'active' : '';
        $html .= '<li class="page-item ' . $activeClass . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($pageUrl) . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Next button
    if ($pagination['page'] < $pagination['total_pages']) {
        $nextUrl = $baseUrl . '?page=' . ($pagination['page'] + 1);
        if ($status != 'all') {
            $nextUrl .= '&status=' . htmlspecialchars($status);
        }
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($nextUrl) . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<a class="page-link" href="#" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span>';
        $html .= '</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

<?php
/**
 * TinyMCE Helper
 * Helper functions for working with TinyMCE editor
 */
namespace App\Helpers;

use App\Helpers\SystemSettingsHelper;

/**
 * TinyMCE Helper
 * Helper functions for working with TinyMCE editor
 */
final class TinyMCEHelper {
    /**
     * Include TinyMCE scripts in the page
     * 
     * @return string HTML to include TinyMCE
     */
    public static function includeTinyMCE() {
        // Use paths relative to the public directory with SystemSettingsHelper::get('SITE_URL')
        return '
        <!-- TinyMCE -->
        <script src="' . SystemSettingsHelper::get('SITE_URL') . '/vendor/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
        <script src="' . SystemSettingsHelper::get('SITE_URL') . '/js/tinymce-init.js"></script>
        ';
    }
    
    /**
     * Create a TinyMCE editor field
     * 
     * @param string $name Field name
     * @param string $value Initial content
     * @param string $id Field ID (optional)
     * @param int $rows Number of rows (optional)
     * @return string HTML for the editor
     */
    public static function editor($name, $value = '', $id = '', $rows = 10) {
        if (empty($id)) {
            $id = $name;
        }
        
        return '<textarea name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($id) . '" 
            class="tinymce-editor" rows="' . (int)$rows . '">' . htmlspecialchars($value) . '</textarea>';
    }
}

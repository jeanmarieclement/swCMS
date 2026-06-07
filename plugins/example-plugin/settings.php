<?php
/**
 * Plugin Settings Interface
 * This file provides a custom settings interface for the plugin
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    exit('Direct access denied');
}

/**
 * Render plugin settings form
 * This function generates the HTML for the plugin settings
 */
function example_plugin_render_settings($current_settings = []) {
    // Default settings
    $defaults = [
        'enabled' => true,
        'welcome_message' => 'Hello from Example Plugin!',
        'show_signature' => true,
        'debug_mode' => false,
        'custom_css' => '',
        'max_items' => 10
    ];
    
    // Merge with current settings
    $settings = array_merge($defaults, $current_settings);
    
    ob_start();
    ?>
    <div class="plugin-settings-form">
        <div class="row">
            <div class="col-md-8">
                <!-- Basic Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Basic Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enabled" name="settings[enabled]" 
                                       <?php echo $settings['enabled'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="enabled">
                                    <strong>Enable Plugin</strong>
                                </label>
                            </div>
                            <small class="text-muted">Enable or disable the plugin functionality.</small>
                        </div>

                        <div class="mb-3">
                            <label for="welcome_message" class="form-label">Welcome Message</label>
                            <input type="text" class="form-control" id="welcome_message" 
                                   name="settings[welcome_message]" value="<?php echo htmlspecialchars($settings['welcome_message']); ?>">
                            <small class="text-muted">Custom message displayed by the plugin.</small>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="show_signature" 
                                       name="settings[show_signature]" <?php echo $settings['show_signature'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_signature">
                                    Show Plugin Signature
                                </label>
                            </div>
                            <small class="text-muted">Display plugin signature on pages.</small>
                        </div>

                        <div class="mb-3">
                            <label for="max_items" class="form-label">Maximum Items</label>
                            <input type="number" class="form-control" id="max_items" name="settings[max_items]" 
                                   value="<?php echo $settings['max_items']; ?>" min="1" max="100">
                            <small class="text-muted">Maximum number of items to display (1-100).</small>
                        </div>
                    </div>
                </div>

                <!-- Advanced Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Advanced Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="debug_mode" 
                                       name="settings[debug_mode]" <?php echo $settings['debug_mode'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="debug_mode">
                                    Debug Mode
                                </label>
                            </div>
                            <small class="text-muted">Enable debug logging for troubleshooting.</small>
                        </div>

                        <div class="mb-3">
                            <label for="custom_css" class="form-label">Custom CSS</label>
                            <textarea class="form-control" id="custom_css" name="settings[custom_css]" 
                                      rows="5" placeholder="Enter custom CSS here..."><?php echo htmlspecialchars($settings['custom_css']); ?></textarea>
                            <small class="text-muted">Additional CSS styles for this plugin.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Plugin Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Plugin Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p><strong>Example Plugin</strong></p>
                            <p class="text-muted">Demonstrates the swCMS plugin system with hooks and filters.</p>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-1">
                                <span>Version:</span>
                                <strong>1.0.0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Author:</span>
                                <strong>swCMS Team</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Status:</span>
                                <strong class="text-success">Active</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Instructions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-book me-2"></i>Usage Instructions</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p><strong>Features:</strong></p>
                            <ul>
                                <li>Adds signature to page content</li>
                                <li>Provides admin dashboard widget</li>
                                <li>Custom CSS injection</li>
                                <li>Debug logging support</li>
                            </ul>
                            
                            <p><strong>Tips:</strong></p>
                            <ul class="mb-0">
                                <li>Enable debug mode for troubleshooting</li>
                                <li>Use custom CSS for styling</li>
                                <li>Adjust max items as needed</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}

/**
 * Validate plugin settings
 * This function validates the settings before saving
 */
function example_plugin_validate_settings($settings) {
    $validated = [];
    
    // Validate enabled flag
    $validated['enabled'] = !empty($settings['enabled']);
    
    // Validate welcome message
    $validated['welcome_message'] = cms_sanitize_text_field($settings['welcome_message'] ?? 'Hello from Example Plugin!');
    
    // Validate show signature flag
    $validated['show_signature'] = !empty($settings['show_signature']);
    
    // Validate debug mode flag
    $validated['debug_mode'] = !empty($settings['debug_mode']);
    
    // Validate custom CSS
    $validated['custom_css'] = strip_tags($settings['custom_css'] ?? '');
    
    // Validate max items
    $max_items = intval($settings['max_items'] ?? 10);
    $validated['max_items'] = max(1, min(100, $max_items));
    
    return $validated;
}

/**
 * Sanitize text field
 */
function cms_sanitize_text_field($text) {
    return trim(strip_tags($text));
}
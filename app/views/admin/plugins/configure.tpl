{* Plugin Configuration Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Configure Plugin - {$plugin.display_name}{/block}

{block name="head"}
<style>
    .plugin-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .setting-group {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
    }
    .setting-description {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/plugins">Plugins</a></li>
        <li class="breadcrumb-item"><a href="/admin/plugins/details?plugin={$plugin.name}">{$plugin.display_name}</a></li>
        <li class="breadcrumb-item active">Configure</li>
    </ol>
</nav>
{/block}

{block name="content"}
{* Plugin Header *}
<div class="plugin-header text-center">
    <div class="d-flex align-items-center justify-content-center mb-3">
        <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
            <i class="fas fa-puzzle-piece fa-2x"></i>
        </div>
        <div>
            <h2 class="mb-0">{$plugin.display_name}</h2>
            <p class="mb-0 opacity-75">Plugin Configuration</p>
        </div>
    </div>
    <p class="mb-0">{$plugin.description}</p>
</div>

{* Flash Messages *}
{if isset($flash) && $flash}
    <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
        {$flash.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<form method="POST" action="/admin/plugins/configure?plugin={$plugin.name}">
    <div class="row">
        <div class="col-lg-8">
            {* Configuration Form *}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Plugin Settings</h5>
                </div>
                <div class="card-body">
                    {* Basic Settings Example - This would be dynamically generated based on plugin *}
                    <div class="setting-group">
                        <h6>General Settings</h6>
                        
                        <div class="mb-3">
                            <label for="enabled" class="form-label">Enable Plugin</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enabled" name="settings[enabled]" 
                                       {if $settings.enabled|default:true}checked{/if}>
                                <label class="form-check-label" for="enabled">
                                    Enable this plugin functionality
                                </label>
                            </div>
                            <div class="setting-description">
                                Controls whether the plugin is actively processing requests.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="display_name" class="form-label">Display Name</label>
                            <input type="text" class="form-control" id="display_name" name="settings[display_name]" 
                                   value="{$settings.display_name|default:$plugin.display_name}">
                            <div class="setting-description">
                                Custom display name for this plugin in the admin interface.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="settings[priority]">
                                <option value="low" {if $settings.priority|default:'normal' == 'low'}selected{/if}>Low</option>
                                <option value="normal" {if $settings.priority|default:'normal' == 'normal'}selected{/if}>Normal</option>
                                <option value="high" {if $settings.priority|default:'normal' == 'high'}selected{/if}>High</option>
                            </select>
                            <div class="setting-description">
                                Plugin execution priority. Higher priority plugins run first.
                            </div>
                        </div>
                    </div>

                    {* Advanced Settings *}
                    <div class="setting-group">
                        <h6>Advanced Settings</h6>
                        
                        <div class="mb-3">
                            <label for="debug_mode" class="form-label">Debug Mode</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="debug_mode" name="settings[debug_mode]" 
                                       {if $settings.debug_mode|default:false}checked{/if}>
                                <label class="form-check-label" for="debug_mode">
                                    Enable debug logging for this plugin
                                </label>
                            </div>
                            <div class="setting-description">
                                When enabled, detailed logs will be written for debugging purposes.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cache_duration" class="form-label">Cache Duration (seconds)</label>
                            <input type="number" class="form-control" id="cache_duration" name="settings[cache_duration]" 
                                   value="{$settings.cache_duration|default:3600}" min="0">
                            <div class="setting-description">
                                How long to cache plugin data. Set to 0 to disable caching.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="custom_css" class="form-label">Custom CSS</label>
                            <textarea class="form-control" id="custom_css" name="settings[custom_css]" rows="5">{$settings.custom_css|default:''}</textarea>
                            <div class="setting-description">
                                Additional CSS styles for this plugin (optional).
                            </div>
                        </div>
                    </div>

                    {* Plugin-Specific Settings Note *}
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> This is a generic configuration interface. 
                        Individual plugins may provide their own specific settings interface 
                        by including a <code>settings.php</code> file in their directory.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {* Plugin Info Sidebar *}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Plugin Information</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Plugin:</span>
                            <strong>{$plugin.name}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Version:</span>
                            <strong>{$plugin.version}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Author:</span>
                            <strong>{$plugin.author}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Files:</span>
                            <strong>{$plugin.files|@count}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Has Settings:</span>
                            <strong>{if $plugin.has_settings}Yes{else}No{/if}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {* Save Actions *}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-save me-2"></i>Save Settings</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                        <a href="/admin/plugins/details?plugin={$plugin.name}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="resetToDefaults()">
                            <i class="fas fa-undo me-2"></i>Reset to Defaults
                        </button>
                    </div>
                </div>
            </div>

            {* Help Card *}
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Configuration Help</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Settings Storage:</strong></p>
                        <ul class="mb-3">
                            <li>Settings are stored in the database</li>
                            <li>Changes take effect immediately</li>
                            <li>Backup before major changes</li>
                        </ul>
                        
                        <p><strong>Troubleshooting:</strong></p>
                        <ul class="mb-0">
                            <li>Enable debug mode for detailed logs</li>
                            <li>Check plugin documentation</li>
                            <li>Reset to defaults if issues occur</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
{/block}

{block name="scripts"}
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.resetToDefaults = function() {
        if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
            // Reset form fields to defaults
            document.getElementById('enabled').checked = true;
            document.getElementById('display_name').value = '{$plugin.display_name}';
            document.getElementById('priority').value = 'normal';
            document.getElementById('debug_mode').checked = false;
            document.getElementById('cache_duration').value = '3600';
            document.getElementById('custom_css').value = '';
        }
    };
});
</script>
{/block}
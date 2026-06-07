{* Backup Manager Settings *}
{extends file="admin/layout.tpl"}

{block name="title"}Backup Settings{/block}

{block name="head"}
<link rel="stylesheet" href="/plugins/backup-manager/assets/css/backup-manager.css">
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/backup-manager">Backup Manager</a></li>
        <li class="breadcrumb-item active">Settings</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="backup-manager">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-cog me-2"></i>Backup Settings</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="/admin/backup-manager" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Backup Manager
            </a>
        </div>
    </div>



    <form method="POST" action="/admin/backup-manager/settings">
        <div class="row">
            <div class="col-12 row gx-3">
            
                {* General Settings *}
                <div class="card mb-4 col-6">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                    </div>
                    <div class="card-body">

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enabled" name="enabled"
                               {if $settings.enabled|default:true}checked{/if}>
                        <label class="form-check-label" for="enabled">
                            <strong>Enable Backup Manager</strong>
                            <div class="form-text">Enable or disable the entire backup system</div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_dashboard_widget" name="show_dashboard_widget"
                               {if $settings.show_dashboard_widget|default:true}checked{/if}>
                        <label class="form-check-label" for="show_dashboard_widget">
                            <strong>Show Dashboard Widget</strong>
                            <div class="form-text">Display backup widget on admin dashboard</div>
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_cleanup" name="auto_cleanup"
                               {if $settings.auto_cleanup|default:true}checked{/if}>
                        <label class="form-check-label" for="auto_cleanup">
                            <strong>Automatic Cleanup</strong>
                            <div class="form-text">Automatically delete old backups</div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="retention_days" class="form-label">Retention Days</label>
                        <input type="number" class="form-control" id="retention_days" name="retention_days"
                               value="{$settings.retention_days|default:30}" min="1" max="365">
                        <div class="form-text">Number of days to keep backups before deletion</div>
                    </div>
                </div>
            </div>

            {* Backup Configuration *}
            <div class="card mb-4 col-6">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-archive me-2"></i>Backup Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="max_backup_size" class="form-label">Maximum Backup Size</label>
                        <input type="text" class="form-control" id="max_backup_size" name="max_backup_size" 
                               value="{$settings.max_backup_size|default:'500MB'}" placeholder="500MB">
                        <div class="form-text">Maximum allowed backup file size (e.g., 500MB, 1GB)</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="backup_timeout" class="form-label">Backup Timeout (seconds)</label>
                        <input type="number" class="form-control" id="backup_timeout" name="backup_timeout" 
                               value="{$settings.backup_timeout|default:300}" min="60" max="3600">
                        <div class="form-text">Maximum time to wait for backup completion</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="compression_level" class="form-label">Compression Level</label>
                        <select class="form-control" id="compression_level" name="compression_level">
                            <option value="0" {if ($settings.compression_level|default:6) == 0}selected{/if}>No Compression</option>
                            <option value="1" {if ($settings.compression_level|default:6) == 1}selected{/if}>Fastest</option>
                            <option value="3" {if ($settings.compression_level|default:6) == 3}selected{/if}>Fast</option>
                            <option value="6" {if ($settings.compression_level|default:6) == 6}selected{/if}>Normal (Recommended)</option>
                            <option value="9" {if ($settings.compression_level|default:6) == 9}selected{/if}>Best Compression</option>
                        </select>
                        <div class="form-text">Higher levels provide better compression but take longer</div>
                    </div>
                </div>
            </div>

            {* Include/Exclude Settings *}
            <div class="card mb-4 col-6">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Include/Exclude Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="include_uploads" name="include_uploads" 
                               {if $settings.include_uploads|default:true}checked{/if}>
                        <label class="form-check-label" for="include_uploads">
                            <strong>Include Uploads</strong>
                            <div class="form-text">Include uploaded files and media</div>
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="include_themes" name="include_themes" 
                               {if $settings.include_themes|default:true}checked{/if}>
                        <label class="form-check-label" for="include_themes">
                            <strong>Include Themes</strong>
                            <div class="form-text">Include theme files in backup</div>
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="include_plugins" name="include_plugins" 
                               {if $settings.include_plugins|default:false}checked{/if}>
                        <label class="form-check-label" for="include_plugins">
                            <strong>Include Plugins</strong>
                            <div class="form-text">Include plugin files in backup</div>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="exclude_patterns" class="form-label">Exclude Patterns</label>
                        <textarea class="form-control" id="exclude_patterns" name="exclude_patterns" rows="8" 
                                  placeholder="node_modules/*&#10;.git/*&#10;*.log">{if ($settings.exclude_patterns|default:[])|count > 0}{$settings.exclude_patterns|implode:"\n"}{else}node_modules/*
.git/*
vendor/*
*.log
cache/*
tmp/*
backups/*{/if}</textarea>
                        <div class="form-text">File patterns to exclude from backups (one per line)</div>
                    </div>
                </div>
            </div>

            {* Email Notifications *}
            <div class="card mb-4 col-6">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" 
                               {if $settings.email_notifications|default:false}checked{/if}>
                        <label class="form-check-label" for="email_notifications">
                            <strong>Enable Email Notifications</strong>
                            <div class="form-text">Send email notifications for backup events</div>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="notification_email" class="form-label">Notification Email</label>
                        <input type="email" class="form-control" id="notification_email" name="notification_email" 
                               value="{$settings.notification_email|default:''}" placeholder="admin@example.com">
                        <div class="form-text">Email address to receive backup notifications</div>
                    </div>
                </div>
            </div>

            {* Advanced Settings *}
            <div class="card mb-4 col-6">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Advanced Settings</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>System Information</strong><br>
                        <small>
                            <strong>PHP Version:</strong> {$system_info.php_version}<br>
                            <strong>Memory Limit:</strong> {$system_info.memory_limit}<br>
                            <strong>Max Execution Time:</strong> {$system_info.max_execution_time} seconds<br>
                            <strong>ZIP Extension:</strong> {if $system_info.zip_available}Available{else}Not Available{/if}<br>
                            <strong>Backup Directory:</strong> {$smarty.const.ROOT_PATH}/backups ({if $system_info.backup_dir_writable}Writable{else}Not Writable{/if})
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Database Type</label>
                        <input type="text" class="form-control" value="{if defined('DB_DRIVER')}{$smarty.const.DB_DRIVER|upper}{else}Unknown{/if}" readonly>
                        <div class="form-text">Current database configuration</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Backup Directory Status</label>
                        <input type="text" class="form-control" 
                               value="{if $system_info.backup_dir_writable}Writable{else}Not Writable{/if}" 
                               class="{if $system_info.backup_dir_writable}text-success{else}text-danger{/if}" readonly>
                        <div class="form-text">Backup directory write permissions</div>
                    </div>
                </div>
            </div>

            </div>
        </div>

        {* Save Button *}
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{* Help Information *}
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-question-circle me-2"></i>Backup Settings Help</h6>
                <div class="row">
                    <div class="col-md-4">
                        <h6>General Settings</h6>
                        <ul class="small">
                            <li><strong>Enable Backup Manager:</strong> Master switch for the entire backup system</li>
                            <li><strong>Dashboard Widget:</strong> Shows backup status on admin dashboard</li>
                            <li><strong>Auto Cleanup:</strong> Automatically removes old backups based on retention days</li>
                            <li><strong>Retention Days:</strong> How long to keep backups before deletion</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Backup Configuration</h6>
                        <ul class="small">
                            <li><strong>Max Size:</strong> Prevents creating backups that are too large</li>
                            <li><strong>Timeout:</strong> Maximum time allowed for backup operations</li>
                            <li><strong>Compression:</strong> Balance between file size and processing time</li>
                            <li><strong>Include/Exclude:</strong> Control what files are included in backups</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Best Practices</h6>
                        <ul class="small">
                            <li>Test backups regularly to ensure they work</li>
                            <li>Store backups in multiple locations for safety</li>
                            <li>Monitor disk space to prevent storage issues</li>
                            <li>Use appropriate compression levels for your server</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle email settings based on notification checkbox
    const emailCheckbox = document.getElementById('email_notifications');
    const emailInput = document.getElementById('notification_email');
    
    function toggleEmailSettings() {
        emailInput.disabled = !emailCheckbox.checked;
        if (!emailCheckbox.checked) {
            emailInput.value = '';
        }
    }
    
    emailCheckbox.addEventListener('change', toggleEmailSettings);
    toggleEmailSettings(); // Initial state
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const retentionDays = document.getElementById('retention_days').value;
        const timeout = document.getElementById('backup_timeout').value;
        
        if (retentionDays < 1 || retentionDays > 365) {
            alert('Retention days must be between 1 and 365');
            e.preventDefault();
            return;
        }
        
        if (timeout < 60 || timeout > 3600) {
            alert('Backup timeout must be between 60 and 3600 seconds');
            e.preventDefault();
            return;
        }
        
        if (emailCheckbox.checked && !emailInput.value) {
            alert('Please enter a notification email address');
            e.preventDefault();
            return;
        }
    });
});
</script>
{/block}
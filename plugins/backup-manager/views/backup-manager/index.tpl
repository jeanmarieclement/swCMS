{* Backup Manager Main Dashboard *}
{extends file="admin/layout.tpl"}

{block name="title"}Backup Manager{/block}

{block name="head"}
<link rel="stylesheet" href="/plugins/backup-manager/assets/css/backup-manager.css">
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item active">Backup Manager</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="backup-manager">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-download me-2"></i>Backup Manager</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-primary" data-backup-type="database">
                    <i class="fas fa-database"></i> Database Backup
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-backup-type="files">
                    <i class="fas fa-folder"></i> Files Backup
                </button>
            </div>
        </div>
    </div>


    {* Active Schedules - Full Width *}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Active Schedules</h5>
            <a href="/admin/backup-manager/schedules" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-cogs"></i> Manage Schedules
            </a>
        </div>
        <div class="card-body p-0">
            {if $schedules && count($schedules) > 0}
                <div class="table-responsive">
                    <table class="table table-hover mb-0 schedule-table">
                        <thead>
                            <tr>
                                <th>Schedule Name</th>
                                <th>Type</th>
                                <th>Frequency</th>
                                <th>Time</th>
                                <th>Next Run</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$schedules item=schedule}
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{$schedule.name}</div>
                                        <small class="text-muted">
                                            Created {$schedule.created_at|date_format:'M j, Y'}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="type-badge type-{$schedule.type}">
                                            {if $schedule.type == 'full'}
                                                <i class="fas fa-database me-1"></i>Full
                                            {elseif $schedule.type == 'database'}
                                                <i class="fas fa-table me-1"></i>Database
                                            {else}
                                                <i class="fas fa-folder me-1"></i>Files
                                            {/if}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="frequency-badge freq-{$schedule.frequency}">
                                            <i class="fas fa-{if $schedule.frequency == 'daily'}calendar-day{elseif $schedule.frequency == 'weekly'}calendar-week{else}calendar{/if} me-1"></i>
                                            {$schedule.frequency|ucfirst}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{$schedule.time}</span>
                                    </td>
                                    <td>
                                        {if $schedule.next_run}
                                            <div class="fw-bold">{$schedule.next_run|date_format:'M j'}</div>
                                            <small class="text-muted">{$schedule.next_run|date_format:'H:i'}</small>
                                        {else}
                                            <span class="text-muted">—</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <span class="badge {if $schedule.active}bg-success{else}bg-secondary{/if}">
                                            {if $schedule.active}Active{else}Inactive{/if}
                                        </span>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-clock"></i>
                        <h4>No Active Schedules</h4>
                        <p class="text-muted mb-3">Create your first backup schedule to automate backups.</p>
                        <a href="/admin/backup-manager/schedules" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Create Schedule
                        </a>
                    </div>
                </div>
            {/if}
        </div>
    </div>

    {* Two Column Layout: Recent Backups + System Information *}
    <div class="row">
        {* Recent Backups *}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Backups</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary btn-sm" data-backup-type="database">
                            <i class="fas fa-database"></i> Database
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-backup-type="files">
                            <i class="fas fa-folder"></i> Files
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="backupList">
                        {if $backups|@count > 0}
                            {foreach from=$backups item=backup}
                                <div class="backup-card backup-{$backup.status} mb-3">
                                    <div class="card-header">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="backup-type-badge backup-type-{$backup.type}">
                                                    {$backup.type|upper}
                                                </span>
                                                <span class="status-badge status-{$backup.status} ms-2">
                                                    {$backup.status|upper}
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                {$backup.created_at|date_format:"%Y-%m-%d %H:%M"}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="mb-2">
                                                    <strong>File:</strong> {$backup.filename|default:'N/A'}
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Size:</strong> {if $backup.file_size}{($backup.file_size/1024/1024)|number_format:2} MB{else}0 MB{/if}
                                                </div>
                                                {if $backup.completed_at}
                                                    <div class="mb-2">
                                                        <strong>Completed:</strong> {$backup.completed_at|date_format:"%Y-%m-%d %H:%M"}
                                                    </div>
                                                {/if}
                                                {if $backup.error_message}
                                                    <div class="text-danger">
                                                        <strong>Error:</strong> {$backup.error_message}
                                                    </div>
                                                {/if}
                                            </div>
                                            <div class="col-md-4">
                                                <div class="backup-actions">
                                                    {if $backup.status == 'completed'}
                                                        <button class="btn btn-backup btn-sm" data-backup-download="{$backup.id}">
                                                            <i class="fas fa-download"></i> Download
                                                        </button>
                                                        <button class="btn btn-backup-secondary btn-sm" data-backup-restore="{$backup.id}">
                                                            <i class="fas fa-upload"></i> Restore
                                                        </button>
                                                    {/if}
                                                    <button class="btn btn-backup-danger btn-sm" data-backup-delete="{$backup.id}">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        {if $backup.status == 'running'}
                                            <div class="backup-progress indeterminate mt-3">
                                                <div class="progress-bar"></div>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-center py-5">
                                <i class="fas fa-download fa-3x text-muted mb-3"></i>
                                <h5>No Backups Found</h5>
                                <p class="text-muted">Create your first backup to get started.</p>
                                <button class="btn btn-primary" data-backup-type="database">
                                    <i class="fas fa-database"></i> Create Database Backup
                                </button>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>

        {* System Information *}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>PHP Version:</span>
                            <span class="fw-bold">{$system_info.php_version}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Memory Limit:</span>
                            <span class="fw-bold">{$system_info.memory_limit}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Max Execution:</span>
                            <span class="fw-bold">{$system_info.max_execution_time}s</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>ZIP Support:</span>
                            <span class="fw-bold {if $system_info.zip_available}text-success{else}text-danger{/if}">
                                {if $system_info.zip_available}Available{else}Not Available{/if}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Backup Directory:</span>
                            <span class="fw-bold {if $system_info.backup_dir_writable}text-success{else}text-danger{/if}">
                                {if $system_info.backup_dir_writable}Writable{else}Not Writable{/if}
                            </span>
                        </div>
                    </div>
                    
                    {* Quick Actions in System Info Card *}
                    <hr>
                    <h6 class="mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="/admin/backup-manager/schedules" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-clock"></i> Manage Schedules
                        </a>
                        <a href="/admin/backup-manager/settings" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <button class="btn btn-outline-warning btn-sm" data-cleanup-backups>
                            <i class="fas fa-broom"></i> Cleanup Old Backups
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script src="/plugins/backup-manager/assets/js/backup-manager.js"></script>
{/block}
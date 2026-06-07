{* Backup Manager Schedules - Modern Design *}
{extends file="admin/layout.tpl"}

{block name="title"}Backup Schedules{/block}

{block name="head"}
<link rel="stylesheet" href="/plugins/backup-manager/assets/css/backup-manager.css">

{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/backup-manager">Backup Manager</a></li>
        <li class="breadcrumb-item active">Schedules</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="backup-manager">
    {* Header *}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-clock text-primary me-2"></i>Backup Schedules
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="/admin/backup-manager" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Backup Manager
                </a>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                    <i class="fas fa-plus me-1"></i>Create Schedule
                </button>
            </div>
        </div>
    </div>


    {* Statistics Row *}
    <div class="row stats-row">
        <div class="col-md-4">
            <div class="stat-card">
                <span class="stat-number">{$schedules|@count}</span>
                <span class="stat-label">
                    <i class="fas fa-calendar-alt me-1"></i>Total Schedules
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card active">
                <span class="stat-number">
                    {assign var="active_count" value=0}
                    {foreach from=$schedules item=schedule}
                        {if $schedule.active}{assign var="active_count" value=$active_count+1}{/if}
                    {/foreach}
                    {$active_count}
                </span>
                <span class="stat-label">
                    <i class="fas fa-play me-1"></i>Active Schedules
                </span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card next">
                <span class="stat-number">
                    {assign var="next_schedule" value=null}
                    {foreach from=$schedules item=schedule}
                        {if $schedule.active && $schedule.next_run}
                            {if !$next_schedule || $schedule.next_run < $next_schedule.next_run}
                                {assign var="next_schedule" value=$schedule}
                            {/if}
                        {/if}
                    {/foreach}
                    {if $next_schedule}
                        <i class="fas fa-clock"></i>
                    {else}
                        <i class="fas fa-minus"></i>
                    {/if}
                </span>
                <span class="stat-label">
                    {if $next_schedule}
                        Next: {$next_schedule.next_run|date_format:'M j, H:i'}
                    {else}
                        No upcoming backups
                    {/if}
                </span>
            </div>
        </div>
    </div>

    {* Schedules Table *}
    {if $schedules && count($schedules) > 0}
        <div class="card">
            <div class="card-body p-0">
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
                                <th>Actions</th>
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
                                        <label class="status-toggle" title="Toggle schedule status">
                                            <input type="checkbox" {if $schedule.active}checked{/if} 
                                                   onchange="toggleSchedule({$schedule.id}, this.checked)">
                                            <span class="status-slider"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="editSchedule({$schedule.id})" 
                                                    title="Edit schedule">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteSchedule({$schedule.id}, '{$schedule.name}')" 
                                                    title="Delete schedule">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    {else}
        <div class="card">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-clock"></i>
                    <h4>No Backup Schedules</h4>
                    <p class="text-muted mb-3">Get started by creating your first backup schedule.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createScheduleModal">
                        <i class="fas fa-plus me-1"></i>Create Your First Schedule
                    </button>
                </div>
            </div>
        </div>
    {/if}
</div>

{* Create Schedule Modal *}
<div class="modal fade" id="createScheduleModal" tabindex="-1" aria-labelledby="createScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createScheduleModalLabel">
                    <i class="fas fa-plus me-2"></i>Create Backup Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/admin/backup-manager/schedules" id="createScheduleForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="schedule_name" class="form-label">Schedule Name</label>
                        <input type="text" class="form-control" id="schedule_name" name="name" required 
                               placeholder="e.g., Daily Full Backup">
                    </div>
                    
                    <div class="mb-3">
                        <label for="schedule_type" class="form-label">Backup Type</label>
                        <select class="form-select" id="schedule_type" name="type" required>
                            <option value="">Choose backup type...</option>
                            <option value="full">Full Backup (Database + Files)</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule_frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="schedule_frequency" name="frequency" required>
                                    <option value="">Choose frequency...</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="schedule_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="schedule_time" name="time" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{* Edit Schedule Modal *}
<div class="modal fade" id="editScheduleModal" tabindex="-1" aria-labelledby="editScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editScheduleModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Backup Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="/admin/backup-manager/schedules" id="editScheduleForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="schedule_id" id="edit_schedule_id">
                    
                    <div class="mb-3">
                        <label for="edit_schedule_name" class="form-label">Schedule Name</label>
                        <input type="text" class="form-control" id="edit_schedule_name" name="name" required 
                               placeholder="e.g., Daily Full Backup">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_schedule_type" class="form-label">Backup Type</label>
                        <select class="form-select" id="edit_schedule_type" name="type" required>
                            <option value="">Choose backup type...</option>
                            <option value="full">Full Backup (Database + Files)</option>
                            <option value="database">Database Only</option>
                            <option value="files">Files Only</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_schedule_frequency" class="form-label">Frequency</label>
                                <select class="form-select" id="edit_schedule_frequency" name="frequency" required>
                                    <option value="">Choose frequency...</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_schedule_time" class="form-label">Time</label>
                                <input type="time" class="form-control" id="edit_schedule_time" name="time" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Update Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSchedule(scheduleId, isActive) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/admin/backup-manager/schedules';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'toggle';
    
    const scheduleIdInput = document.createElement('input');
    scheduleIdInput.name = 'schedule_id';
    scheduleIdInput.value = scheduleId;
    
    form.appendChild(actionInput);
    form.appendChild(scheduleIdInput);
    
    document.body.appendChild(form);
    form.submit();
}

function deleteSchedule(scheduleId, scheduleName) {
    if (confirm('Are you sure you want to delete the schedule "' + scheduleName + '"?\n\nThis action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/backup-manager/schedules';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        const scheduleIdInput = document.createElement('input');
        scheduleIdInput.name = 'schedule_id';
        scheduleIdInput.value = scheduleId;
        
        form.appendChild(actionInput);
        form.appendChild(scheduleIdInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function editSchedule(scheduleId) {
    // Find the schedule data in the page
    const tableRow = document.querySelector('button[onclick*="editSchedule(' + scheduleId + ')"]').closest('tr');
    
    if (!tableRow) {
        alert('Schedule data not found');
        return;
    }
    
    // Extract data from the table row
    const scheduleName = tableRow.querySelector('td:first-child .fw-bold').textContent;
    const typeElement = tableRow.querySelector('.type-badge');
    const freqElement = tableRow.querySelector('.frequency-badge');
    const timeElement = tableRow.querySelector('td:nth-child(4) .fw-bold');
    
    // Get type value from badge class
    let scheduleType = 'full';
    if (typeElement.classList.contains('type-database')) scheduleType = 'database';
    else if (typeElement.classList.contains('type-files')) scheduleType = 'files';
    
    // Get frequency value from badge class
    let scheduleFreq = 'daily';
    if (freqElement.classList.contains('freq-weekly')) scheduleFreq = 'weekly';
    else if (freqElement.classList.contains('freq-monthly')) scheduleFreq = 'monthly';
    
    const scheduleTime = timeElement.textContent.trim();
    
    // Populate the edit modal
    document.getElementById('edit_schedule_id').value = scheduleId;
    document.getElementById('edit_schedule_name').value = scheduleName;
    document.getElementById('edit_schedule_type').value = scheduleType;
    document.getElementById('edit_schedule_frequency').value = scheduleFreq;
    document.getElementById('edit_schedule_time').value = scheduleTime;
    
    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
    editModal.show();
}
</script>
{/block}
{* Roles & Permissions Management Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Roles & Permissions{/block}

{block name="head"}
<style>
    .role-card {
        transition: transform 0.2s ease-in-out;
    }
    .role-card:hover {
        transform: translateY(-2px);
    }
    .role-level-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
    }
    .role-level-4 { background: #dc3545; color: white; }
    .role-level-3 { background: #fd7e14; color: white; }
    .role-level-2 { background: #20c997; color: white; }
    .role-level-1 { background: #0dcaf0; color: white; }
    .role-level-0 { background: #6c757d; color: white; }
    .permission-badge {
        font-size: 0.75rem;
        margin: 0.125rem;
    }
    .permission-list {
        max-height: 120px;
        overflow-y: auto;
    }
    .role-icon {
        width: 48px;
        height: 48px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    .role-actions {
        min-height: 50px;
        display: flex;
        align-items: center;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item active">Roles & Permissions</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Roles & Permissions</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='/admin/roles/create'">
            <i class="fas fa-plus"></i> Create Role
        </button>
    </div>
</div>

{* System Info *}
<div class="card border-info mb-4">
    <div class="card-body bg-light text-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Role System:</strong> {$roles|@count} roles configured with hierarchical permissions (0=Subscriber, 4=Super Admin)
    </div>
</div>

{* Roles Grid *}
<div class="row">
    {if $roles|@count > 0}
        {foreach from=$roles item=role}
            {* Determine role icon based on level *}
            {if $role.level == 4}
                {assign var="role_icon" value="fas fa-crown"}
                {assign var="role_color" value="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);"}
            {elseif $role.level == 3}
                {assign var="role_icon" value="fas fa-user-shield"}
                {assign var="role_color" value="background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);"}
            {elseif $role.level == 2}
                {assign var="role_icon" value="fas fa-user-edit"}
                {assign var="role_color" value="background: linear-gradient(135deg, #20c997 0%, #13795b 100%);"}
            {elseif $role.level == 1}
                {assign var="role_icon" value="fas fa-user-pen"}
                {assign var="role_color" value="background: linear-gradient(135deg, #0dcaf0 0%, #055160 100%);"}
            {else}
                {assign var="role_icon" value="fas fa-user"}
                {assign var="role_color" value="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);"}
            {/if}
            
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card h-100 role-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{$role.name|capitalize}</h5>
                        <span class="role-level-badge role-level-{$role.level}">
                            Level {$role.level}
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="role-icon" style="{$role_color}">
                                <i class="{$role_icon}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="card-text">{$role.description|default:'No description available'}</p>
                                <div class="small text-muted">
                                    <div><strong>Access Level:</strong> {$role.level}/4</div>
                                    <div><strong>Users:</strong> {$role.user_count|default:0}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="permission-list mb-3">
                            <h6 class="small text-muted mb-2">PERMISSIONS:</h6>
                            {if isset($role.permissions) && $role.permissions|@count > 0}
                                {if isset($role.permissions[0]) && $role.permissions[0] === "*"}
                                    <span class="badge bg-danger permission-badge">
                                        <i class="fas fa-star me-1"></i>Full Access
                                    </span>
                                {else}
                                    {foreach from=$role.permissions item=permission}
                                        <span class="badge bg-primary permission-badge">
                                            <i class="fas fa-check me-1"></i>{$permission|capitalize}
                                        </span>
                                    {/foreach}
                                {/if}
                            {else}
                                <span class="badge bg-secondary permission-badge">No permissions assigned</span>
                            {/if}
                        </div>
                        
                        <div class="role-actions">
                            <a href="/admin/roles/edit/{$role.id}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-edit"></i> Edit Permissions
                            </a>
                            
                            {if $role.name != 'super_admin' && $role.name != 'admin'}
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="confirmDelete('{$role.id}', '{$role.name}')" title="Delete Role">
                                    <i class="fas fa-trash"></i>
                                </button>
                            {else}
                                <span class="badge bg-warning text-dark small">Protected</span>
                            {/if}
                        </div>
                    </div>
                    
                    {* Footer with additional info *}
                    <div class="card-footer bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                Created: {$role.created_at|date_format:"%d/%m/%Y"}
                            </small>
                            <small class="text-muted">
                                {if isset($role.permissions[0]) && $role.permissions[0] === "*"}
                                    <i class="fas fa-unlock text-warning me-1"></i>Full Access
                                {else}
                                    <i class="fas fa-shield-alt text-success me-1"></i>{$role.permissions|@count} Permissions
                                {/if}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-user-tag fa-3x mb-3 text-muted"></i>
                    <h5>No Roles Found</h5>
                    <p class="text-muted">No user roles are currently configured in your system.</p>
                    <a href="/admin/roles/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Role
                    </a>
                </div>
            </div>
        </div>
    {/if}
</div>

{* Role Hierarchy Info *}
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-question-circle me-2"></i>Role System Help</h6>
                <div class="row">
                    <div class="col-md-4">
                        <h6>Role Hierarchy</h6>
                        <ul class="small mb-3">
                            <li class="mb-2"><span class="role-level-badge role-level-4">Level 4</span> Super Admin - Full system access</li>
                            <li class="mb-2"><span class="role-level-badge role-level-3">Level 3</span> Admin - Administrative access</li>
                            <li class="mb-2"><span class="role-level-badge role-level-2">Level 2</span> Editor - Content management</li>
                            <li class="mb-2"><span class="role-level-badge role-level-1">Level 1</span> Author - Content creation</li>
                            <li class="mb-2"><span class="role-level-badge role-level-0">Level 0</span> Subscriber - Read-only access</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Permission Types</h6>
                        <ul class="small mb-3">
                            <li><strong>Template Access:</strong> Admin page access control</li>
                            <li><strong>Content Permissions:</strong> Create, edit, delete content</li>
                            <li><strong>User Management:</strong> Manage other users</li>
                            <li><strong>System Settings:</strong> Configuration access</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Best Practices</h6>
                        <ul class="small mb-0">
                            <li>Assign minimum required permissions</li>
                            <li>Regular permission audits</li>
                            <li>Use role hierarchy effectively</li>
                            <li>Protect system-critical roles</li>
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
    // Role deletion confirmation
    window.confirmDelete = function(roleId, roleName) {
        if (confirm('Are you sure you want to delete the "' + roleName + '" role? This action cannot be undone and will affect all users with this role.')) {
            // Create a form to submit the delete request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/roles/delete/' + roleId;
            
            // Add CSRF token if available
            if (typeof csrfToken !== 'undefined') {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    };
});
</script>
{/block}
{* Role Edit Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Edit Role - {$role.name|capitalize}{/block}

{block name="head"}
<style>
    .role-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 0.375rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    .permission-group {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
    }
    .permission-item {
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        border: 1px solid #e9ecef;
        border-radius: 0.25rem;
        background: white;
    }
    .permission-item:hover {
        background: #f8f9fa;
    }
    .role-level-indicator {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        margin-right: 1rem;
    }
    .level-4 { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
    .level-3 { background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%); }
    .level-2 { background: linear-gradient(135deg, #20c997 0%, #13795b 100%); }
    .level-1 { background: linear-gradient(135deg, #0dcaf0 0%, #055160 100%); }
    .level-0 { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/roles">Roles & Permissions</a></li>
        <li class="breadcrumb-item active">Edit {$role.name|capitalize}</li>
    </ol>
</nav>
{/block}

{block name="content"}
{* Role Header *}
<div class="role-header text-center">
    <div class="d-flex align-items-center justify-content-center mb-3">
        <div class="role-level-indicator level-{$role.level}">
            {$role.level}
        </div>
        <div>
            <h2 class="mb-0">{$role.name|capitalize} Role</h2>
            <p class="mb-0 opacity-75">Level {$role.level} - Permission Management</p>
        </div>
    </div>
    <p class="mb-0">{$role.description|default:'Configure permissions for this role'}</p>
</div>



<form method="POST" action="/admin/roles/edit/{$role.id}">
    <input type="hidden" name="csrf_token" value="{$csrf_token}">
    <div class="row">
        <div class="col-lg-8">
            {* Permission Groups *}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Template Access Permissions</h5>
                </div>
                <div class="card-body">
                    {* Full Access Option *}
                    <div class="permission-group">
                        <h6 class="text-danger">Full System Access</h6>
                        <div class="permission-item">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="all_permissions" name="permissions[]" value="all"
                                       {if isset($role.permissions[0]) && $role.permissions[0] === "*"}checked{/if}
                                       onchange="toggleAllPermissions(this)">
                                <label class="form-check-label fw-bold text-danger" for="all_permissions">
                                    <i class="fas fa-crown me-2"></i>Grant Full Access (All Permissions)
                                </label>
                                <div class="small text-muted">
                                    WARNING: This grants unrestricted access to all admin functions
                                </div>
                            </div>
                        </div>
                    </div>

                    {* Individual Permissions *}
                    <div id="individual_permissions">
                        <div class="permission-group">
                            <h6>Core Administration</h6>
                            {assign var="core_permissions" value=["dashboard", "profile", "settings"]}
                            {foreach from=$core_permissions item=permission}
                                <div class="permission-item">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               id="permission_{$permission}" name="permissions[]" value="{$permission}"
                                               {if isset($role.permissions) && in_array($permission, $role.permissions)}checked{/if}>
                                        <label class="form-check-label" for="permission_{$permission}">
                                            {if $permission == 'dashboard'}
                                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard Access
                                            {elseif $permission == 'profile'}
                                                <i class="fas fa-user me-2"></i>Profile Management  
                                            {elseif $permission == 'settings'}
                                                <i class="fas fa-cogs me-2"></i>System Settings
                                            {/if}
                                        </label>
                                        <div class="small text-muted">
                                            {if $permission == 'dashboard'}
                                                Access to the main admin dashboard
                                            {elseif $permission == 'profile'}
                                                Manage own profile and account settings
                                            {elseif $permission == 'settings'}
                                                Modify system configuration and settings
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        <div class="permission-group">
                            <h6>Content Management</h6>
                            {assign var="content_permissions" value=["articles", "pages", "categories", "tags", "media", "comments"]}
                            {foreach from=$content_permissions item=permission}
                                <div class="permission-item">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               id="permission_{$permission}" name="permissions[]" value="{$permission}"
                                               {if isset($role.permissions) && in_array($permission, $role.permissions)}checked{/if}>
                                        <label class="form-check-label" for="permission_{$permission}">
                                            {if $permission == 'articles'}
                                                <i class="fas fa-newspaper me-2"></i>Articles & Posts
                                            {elseif $permission == 'pages'}
                                                <i class="fas fa-file-alt me-2"></i>Pages Management
                                            {elseif $permission == 'categories'}
                                                <i class="fas fa-folder me-2"></i>Categories
                                            {elseif $permission == 'tags'}
                                                <i class="fas fa-tags me-2"></i>Tags Management
                                            {elseif $permission == 'media'}
                                                <i class="fas fa-images me-2"></i>Media Library
                                            {elseif $permission == 'comments'}
                                                <i class="fas fa-comments me-2"></i>Comments Moderation
                                            {/if}
                                        </label>
                                        <div class="small text-muted">
                                            {if $permission == 'articles'}
                                                Create, edit, and manage articles and blog posts
                                            {elseif $permission == 'pages'}
                                                Manage static pages and page content
                                            {elseif $permission == 'categories'}
                                                Organize content with categories
                                            {elseif $permission == 'tags'}
                                                Create and manage content tags
                                            {elseif $permission == 'media'}
                                                Upload and manage media files
                                            {elseif $permission == 'comments'}
                                                Moderate and manage user comments
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        <div class="permission-group">
                            <h6>User & System Management</h6>
                            {assign var="admin_permissions" value=["users", "roles", "themes", "plugins"]}
                            {foreach from=$admin_permissions item=permission}
                                <div class="permission-item">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               id="permission_{$permission}" name="permissions[]" value="{$permission}"
                                               {if isset($role.permissions) && in_array($permission, $role.permissions)}checked{/if}>
                                        <label class="form-check-label" for="permission_{$permission}">
                                            {if $permission == 'users'}
                                                <i class="fas fa-users me-2"></i>User Management
                                            {elseif $permission == 'roles'}
                                                <i class="fas fa-user-tag me-2"></i>Roles & Permissions
                                            {elseif $permission == 'themes'}
                                                <i class="fas fa-palette me-2"></i>Theme Management
                                            {elseif $permission == 'plugins'}
                                                <i class="fas fa-puzzle-piece me-2"></i>Plugin Management
                                            {/if}
                                        </label>
                                        <div class="small text-muted">
                                            {if $permission == 'users'}
                                                Manage user accounts and user data
                                            {elseif $permission == 'roles'}
                                                Configure roles and permission settings
                                            {elseif $permission == 'themes'}
                                                Install and configure site themes
                                            {elseif $permission == 'plugins'}
                                                Manage and configure system plugins
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {* Role Information *}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Role Information</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Role Name:</span>
                            <strong>{$role.name}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Access Level:</span>
                            <strong>{$role.level}/4</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Users:</span>
                            <strong>{$role.user_count|default:0}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Created:</span>
                            <strong>{$role.created_at|date_format:"%d/%m/%Y"}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Protected:</span>
                            <strong>{if $role.name == 'super_admin' || $role.name == 'admin'}Yes{else}No{/if}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {* Save Actions *}
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-save me-2"></i>Save Changes</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Permissions
                        </button>
                        <a href="/admin/roles" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Cancel Changes
                        </a>
                        <button type="button" class="btn btn-outline-warning" onclick="resetPermissions()">
                            <i class="fas fa-undo me-2"></i>Reset to Default
                        </button>
                    </div>
                </div>
            </div>

            {* Help Card *}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Permission Help</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p><strong>Permission Levels:</strong></p>
                        <ul class="mb-3">
                            <li><strong>Full Access:</strong> All permissions granted</li>
                            <li><strong>Individual:</strong> Specific permissions only</li>
                            <li><strong>None:</strong> No admin access</li>
                        </ul>
                        
                        <p><strong>Best Practices:</strong></p>
                        <ul class="mb-0">
                            <li>Grant minimum required permissions</li>
                            <li>Review permissions regularly</li>
                            <li>Test role functionality</li>
                            <li>Document role purposes</li>
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
    const allPermissionsCheckbox = document.getElementById('all_permissions');
    const individualPermissions = document.getElementById('individual_permissions');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    // Initialize state based on current selection
    if (allPermissionsCheckbox && allPermissionsCheckbox.checked) {
        individualPermissions.style.opacity = '0.5';
        individualPermissions.style.pointerEvents = 'none';
    }

    window.toggleAllPermissions = function(checkbox) {
        if (checkbox.checked) {
            // Disable individual permissions
            individualPermissions.style.opacity = '0.5';
            individualPermissions.style.pointerEvents = 'none';
            
            // Uncheck all individual permissions
            permissionCheckboxes.forEach(cb => cb.checked = false);
        } else {
            // Enable individual permissions
            individualPermissions.style.opacity = '1';
            individualPermissions.style.pointerEvents = 'auto';
        }
    };

    window.resetPermissions = function() {
        if (confirm('Are you sure you want to reset all permissions? This will uncheck all permission boxes.')) {
            allPermissionsCheckbox.checked = false;
            permissionCheckboxes.forEach(cb => cb.checked = false);
            
            // Re-enable individual permissions
            individualPermissions.style.opacity = '1';
            individualPermissions.style.pointerEvents = 'auto';
        }
    };
});
</script>
{/block}
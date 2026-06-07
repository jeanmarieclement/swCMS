{* Admin User Edit Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Edit User{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/users">Users</a></li>
        <li class="breadcrumb-item active">Edit User</li>
    </ol>
</nav>
{/block}

{block name="content"}
<form method="post" action="{$admin_url}/users/edit/{$user.id}" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="{$csrf_token}">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit User: {$user.username}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{$admin_url}/users" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    {* Display messages *}
    {if isset($message) && $message}
        <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
            {$message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {elseif $success}
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> User updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {elseif $error}
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please correct the following errors:
            <div class="mt-2">{$error}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}

    {* User Info Card *}
    {include file="admin/users/partials/_user_info.tpl"}

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-edit me-1"></i>
                    User Details
                </div>
                <div class="card-body">
                    {include file="admin/users/partials/_user_form_fields.tpl"}
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-shield me-1"></i>
                    Role & Permissions
                </div>
                <div class="card-body">
                    {include file="admin/users/partials/_user_roles.tpl"}
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    User Statistics
                </div>
                <div class="card-body">
                    {if isset($user.created_at)}
                        <p class="mb-2"><strong>Created:</strong> {$user.created_at|date_format:"%B %e, %Y at %l:%M %p"}</p>
                    {/if}
                    {if isset($user.updated_at) && $user.updated_at != $user.created_at}
                        <p class="mb-2"><strong>Last modified:</strong> {$user.updated_at|date_format:"%B %e, %Y at %l:%M %p"}</p>
                    {/if}
                    {if isset($user.last_login)}
                        <p class="mb-2"><strong>Last login:</strong> 
                            {if $user.last_login}
                                {$user.last_login|date_format:"%B %e, %Y at %l:%M %p"}
                            {else}
                                <em class="text-muted">Never</em>
                            {/if}
                        </p>
                    {/if}
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-save me-1"></i>
                    Save Changes
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update User
                        </button>
                        <a href="{$admin_url}/users" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
{/block}

{block name="scripts"}
{include file="admin/users/partials/_user_modals.tpl"}
{include file="admin/users/partials/_user_scripts.tpl"}

<script>
    $(document).ready(function() {
        // Validate form before submit
        $('form.needs-validation').on('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    });
</script>
{/block}

{* Admin User Delete Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Delete User{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/users">Users</a></li>
        <li class="breadcrumb-item active">Delete User</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Delete User</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{$admin_url}/users" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

{* Display messages *}
{if $error}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Confirm User Deletion
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Warning!</strong><br>
            You are about to delete the user <strong>"{$user.username}"</strong> ({$user.email}).
            This action cannot be undone and the user will no longer be able to log in.
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-1"></i>
                User Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Username:</th>
                                <td>{$user.username}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{$user.email}</td>
                            </tr>
                            <tr>
                                <th>Display Name:</th>
                                <td>{$user.display_name}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Role:</th>
                                <td>
                                    <span class="badge {if $user.role == 'admin'}bg-danger{elseif $user.role == 'editor'}bg-warning{elseif $user.role == 'author'}bg-info{else}bg-secondary{/if}">
                                        {$user.role|capitalize}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge {if $user.status == 'active'}bg-success{else}bg-secondary{/if}">
                                        {$user.status|capitalize}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{$user.created_at|date_format:"%B %e, %Y"}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <form method="post" action="{$admin_url}/users/delete/{$user.id}">
            <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
            
            <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" value="1" required>
                <label class="form-check-label" for="confirm_delete">
                    <strong>I confirm that I want to delete this user permanently</strong>
                </label>
                <div class="form-text">This action cannot be undone.</div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{$admin_url}/users" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Delete User
                </button>
            </div>
        </form>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
    $(document).ready(function() {
        // Ensure checkbox is checked before allowing submit
        $('form').on('submit', function(e) {
            if (!$('#confirm_delete').is(':checked')) {
                e.preventDefault();
                alert('Please confirm that you want to delete this user by checking the confirmation box.');
                return false;
            }
            
            // Double confirmation for admin users
            {if $user.role == 'admin'}
            if (!confirm('You are about to delete an ADMIN user. This is a critical action. Are you absolutely sure?')) {
                e.preventDefault();
                return false;
            }
            {/if}
        });
    });
</script>
{/block}

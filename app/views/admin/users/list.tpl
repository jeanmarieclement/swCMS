{* Admin Users Index Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Users{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Users</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{$admin_url}/users/create" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Add New User
            </a>
        </div>
    </div>
</div>

{* Display messages *}
{if isset($message) && $message}
    <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.created) && $smarty.get.created}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> User created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.updated) && $smarty.get.updated}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> User updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.deleted) && $smarty.get.deleted}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> User deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> 
        {if $smarty.get.error == 'not_found'}
            User not found.
        {elseif $smarty.get.error == 'delete_failed'}
            Failed to delete user.
        {elseif $smarty.get.error == 'permission_denied'}
            You don't have permission to perform this action.
        {else}
            An error occurred.
        {/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $success}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {$success}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $error}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Users Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-1"></i>
        Filter Users
    </div>
    <div class="card-body">
        <form method="get" action="{$admin_url}/users" class="row g-3">
            <div class="col-md-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="all" {if isset($filter.role) && $filter.role == 'all'}selected{elseif !isset($filter.role)}selected{/if}>All Roles</option>
                    <option value="admin" {if isset($filter.role) && $filter.role == 'admin'}selected{/if}>Administrator</option>
                    <option value="editor" {if isset($filter.role) && $filter.role == 'editor'}selected{/if}>Editor</option>
                    <option value="author" {if isset($filter.role) && $filter.role == 'author'}selected{/if}>Author</option>
                    <option value="subscriber" {if isset($filter.role) && $filter.role == 'subscriber'}selected{/if}>Subscriber</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" {if isset($filter.status) && $filter.status == 'all'}selected{elseif !isset($filter.status)}selected{/if}>All Status</option>
                    <option value="active" {if isset($filter.status) && $filter.status == 'active'}selected{/if}>Active</option>
                    <option value="inactive" {if isset($filter.status) && $filter.status == 'inactive'}selected{/if}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{if isset($filter.search)}{$filter.search}{/if}" placeholder="Search by username, email, or name...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-users me-1"></i>
        Users List
    </div>
    <div class="card-body">
        {if $users}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="usersTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Display Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$users item=user}
                            {include file="admin/users/partials/_user_row.tpl" user=$user}
                        {/foreach}
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            {if $pagination && $pagination.total_pages > 1}
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        {foreach from=$pagination.pages item=page}
                            <li class="page-item {$page.class}">
                                <a class="page-link" href="{$page.url}">{$page.label}</a>
                            </li>
                        {/foreach}
                    </ul>
                </nav>
            {/if}
        {else}
            <div class="alert alert-info mb-0">
                No users found. <a href="{$admin_url}/users/create" class="alert-link">Create a new user</a>.
            </div>
        {/if}
    </div>
</div>
{/block}

{block name="scripts"}
<script>
    $(document).ready(function() {
        // Delete user confirmation is handled in the modal
        console.log('Users management loaded');
    });
</script>
{/block}

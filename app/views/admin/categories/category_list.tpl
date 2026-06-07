{* Admin Categories Index Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Categories{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Categories</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Categories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{$admin_url}/categories/create" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Add New Category
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
        <i class="fas fa-check-circle me-2"></i> Category created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.updated) && $smarty.get.updated}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Category updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.deleted) && $smarty.get.deleted}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Category deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> 
        {if $smarty.get.error == 'not_found'}
            Category not found.
        {elseif $smarty.get.error == 'assigned'}
            Category is assigned to articles and cannot be deleted.
        {elseif $smarty.get.error == 'delete_failed'}
            Failed to delete category.
        {else}
            An error occurred.
        {/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $success}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {if $success == 'created'}Category created successfully!{elseif $success == 'updated'}Category updated successfully!{elseif $success == 'deleted'}Category deleted successfully!{/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $error}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {if $error == 'notfound'}Category not found.{elseif $error == 'assigned'}Category is assigned to articles and cannot be deleted.{else}An error occurred.{/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Categories Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Categories List
    </div>
    <div class="card-body">
        {if $categories}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="categoriesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Articles Count</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $categories as $cat}
                            <tr>
                                <td>
                                    <a href="{$admin_url}/categories/edit/{$cat.id}" class="fw-bold">{$cat.name|escape}</a>
                                </td>
                                <td>{$cat.slug|escape}</td>
                                <td>
                                    {if isset($cat.description) && $cat.description}
                                        {$cat.description|truncate:50|escape}
                                    {else}
                                        <em class="text-muted">No description</em>
                                    {/if}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{$cat.post_count|default:0}</span>
                                </td>
                                <td>{$cat.created_at|date_format:"%b %e, %Y"}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{$admin_url}/categories/edit/{$cat.id}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-category" data-id="{$cat.id}" data-name="{$cat.name|escape}" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <div class="alert alert-info mb-0">
                No categories found. <a href="{$admin_url}/categories/create" class="alert-link">Create a new category</a>.
            </div>
        {/if}
    </div>
</div>
{/block}

{block name="scripts"}
<script>
    $(document).ready(function() {
        // Delete category confirmation
        $('.delete-category').on('click', function() {
            if (confirm('Are you sure you want to delete the category "' + $(this).data('name') + '"? This action cannot be undone.')) {
                var categoryId = $(this).data('id');
                window.location.href = '{$admin_url}/categories/delete/' + categoryId;
            }
        });
    });
</script>
{/block}

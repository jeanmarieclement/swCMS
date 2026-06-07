{* Admin Tags Index Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Tags{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Tags</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Tags</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{$admin_url}/tags/create" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Add New Tag
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
        <i class="fas fa-check-circle me-2"></i> Tag created successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.updated) && $smarty.get.updated}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Tag updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.deleted) && $smarty.get.deleted}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Tag deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> 
        {if $smarty.get.error == 'not_found'}
            Tag not found.
        {elseif $smarty.get.error == 'assigned'}
            Tag is assigned to articles and cannot be deleted.
        {elseif $smarty.get.error == 'delete_failed'}
            Failed to delete tag.
        {else}
            An error occurred.
        {/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $success}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {if $success == 'created'}Tag created successfully!{elseif $success == 'updated'}Tag updated successfully!{elseif $success == 'deleted'}Tag deleted successfully!{/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif $error}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {if $error == 'notfound'}Tag not found.{elseif $error == 'assigned'}Tag is assigned to articles and cannot be deleted.{else}An error occurred.{/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Tags Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-tags me-1"></i>
        Tags List
    </div>
    <div class="card-body">
        {if $tags}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tagsTable" width="100%" cellspacing="0">
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
                        {foreach $tags as $tag}
                            <tr>
                                <td>
                                    <a href="{$admin_url}/tags/edit?id={$tag.id}" class="fw-bold">{$tag.name|escape}</a>
                                </td>
                                <td>{$tag.slug|escape}</td>
                                <td>
                                    {if isset($tag.description) && $tag.description}
                                        {$tag.description|truncate:50|escape}
                                    {else}
                                        <em class="text-muted">No description</em>
                                    {/if}
                                </td>
                                <td>
                                    <span class="badge bg-primary">{$tag.post_count|default:0}</span>
                                </td>
                                <td>{$tag.created_at|date_format:"%b %e, %Y"}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{$admin_url}/tags/edit?id={$tag.id}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-tag" data-id="{$tag.id}" data-name="{$tag.name|escape}" title="Delete">
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
                No tags found. <a href="{$admin_url}/tags/create" class="alert-link">Create a new tag</a>.
            </div>
        {/if}
    </div>
</div>
{/block}

{block name="scripts"}
<script>
    $(document).ready(function() {
        // Delete tag confirmation
        $('.delete-tag').on('click', function() {
            if (confirm('Are you sure you want to delete the tag "' + $(this).data('name') + '"? This action cannot be undone.')) {
                // Create form and submit
                var form = $('<form method="POST" action="{$admin_url}/tags/delete"></form>');
                form.append('<input type="hidden" name="id" value="' + $(this).data('id') + '">');
                form.append('<input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">');
                $('body').append(form);
                form.submit();
            }
        });
    });
</script>
{/block}

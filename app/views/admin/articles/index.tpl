{* Admin Articles Index Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Articles{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Articles</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Articles</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{$admin_url}/articles/create" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus me-1"></i> Add New Article
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
{elseif isset($smarty.get.saved) && $smarty.get.saved}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Article saved successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.deleted) && $smarty.get.deleted}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Article deleted successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.status_changed) && $smarty.get.status_changed}
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> Article status updated successfully!
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{elseif isset($smarty.get.error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> 
        {if $smarty.get.error == 'not_found'}
            Article not found.
        {elseif $smarty.get.error == 'delete_failed'}
            Failed to delete article.
        {elseif $smarty.get.error == 'status_change_failed'}
            Failed to change article status.
        {else}
            An error occurred.
        {/if}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Articles Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-1"></i>
        Filter Articles
    </div>
    <div class="card-body">
        <form method="get" action="{$admin_url}/articles" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" {if $pagination.status == 'all'}selected{/if}>All</option>
                    <option value="published" {if $pagination.status == 'published'}selected{/if}>Published</option>
                    <option value="draft" {if $pagination.status == 'draft'}selected{/if}>Draft</option>
                    <option value="trash" {if $pagination.status == 'trash'}selected{/if}>Trash</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    {if isset($categories)}
                        {foreach from=$categories item=category}
                            <option value="{$category.id}" {if isset($selected_category) && $selected_category == $category.id}selected{/if}>{$category.name}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{$search|default:''}" placeholder="Search by title or content...">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Articles Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Articles List
    </div>
    <div class="card-body">
        {if $articles}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="articlesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $articles as $article}
                            <tr>
                                <td>
                                    <a href="{$admin_url}/articles/edit/{$article.id}" class="fw-bold">{$article.title|escape}</a>
                                </td>
                                <td>
                                    {if isset($article.display_name)}
                                        {$article.display_name|escape}
                                    {elseif isset($article.username)}
                                        {$article.username|escape}
                                    {else}
                                        Unknown
                                    {/if}
                                </td>
                                <td>
                                    {if isset($article.category_name)}
                                        {$article.category_name|escape}
                                    {else}
                                        Uncategorized
                                    {/if}
                                </td>
                                <td>
                                    <span class="badge bg-{if $article.status == 'published'}success{elseif $article.status == 'draft'}warning{else}danger{/if}">
                                        {$article.status|capitalize}
                                    </span>
                                </td>
                                <td>
                                    {if $article.status == 'published' && isset($article.published_at)}
                                        {$article.published_at|date_format:"%b %e, %Y"}
                                    {else}
                                        {$article.created_at|date_format:"%b %e, %Y"}
                                    {/if}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{$admin_url}/articles/edit/{$article.id}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="{$site_url}/{$article.slug}" target="_blank" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        {if $article.status != 'trash'}
                                            <button type="button" class="btn btn-sm btn-outline-danger trash-article" data-id="{$article.id}" title="Move to Trash">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {else}
                                            <button type="button" class="btn btn-sm btn-outline-success restore-article" data-id="{$article.id}" title="Restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-article" data-id="{$article.id}" title="Delete Permanently">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            {if $pagination.total_pages > 1}
                {pagination data=$pagination url="{$admin_url}/articles" status=$pagination.status}
            {/if}
        {else}
            <div class="alert alert-info mb-0">
                No articles found. {if $pagination.status != 'all'}Try changing the filter or {/if}<a href="{$admin_url}/articles/create" class="alert-link">create a new article</a>.
            </div>
        {/if}
    </div>
</div>
{/block}

{block name="scripts"}

<script>
    $(document).ready(function() {
     
        
        // Trash article
        $('.trash-article').on('click', function() {
            if (confirm('Are you sure you want to move this article to trash?')) {
                var articleId = $(this).data('id');
                window.location.href = '{$admin_url}/articles/status/' + articleId + '/trash';
            }
        });
        
        // Restore article
        $('.restore-article').on('click', function() {
            if (confirm('Are you sure you want to restore this article?')) {
                var articleId = $(this).data('id');
                window.location.href = '{$admin_url}/articles/status/' + articleId + '/draft';
            }
        });
        
        // Delete article permanently
        $('.delete-article').on('click', function() {
            if (confirm('Are you sure you want to permanently delete this article? This action cannot be undone!')) {
                var articleId = $(this).data('id');
                window.location.href = '{$admin_url}/articles/delete/' + articleId;
            }
        });
    });
</script>
{/block}

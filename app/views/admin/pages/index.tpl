{* Admin Pages Index Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Manage Pages{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Pages</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Pages</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{$admin_url}/pages/create" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Page
        </a>
    </div>
</div>

{* Display messages *}
{if isset($message) && $message}
    <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Pages Filter -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-1"></i>
        Filter Pages
    </div>
    <div class="card-body">
        <form method="get" action="{$admin_url}/pages" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="all" {if $pagination.status == 'all'}selected{/if}>All</option>
                    <option value="published" {if $pagination.status == 'published'}selected{/if}>Published</option>
                    <option value="draft" {if $pagination.status == 'draft'}selected{/if}>Draft</option>
                    <option value="trash" {if $pagination.status == 'trash'}selected{/if}>Trash</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" value="{$search|default:''}" placeholder="Search by title or content...">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Pages Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Pages List
    </div>
    <div class="card-body">
        {if $pages}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="pagesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th>Last Modified</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach $pages as $page}
                            <tr>
                                <td>
                                    <a href="{$admin_url}/pages/edit/{$page.id}" class="fw-bold">{$page.title}</a>
                                    {if $page.parent_id > 0}
                                        <span class="badge bg-secondary ms-1">Child</span>
                                    {/if}
                                </td>
                                <td>{$page.slug}</td>
                                <td>
                                    <span class="badge bg-{if $page.status == 'published'}success{elseif $page.status == 'draft'}warning{else}danger{/if}">
                                        {$page.status|capitalize}
                                    </span>
                                </td>
                                <td>{$page.created_at|date_format:"%B %e, %Y"}</td>
                                <td>{$page.updated_at|date_format:"%B %e, %Y"}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{$admin_url}/pages/edit/{$page.id}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{$site_url}/{$page.slug}" target="_blank" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {if $page.status != 'trash'}
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-page-btn" data-id="{$page.id}" data-title="{$page.title}" title="Move to Trash">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {else}
                                            <button type="button" class="btn btn-sm btn-outline-success restore-page" data-id="{$page.id}" title="Restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-page-btn" data-id="{$page.id}" data-title="{$page.title}" title="Delete Permanently">
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
                {pagination data=$pagination url="{$admin_url}/pages" status=$pagination.status}
            {/if}
        {else}
            <div class="alert alert-info mb-0">
                No pages found. {if $pagination.status != 'all'}Try changing the filter or {/if}<a href="{$admin_url}/pages/create" class="alert-link">create a new page</a>.
            </div>
        {/if}
    </div>
</div>

{* Include delete confirmation modal *}
{include file="admin/pages/partials/_modals.tpl"}
{/block}

{block name="scripts"}

<script>
    $(document).ready(function() {

        // Handle delete button clicks
        $('.delete-page-btn').on('click', function() {
            const pageId = $(this).data('id');
            const pageTitle = $(this).data('title');

            $('#confirmDeleteBtn').attr('href', '{$admin_url}/pages/delete/' + pageId);
            $('#deleteConfirmModal .modal-body').html('Are you sure you want to delete the page <strong>"' + pageTitle + '"</strong>? This action cannot be undone.');

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });

        // Handle restore button clicks
        $('.restore-page').on('click', function() {
            const pageId = $(this).data('id');

            // Create a form to submit the restore request
            const form = $('<form>', {
                'method': 'POST',
                'action': '{$admin_url}/pages/status'
            });

            // Add CSRF token
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'csrf_token',
                'value': '{App\Helpers\SecurityHelper::csrf_token()}'
            }));

            // Add page ID
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'id',
                'value': pageId
            }));

            // Add status (restore to draft)
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'status',
                'value': 'draft'
            }));

            // Append form to body and submit
            form.appendTo('body').submit();
        });
    });
</script>
{/block}
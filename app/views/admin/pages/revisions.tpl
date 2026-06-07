{extends file="admin/layout.tpl"}

{block name="title"}Page Revisions - {$page.title}{/block}

{block name="content"}
<div class="container-fluid px-4">
    <h1 class="mt-4">Revision History: {$page.title}</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{$admin_url}/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/edit/{$page.id}">Edit Page</a></li>
        <li class="breadcrumb-item active">Revisions</li>
    </ol>
    
    {if $message}
    <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    {/if}
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            Revision History
        </div>
        <div class="card-body">
            {if $revisions}
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Revision</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$revisions item=revision}
                            <tr>
                                <td>#{$revision.id}</td>
                                <td>{$revision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}</td>
                                <td>
                                    {if $revision.status == 'published'}
                                        <span class="badge bg-success">Published</span>
                                    {elseif $revision.status == 'draft'}
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    {elseif $revision.status == 'trash'}
                                        <span class="badge bg-danger">Trash</span>
                                    {/if}
                                </td>
                                <td>{$revision.revision_note}</td>
                                <td>
                                    <a href="{$admin_url}/pages/view-revision/{$page.id}/{$revision.id}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal{$revision.id}">
                                        <i class="fas fa-undo"></i> Restore
                                    </button>
                                    
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{$revision.id}">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                    
                                    <!-- Compare dropdown -->
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-exchange-alt"></i> Compare
                                        </button>
                                        <ul class="dropdown-menu">
                                            {foreach from=$revisions item=compareRevision}
                                                {if $compareRevision.id != $revision.id}
                                                    <li>
                                                        <a class="dropdown-item" href="{$admin_url}/pages/compare-revisions/{$page.id}/{$compareRevision.id}/{$revision.id}">
                                                            With #{$compareRevision.id} ({$compareRevision.created_at|date_format:"%Y-%m-%d %H:%M"})
                                                        </a>
                                                    </li>
                                                {/if}
                                            {/foreach}
                                        </ul>
                                    </div>
                                    
                                    <!-- Restore Modal -->
                                    <div class="modal fade" id="restoreModal{$revision.id}" tabindex="-1" aria-labelledby="restoreModalLabel{$revision.id}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="restoreModalLabel{$revision.id}">Restore Revision</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to restore the page to revision #{$revision.id} from {$revision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{$admin_url}/pages/restore-revision/{$page.id}/{$revision.id}" method="post">
                                                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                                        <button type="submit" class="btn btn-success">Restore</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal{$revision.id}" tabindex="-1" aria-labelledby="deleteModalLabel{$revision.id}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteModalLabel{$revision.id}">Delete Revision</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete revision #{$revision.id} from {$revision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}?
                                                    <br><br>
                                                    <strong>Warning:</strong> This action cannot be undone.
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{$admin_url}/pages/delete-revision/{$page.id}/{$revision.id}" method="post">
                                                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                
                <!-- Pagination -->
                {if $totalPages > 1}
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        {if $currentPage > 1}
                            <li class="page-item">
                                <a class="page-link" href="{$admin_url}/pages/revisions/{$page.id}?page={$currentPage-1}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        {else}
                            <li class="page-item disabled">
                                <span class="page-link">&laquo;</span>
                            </li>
                        {/if}
                        
                        {for $i=1 to $totalPages}
                            <li class="page-item {if $i == $currentPage}active{/if}">
                                <a class="page-link" href="{$admin_url}/pages/revisions/{$page.id}?page={$i}">{$i}</a>
                            </li>
                        {/for}
                        
                        {if $currentPage < $totalPages}
                            <li class="page-item">
                                <a class="page-link" href="{$admin_url}/pages/revisions/{$page.id}?page={$currentPage+1}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        {else}
                            <li class="page-item disabled">
                                <span class="page-link">&raquo;</span>
                            </li>
                        {/if}
                    </ul>
                </nav>
                {/if}
            {else}
                <div class="alert alert-info">
                    No revisions found for this page.
                </div>
            {/if}
        </div>
    </div>
    
    <div class="mb-4">
        <a href="{$admin_url}/pages/edit/{$page.id}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Back to Edit Page
        </a>
    </div>
</div>
{/block}

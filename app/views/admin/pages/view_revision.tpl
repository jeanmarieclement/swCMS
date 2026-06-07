{extends file="admin/layout.tpl"}

{block name="title"}View Revision - {$revision.title}{/block}

{block name="content"}
<div class="container-fluid px-4">
    <h1 class="mt-4">View Revision: #{$revision.id}</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{$admin_url}/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/edit/{$page.id}">Edit Page</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/revisions/{$page.id}">Revisions</a></li>
        <li class="breadcrumb-item active">View Revision</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Revision Details
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Revision ID:</div>
                <div class="col-md-9">#{$revision.id}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Created At:</div>
                <div class="col-md-9">{$revision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}</div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Status:</div>
                <div class="col-md-9">
                    {if $revision.status == 'published'}
                        <span class="badge bg-success">Published</span>
                    {elseif $revision.status == 'draft'}
                        <span class="badge bg-warning text-dark">Draft</span>
                    {elseif $revision.status == 'trash'}
                        <span class="badge bg-danger">Trash</span>
                    {/if}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 fw-bold">Revision Note:</div>
                <div class="col-md-9">{$revision.revision_note}</div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-heading me-1"></i>
            Title
        </div>
        <div class="card-body">
            <h2>{$revision.title}</h2>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-file-alt me-1"></i>
            Content
        </div>
        <div class="card-body">
            <div class="content-preview">
                {$revision.content}
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{$admin_url}/pages/revisions/{$page.id}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to Revisions
            </a>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal">
                <i class="fas fa-undo"></i> Restore This Revision
            </button>
        </div>
    </div>
    
    <!-- Restore Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreModalLabel">Restore Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore the page to this revision (#{$revision.id}) from {$revision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}?
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
</div>
{/block}

{block name="scripts"}
<script>
    // Apply styling to the content preview
    document.addEventListener('DOMContentLoaded', function() {
        // Add Bootstrap classes to tables in the content
        const tables = document.querySelectorAll('.content-preview table');
        tables.forEach(function(table) {
            table.classList.add('table', 'table-bordered');
        });
        
        // Add Bootstrap classes to images in the content
        const images = document.querySelectorAll('.content-preview img');
        images.forEach(function(img) {
            img.classList.add('img-fluid');
        });
    });
</script>
{/block}

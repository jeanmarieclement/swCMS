{extends file="admin/layout.tpl"}

{block name="title"}Compare Revisions - {$page.title}{/block}

{block name="head"}
<style>
    .diff-added {
        background-color: #e6ffed;
        text-decoration: none;
        color: #24292e;
    }
    .diff-removed {
        background-color: #ffeef0;
        text-decoration: line-through;
        color: #24292e;
    }
    .diff-table {
        width: 100%;
        border-collapse: collapse;
    }
    .diff-table td {
        padding: 8px;
        border: 1px solid #ddd;
        vertical-align: top;
    }
    .revision-meta {
        background-color: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 15px;
    }
</style>
{/block}

{block name="content"}
<div class="container-fluid px-4">
    <h1 class="mt-4">Compare Revisions</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{$admin_url}/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/edit/{$page.id}">Edit Page</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/revisions/{$page.id}">Revisions</a></li>
        <li class="breadcrumb-item active">Compare Revisions</li>
    </ol>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Older Revision (#{$oldRevision.id})
                </div>
                <div class="card-body">
                    <div class="revision-meta">
                        <div><strong>Date:</strong> {$oldRevision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}</div>
                        <div>
                            <strong>Status:</strong>
                            {if $oldRevision.status == 'published'}
                                <span class="badge bg-success">Published</span>
                            {elseif $oldRevision.status == 'draft'}
                                <span class="badge bg-warning text-dark">Draft</span>
                            {elseif $oldRevision.status == 'trash'}
                                <span class="badge bg-danger">Trash</span>
                            {/if}
                        </div>
                        <div><strong>Note:</strong> {$oldRevision.revision_note}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history me-1"></i>
                    Newer Revision (#{$newRevision.id})
                </div>
                <div class="card-body">
                    <div class="revision-meta">
                        <div><strong>Date:</strong> {$newRevision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}</div>
                        <div>
                            <strong>Status:</strong>
                            {if $newRevision.status == 'published'}
                                <span class="badge bg-success">Published</span>
                            {elseif $newRevision.status == 'draft'}
                                <span class="badge bg-warning text-dark">Draft</span>
                            {elseif $newRevision.status == 'trash'}
                                <span class="badge bg-danger">Trash</span>
                            {/if}
                        </div>
                        <div><strong>Note:</strong> {$newRevision.revision_note}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exchange-alt me-1"></i>
            Title Comparison
        </div>
        <div class="card-body">
            <table class="diff-table">
                <tr>
                    <td width="50%">
                        <h4>{$oldRevision.title}</h4>
                    </td>
                    <td width="50%">
                        <h4>{$newRevision.title}</h4>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exchange-alt me-1"></i>
            Content Comparison
        </div>
        <div class="card-body">
            <div id="content-diff">
                <table class="diff-table">
                    <tr>
                        <td width="50%">
                            <div class="old-content">{$oldRevision.content}</div>
                        </td>
                        <td width="50%">
                            <div class="new-content">{$newRevision.content}</div>
                        </td>
                    </tr>
                </table>
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
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreOldModal">
                <i class="fas fa-undo"></i> Restore Older Revision
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreNewModal">
                <i class="fas fa-undo"></i> Restore Newer Revision
            </button>
        </div>
    </div>
    
    <!-- Restore Old Revision Modal -->
    <div class="modal fade" id="restoreOldModal" tabindex="-1" aria-labelledby="restoreOldModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreOldModalLabel">Restore Older Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore the page to revision #{$oldRevision.id} from {$oldRevision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{$admin_url}/pages/restore-revision/{$page.id}/{$oldRevision.id}" method="post">
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        <button type="submit" class="btn btn-success">Restore</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Restore New Revision Modal -->
    <div class="modal fade" id="restoreNewModal" tabindex="-1" aria-labelledby="restoreNewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreNewModalLabel">Restore Newer Revision</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore the page to revision #{$newRevision.id} from {$newRevision.created_at|date_format:"%Y-%m-%d %H:%M:%S"}?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{$admin_url}/pages/restore-revision/{$page.id}/{$newRevision.id}" method="post">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/diff/5.1.0/diff.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to create a visual diff between two texts
    function createTextDiff(oldText, newText, outputElement) {
        // Create a diff object
        const diff = Diff.diffWords(oldText, newText);
        
        // Clear the output element
        outputElement.innerHTML = '';
        
        // Create the diff HTML
        diff.forEach(function(part) {
            const span = document.createElement('span');
            span.textContent = part.value;
            
            if (part.added) {
                span.className = 'diff-added';
            } else if (part.removed) {
                span.className = 'diff-removed';
            }
            
            outputElement.appendChild(span);
        });
    }
    
    // Get the old and new content
    const oldTitle = document.querySelector('.diff-table tr:first-child td:first-child h4').textContent;
    const newTitle = document.querySelector('.diff-table tr:first-child td:last-child h4').textContent;
    const oldContent = document.querySelector('.old-content').innerHTML;
    const newContent = document.querySelector('.new-content').innerHTML;
    
    // Create a container for the title diff
    const titleDiffContainer = document.createElement('div');
    document.querySelector('.diff-table tr:first-child td:first-child').innerHTML = '';
    document.querySelector('.diff-table tr:first-child td:first-child').appendChild(titleDiffContainer);
    
    // Create the title diff
    createTextDiff(oldTitle, newTitle, titleDiffContainer);
    
    // Create a container for the content diff
    const contentDiffContainer = document.createElement('div');
    document.querySelector('#content-diff .diff-table tr:first-child td:first-child').innerHTML = '';
    document.querySelector('#content-diff .diff-table tr:first-child td:first-child').appendChild(contentDiffContainer);
    
    // Create the content diff (this might be complex for HTML content)
    try {
        createTextDiff(oldContent, newContent, contentDiffContainer);
    } catch (e) {
        console.error('Error creating content diff:', e);
        contentDiffContainer.innerHTML = '<div class="alert alert-warning">Unable to generate diff for complex HTML content. Please compare manually.</div>';
    }
    
    // Hide the new content column as we're showing the diff in the left column
    document.querySelector('#content-diff .diff-table tr:first-child td:last-child').style.display = 'none';
    document.querySelector('.diff-table tr:first-child td:last-child').style.display = 'none';
    
    // Make the diff column take full width
    document.querySelector('#content-diff .diff-table tr:first-child td:first-child').style.width = '100%';
    document.querySelector('.diff-table tr:first-child td:first-child').style.width = '100%';
});
</script>
{/block}

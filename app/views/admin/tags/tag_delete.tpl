{* Admin Tag Delete Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Delete Tag{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/tags">Tags</a></li>
        <li class="breadcrumb-item active">Delete Tag</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Delete Tag</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{$admin_url}/tags" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Tags
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <i class="fas fa-exclamation-triangle me-1"></i>
        Confirm Deletion
    </div>
    <div class="card-body">
        {if $assigned}
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <strong>Cannot Delete Tag</strong><br>
                The tag <strong>"{$tag.name|escape}"</strong> is assigned to one or more articles and cannot be deleted. 
                Please remove the tag from all articles before attempting to delete it.
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{$admin_url}/tags" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Tags
                </a>
            </div>
        {else}
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning!</strong><br>
                Are you sure you want to delete the tag <strong>"{$tag.name|escape}"</strong>?
                This action cannot be undone.
            </div>
            
            <form method="post" action="{$admin_url}/tags/delete">
                <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
                <input type="hidden" name="id" value="{$tag.id}">
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{$admin_url}/tags" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Tag
                    </button>
                </div>
            </form>
        {/if}
    </div>
</div>
{/block}

{* Admin Page Delete Confirmation Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Delete Page{/block}

{block name="content"}
<div class="container-fluid px-4">
    <h1 class="mt-4">Delete Page</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{$admin_url}/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item active">Delete Page</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Warning: You are about to delete a page
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <h4 class="alert-heading">Are you sure you want to delete this page?</h4>
                <p>You are about to delete the page "<strong>{$page.title}</strong>". This action cannot be undone.</p>
                
                {if $children}
                    <hr>
                    <p class="mb-0"><strong>Warning:</strong> This page has {$children|@count} child pages that will be affected:</p>
                    <ul>
                        {foreach $children as $child}
                            <li>{$child.title}</li>
                        {/foreach}
                    </ul>
                    <p>If you delete this page, all child pages will have their parent set to none.</p>
                {/if}
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header">Page Details</div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Title:</th>
                                    <td>{$page.title}</td>
                                </tr>
                                <tr>
                                    <th>Slug:</th>
                                    <td>{$page.slug}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{if $page.status == 'published'}success{elseif $page.status == 'draft'}warning{else}secondary{/if}">
                                            {$page.status|capitalize}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{$page.created_at|date_format:"%Y-%m-%d %H:%M:%S"}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <form action="{$formAction}" method="post">
                        {* CSRF Token *}
                        <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-trash-alt me-1"></i> Confirm Deletion
                            </button>
                            <a href="{$admin_url}/pages" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

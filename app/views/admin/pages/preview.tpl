{* Admin Page Preview Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Preview: {$page.title}{/block}

{block name="content"}
<div class="container-fluid px-4">
    <h1 class="mt-4">Page Preview</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{$admin_url}/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages/edit/{$page.id}">Edit Page</a></li>
        <li class="breadcrumb-item active">Preview</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-eye me-1"></i>
                Preview: {$page.title}
            </div>
            <div>
                <span class="badge bg-{if $page.status == 'published'}success{elseif $page.status == 'draft'}warning{else}secondary{/if} me-2">
                    {$page.status|capitalize}
                </span>
                <a href="{$admin_url}/pages/edit/{$page.id}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit Page
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="preview-container">
                <div class="preview-header mb-4">
                    <h1 class="preview-title">{$page.title}</h1>
                    {if $page.created_at}
                        <div class="preview-meta text-muted">
                            <small>Created on {$page.created_at|date_format:"%B %e, %Y"}</small>
                        </div>
                    {/if}
                </div>
                
                <div class="preview-content">
                    {$page.content}
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="row">
                <div class="col-md-6">
                    <strong>Permalink:</strong> <a href="{$site_url}/{$page.slug}" target="_blank">{$site_url}/{$page.slug}</a>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Template:</strong> {$page.template|default:'default'|capitalize}
                </div>
            </div>
        </div>
    </div>
    
    <div class="d-flex justify-content-between mb-4">
        <a href="{$admin_url}/pages/edit/{$page.id}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Page
        </a>
        {if $page.status != 'published'}
            <form method="POST" action="{$admin_url}/pages/status" style="display:inline;">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <input type="hidden" name="id" value="{$page.id}">
                <input type="hidden" name="status" value="published">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check me-1"></i> Publish Page
                </button>
            </form>
        {/if}
    </div>
</div>
{/block}

{block name="styles"}
<style>
    .preview-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        background-color: #fff;
    }
    
    .preview-title {
        margin-bottom: 0.5rem;
        font-size: 2.5rem;
    }
    
    .preview-content {
        font-family: 'Georgia', serif;
        font-size: 1.1rem;
        line-height: 1.6;
    }
    
    .preview-content img {
        max-width: 100%;
        height: auto;
        margin: 1rem 0;
    }
    
    .preview-content h1, .preview-content h2, .preview-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .preview-content p {
        margin-bottom: 1rem;
    }
    
    .preview-content blockquote {
        border-left: 4px solid #e0e0e0;
        padding-left: 1rem;
        margin-left: 0;
        color: #666;
    }
</style>
{/block}

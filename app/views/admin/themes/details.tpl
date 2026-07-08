{* Theme Details Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Theme Details - {$theme.display_name}{/block}

{block name="head"}
<style>
    .theme-preview {
        max-height: 300px;
        object-fit: cover;
        background: #f8f9fa;
    }
    .file-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background: #f8f9fa;
    }
    .theme-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .theme-active-badge {
        background: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .code-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        white-space: pre-wrap;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/themes">Themes</a></li>
        <li class="breadcrumb-item active">{$theme.display_name}</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">{$theme.display_name}</h1>
        {if $theme.name == $active_theme}
            <span class="theme-active-badge">
                <i class="fas fa-check-circle me-1"></i> Active Theme
            </span>
        {/if}
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/themes" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Themes
            </a>
        </div>
        {if $theme.name != $active_theme}
            <form method="POST" action="/admin/themes/activate" style="display: inline;">
                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                <input type="hidden" name="theme" value="{$theme.name}">
                <button type="submit" class="btn btn-sm btn-success" 
                        onclick="return confirm('Are you sure you want to activate this theme?')">
                    <i class="fas fa-check"></i> Activate Theme
                </button>
            </form>
        {/if}
    </div>
</div>

{* Flash Messages *}
{if isset($flash) && $flash}
    <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
        {$flash.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="row">
    {* Theme Preview *}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Theme Preview</h5>
            </div>
            <div class="card-body p-0">
                {if $theme.screenshot}
                    <img src="{$theme.screenshot}" alt="{$theme.display_name} Screenshot" 
                         class="img-fluid w-100 theme-preview">
                {else}
                    <div class="text-center py-5 bg-light">
                        <i class="fas fa-image fa-4x mb-3 text-muted"></i>
                        <h5>No Preview Available</h5>
                        <p class="text-muted">This theme doesn't have a screenshot.</p>
                    </div>
                {/if}
            </div>
        </div>

        {* Theme Templates *}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-code me-2"></i>Template Files ({$theme.templates|@count})</h5>
            </div>
            <div class="card-body">
                {if $theme.templates|@count > 0}
                    <div class="file-list">
                        {foreach from=$theme.templates item=template}
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-monospace">
                                    <i class="fas fa-file-alt me-2"></i>{$template}
                                </span>
                                <span class="badge bg-secondary">TPL</span>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <div class="text-muted">No template files found.</div>
                {/if}
            </div>
        </div>

        {* Theme Assets *}
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-palette me-2"></i>CSS Files ({$theme.assets.css|@count})</h6>
                    </div>
                    <div class="card-body">
                        {if $theme.assets.css|@count > 0}
                            {foreach from=$theme.assets.css item=css}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="text-monospace small">
                                        <i class="fas fa-file me-2"></i>{$css}
                                    </span>
                                    <span class="badge bg-primary">CSS</span>
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-muted small">No CSS files found.</div>
                        {/if}
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-code me-2"></i>JavaScript Files ({$theme.assets.js|@count})</h6>
                    </div>
                    <div class="card-body">
                        {if $theme.assets.js|@count > 0}
                            {foreach from=$theme.assets.js item=js}
                                <div class="d-flex justify-content-between align-items-center py-1">
                                    <span class="text-monospace small">
                                        <i class="fas fa-file me-2"></i>{$js}
                                    </span>
                                    <span class="badge bg-warning">JS</span>
                                </div>
                            {/foreach}
                        {else}
                            <div class="text-muted small">No JavaScript files found.</div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* Theme Information Sidebar *}
    <div class="col-lg-4">
        <div class="card mb-4 theme-info-card">
            <div class="card-body text-center">
                <h4 class="card-title">{$theme.display_name}</h4>
                <p class="card-text">{$theme.description}</p>
                <hr class="bg-white">
                <div class="row text-center">
                    <div class="col-6">
                        <h6>Version</h6>
                        <p class="mb-0">{$theme.version}</p>
                    </div>
                    <div class="col-6">
                        <h6>Author</h6>
                        <p class="mb-0">{$theme.author}</p>
                    </div>
                </div>
            </div>
        </div>

        {* Theme Statistics *}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Theme Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{$theme.templates|@count}</h4>
                            <small>Templates</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-success">{$theme.assets.css|@count + $theme.assets.js|@count}</h4>
                            <small>Assets</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between">
                        <span>CSS Files:</span>
                        <span class="fw-bold">{$theme.assets.css|@count}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>JS Files:</span>
                        <span class="fw-bold">{$theme.assets.js|@count}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Theme Directory:</span>
                        <code class="small">{$theme.name}</code>
                    </div>
                </div>
            </div>
        </div>

        {* Theme Actions *}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Theme Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    {if $theme.name != $active_theme}
                        <form method="POST" action="/admin/themes/activate">
                            <input type="hidden" name="csrf_token" value="{$csrf_token}">
                            <input type="hidden" name="theme" value="{$theme.name}">
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Are you sure you want to activate this theme?')">
                                <i class="fas fa-check-circle me-2"></i>Activate Theme
                            </button>
                        </form>
                    {else}
                        <button class="btn btn-success w-100" disabled>
                            <i class="fas fa-check-circle me-2"></i>Currently Active
                        </button>
                    {/if}
                    
                    <button class="btn btn-outline-primary w-100" onclick="previewTheme()">
                        <i class="fas fa-external-link-alt me-2"></i>Preview Site
                    </button>
                    
                    {if $theme.name != 'default'}
                        <button class="btn btn-outline-secondary w-100" onclick="exportTheme()">
                            <i class="fas fa-download me-2"></i>Export Theme
                        </button>
                        
                        {if $theme.name != $active_theme}
                            <button class="btn btn-outline-danger w-100" onclick="deleteTheme()">
                                <i class="fas fa-trash me-2"></i>Delete Theme
                            </button>
                        {/if}
                    {/if}
                </div>
            </div>
        </div>

        {* Theme Support *}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p>Having issues with this theme?</p>
                    <ul class="mb-0">
                        <li>Check the theme documentation</li>
                        <li>Visit the theme author's website</li>
                        <li>Review the template files for errors</li>
                        <li>Contact support if available</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.previewTheme = function() {
        // Open site in new tab for preview
        window.open('/', '_blank');
    };

    window.exportTheme = function() {
        alert('Theme export functionality will be available in a future version.');
    };

    window.deleteTheme = function() {
        if (confirm('Are you sure you want to delete this theme? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/themes/delete';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'theme';
            input.value = '{$theme.name}';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    };
});
</script>
{/block}
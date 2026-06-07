{* Themes Management Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Themes{/block}

{block name="head"}
<style>
    .theme-card {
        transition: transform 0.2s ease-in-out;
    }
    .theme-card:hover {
        transform: translateY(-2px);
    }
    .theme-screenshot {
        height: 200px;
        object-fit: cover;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
    }
    .theme-active {
        border: 2px solid #28a745;
    }
    .theme-active .card-header {
        background-color: #28a745;
        color: white;
    }
    .theme-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .theme-actions {
        min-height: 50px;
        display: flex;
        align-items: center;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item active">Themes</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Themes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='/admin/themes/install'">
            <i class="fas fa-plus"></i> Install Theme
        </button>
    </div>
</div>

{* Flash Messages *}
{if isset($flash) && $flash}
    <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
        {$flash.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

{* Current Active Theme Info *}
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Current Active Theme:</strong> {$active_theme|default:'default'}
</div>

{* Themes Grid *}
<div class="row">
    {if $themes|@count > 0}
        {foreach from=$themes item=theme}
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 theme-card {if $theme.name == $active_theme}theme-active{/if}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{$theme.display_name}</h5>
                        {if $theme.name == $active_theme}
                            <span class="badge bg-light text-dark">Active</span>
                        {/if}
                    </div>
                    
                    {* Theme Screenshot *}
                    <div class="theme-screenshot">
                        {if $theme.screenshot}
                            <img src="{$theme.screenshot}" alt="{$theme.display_name} Screenshot" 
                                 class="img-fluid w-100 h-100" style="object-fit: cover;">
                        {else}
                            <div class="text-center">
                                <i class="fas fa-image fa-3x mb-2"></i>
                                <div>No Preview Available</div>
                            </div>
                        {/if}
                    </div>
                    
                    <div class="card-body">
                        <p class="card-text">{$theme.description}</p>
                        
                        <div class="theme-info mb-3">
                            <div><strong>Version:</strong> {$theme.version}</div>
                            <div><strong>Author:</strong> {$theme.author}</div>
                            <div><strong>Templates:</strong> {$theme.templates|@count}</div>
                        </div>
                        
                        <div class="theme-actions">
                            {if $theme.name != $active_theme}
                                <form method="POST" action="/admin/themes/activate" style="display: inline;">
                                    <input type="hidden" name="theme" value="{$theme.name}">
                                    <button type="submit" class="btn btn-success btn-sm me-2" 
                                            onclick="return confirm('Are you sure you want to activate this theme?')">
                                        <i class="fas fa-check"></i> Activate
                                    </button>
                                </form>
                            {/if}
                            
                            <a href="/admin/themes/details?theme={$theme.name}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                            
                            {if $theme.name != 'default' && $theme.name != $active_theme}
                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                        onclick="confirmDelete('{$theme.name}')" title="Delete Theme">
                                    <i class="fas fa-trash"></i>
                                </button>
                            {/if}
                        </div>
                    </div>
                    
                    {* Footer with additional info *}
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-palette me-1"></i> CSS: {$theme.assets.css|@count} files
                            <i class="fas fa-code ms-2 me-1"></i> JS: {$theme.assets.js|@count} files
                        </small>
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-palette fa-3x mb-3 text-muted"></i>
                    <h5>No Themes Found</h5>
                    <p class="text-muted">No themes are currently installed on your system.</p>
                    <a href="/admin/themes/install" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Install Your First Theme
                    </a>
                </div>
            </div>
        </div>
    {/if}
</div>

{* Help Info *}
<div class="row mt-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-question-circle me-2"></i>Theme Management Help</h6>
                <div class="row">
                    <div class="col-md-4">
                        <h6>Installing Themes</h6>
                        <ul class="small">
                            <li>Upload theme files to <code>/public/themes/</code></li>
                            <li>Each theme needs a <code>templates/</code> directory</li>
                            <li>Include <code>home.tpl</code> or <code>layout.tpl</code> as minimum</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Theme Structure</h6>
                        <ul class="small">
                            <li><code>templates/</code> - Smarty template files</li>
                            <li><code>css/</code> - Stylesheets</li>
                            <li><code>js/</code> - JavaScript files</li>
                            <li><code>theme.conf.php</code> - Configuration (optional)</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Customization</h6>
                        <ul class="small">
                            <li>The 'default' theme serves as fallback</li>
                            <li>Override specific templates in your active theme</li>
                            <li>Add custom CSS and JS assets</li>
                            <li>Create child themes for advanced customization</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme activation confirmation
    window.confirmDelete = function(themeName) {
        if (confirm('Are you sure you want to delete the "' + themeName + '" theme? This action cannot be undone.')) {
            // Create a form to submit the delete request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/themes/delete';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'theme';
            input.value = themeName;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    };
});
</script>
{/block}
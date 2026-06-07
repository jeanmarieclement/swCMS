{* Plugins Management Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Plugins{/block}

{block name="head"}
<style>
    .plugin-card {
        transition: transform 0.2s ease-in-out;
    }
    .plugin-card:hover {
        transform: translateY(-2px);
    }
    .plugin-active {
        border: 2px solid #28a745;
    }
    .plugin-active .card-header {
        background-color: #28a745;
        color: white;
    }
    .plugin-inactive {
        border: 2px solid #dc3545;
    }
    .plugin-inactive .card-header {
        background-color: #dc3545;
        color: white;
    }
    .plugin-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .plugin-actions {
        min-height: 50px;
        display: flex;
        align-items: center;
    }
    .plugin-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2rem;
        margin-right: 1rem;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item active">Plugins</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Plugins</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='/admin/plugins/generate'">
                <i class="fas fa-magic"></i> Generate Plugin
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.href='/admin/plugins/install'">
                <i class="fas fa-plus"></i> Install Plugin
            </button>
        </div>
    </div>
</div>

{* Flash Messages *}
{if isset($flash) && $flash}
    <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
        {$flash.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

{* Active Plugins Summary *}
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Active Plugins:</strong> {$active_plugins|@count} of {$plugins|@count} plugins are currently active
</div>

{* Plugins Grid *}
<div class="row">
    {if $plugins|@count > 0}
        {foreach from=$plugins item=plugin}
            {assign var="is_active" value=false}
            {foreach from=$active_plugins item=active_plugin}
                {if $active_plugin == $plugin.name}
                    {assign var="is_active" value=true}
                    {break}
                {/if}
            {/foreach}
            
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card h-100 plugin-card {if $is_active}plugin-active{else}plugin-inactive{/if}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{$plugin.display_name}</h5>
                        {if $is_active}
                            <span class="badge bg-light text-dark">Active</span>
                        {else}
                            <span class="badge bg-light text-dark">Inactive</span>
                        {/if}
                    </div>
                    
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="plugin-icon">
                                <i class="fas fa-puzzle-piece"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="card-text">{$plugin.description}</p>
                                <div class="plugin-info">
                                    <div><strong>Version:</strong> {$plugin.version}</div>
                                    <div><strong>Author:</strong> {$plugin.author}</div>
                                    {if $plugin.requires}
                                        <div><strong>Requires:</strong> {$plugin.requires}</div>
                                    {/if}
                                    {if $plugin.requires_php}
                                        <div><strong>PHP:</strong> {$plugin.requires_php}+</div>
                                    {/if}
                                    {if $plugin.depends}
                                        <div><strong>Dependencies:</strong> 
                                            {foreach from=$plugin.depends item=dep name=deps}
                                                <span class="badge bg-info">{$dep}</span>{if !$smarty.foreach.deps.last} {/if}
                                            {/foreach}
                                        </div>
                                    {/if}
                                    {if $plugin.conflicts}
                                        <div><strong>Conflicts:</strong> 
                                            {foreach from=$plugin.conflicts item=conflict name=conflicts}
                                                <span class="badge bg-warning">{$conflict}</span>{if !$smarty.foreach.conflicts.last} {/if}
                                            {/foreach}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        
                        <div class="plugin-actions">
                            {if $is_active}
                                <form method="POST" action="/admin/plugins/deactivate" style="display: inline;">
                                    <input type="hidden" name="plugin" value="{$plugin.name}">
                                    <button type="submit" class="btn btn-danger btn-sm me-2" 
                                            onclick="return confirm('Are you sure you want to deactivate this plugin?')">
                                        <i class="fas fa-stop"></i> Deactivate
                                    </button>
                                </form>
                                
                                {if $plugin.has_settings}
                                    <a href="/admin/plugins/configure?plugin={$plugin.name}" class="btn btn-outline-secondary btn-sm me-2">
                                        <i class="fas fa-cogs"></i> Settings
                                    </a>
                                {/if}
                            {else}
                                <form method="POST" action="/admin/plugins/activate" style="display: inline;">
                                    <input type="hidden" name="plugin" value="{$plugin.name}">
                                    <button type="submit" class="btn btn-success btn-sm me-2" 
                                            onclick="return confirm('Are you sure you want to activate this plugin?')">
                                        <i class="fas fa-play"></i> Activate
                                    </button>
                                </form>
                            {/if}
                            
                            <a href="/admin/plugins/details?plugin={$plugin.name}" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                            
                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                    onclick="confirmDelete('{$plugin.name}')" title="Delete Plugin">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    {* Footer with additional info *}
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-file me-1"></i> Files: {$plugin.files|@count}
                            {if $plugin.has_hooks}
                                <i class="fas fa-link ms-2 me-1"></i> Hooks
                            {/if}
                            {if $plugin.has_settings}
                                <i class="fas fa-cogs ms-2 me-1"></i> Configurable
                            {/if}
                        </small>
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-puzzle-piece fa-3x mb-3 text-muted"></i>
                    <h5>No Plugins Found</h5>
                    <p class="text-muted">No plugins are currently installed on your system.</p>
                    <a href="/admin/plugins/install" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Install Your First Plugin
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
                <h6 class="card-title"><i class="fas fa-question-circle me-2"></i>Plugin Management Help</h6>
                <div class="row">
                    <div class="col-md-4">
                        <h6>Installing Plugins</h6>
                        <ul class="small">
                            <li>Upload plugin files to <code>/plugins/</code></li>
                            <li>Each plugin needs a main file: <code>plugin-name.php</code></li>
                            <li>Include plugin header with metadata</li>
                            <li>Optional: <code>settings.php</code> and <code>hooks.php</code></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Plugin Structure</h6>
                        <ul class="small">
                            <li><code>plugin-name.php</code> - Main plugin file</li>
                            <li><code>settings.php</code> - Configuration interface</li>
                            <li><code>hooks.php</code> - Hook definitions</li>
                            <li><code>assets/</code> - CSS, JS, images</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Development</h6>
                        <ul class="small">
                            <li>Use plugin hooks for customization</li>
                            <li>Follow standard plugin structure conventions</li>
                            <li>Store settings in system settings</li>
                            <li>Test plugins before activation</li>
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
    // Plugin deletion confirmation
    window.confirmDelete = function(pluginName) {
        if (confirm('Are you sure you want to delete the "' + pluginName + '" plugin? This action cannot be undone.')) {
            // Create a form to submit the delete request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/plugins/delete';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'plugin';
            input.value = pluginName;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    };
});
</script>
{/block}
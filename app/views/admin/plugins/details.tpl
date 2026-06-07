{* Plugin Details Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Plugin Details - {$plugin.display_name}{/block}

{block name="head"}
<style>
    .plugin-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    .plugin-active-badge {
        background: #28a745;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .plugin-inactive-badge {
        background: #dc3545;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .file-list {
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background: #f8f9fa;
    }
    .code-preview {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
        white-space: pre-wrap;
        max-height: 300px;
        overflow-y: auto;
    }
    .plugin-icon-large {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        margin: 0 auto 1rem;
    }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/admin/plugins">Plugins</a></li>
        <li class="breadcrumb-item active">{$plugin.display_name}</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">{$plugin.display_name}</h1>
        {if $is_active}
            <span class="plugin-active-badge">
                <i class="fas fa-check-circle me-1"></i> Active Plugin
            </span>
        {else}
            <span class="plugin-inactive-badge">
                <i class="fas fa-times-circle me-1"></i> Inactive Plugin
            </span>
        {/if}
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/plugins" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Plugins
            </a>
        </div>
        {if $is_active}
            <form method="POST" action="/admin/plugins/deactivate" style="display: inline;">
                <input type="hidden" name="plugin" value="{$plugin.name}">
                <button type="submit" class="btn btn-sm btn-danger" 
                        onclick="return confirm('Are you sure you want to deactivate this plugin?')">
                    <i class="fas fa-stop"></i> Deactivate Plugin
                </button>
            </form>
        {else}
            <form method="POST" action="/admin/plugins/activate" style="display: inline;">
                <input type="hidden" name="plugin" value="{$plugin.name}">
                <button type="submit" class="btn btn-sm btn-success" 
                        onclick="return confirm('Are you sure you want to activate this plugin?')">
                    <i class="fas fa-play"></i> Activate Plugin
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
    {* Plugin Files *}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-code me-2"></i>Plugin Files ({$plugin.files|@count})</h5>
            </div>
            <div class="card-body">
                {if $plugin.files|@count > 0}
                    <div class="file-list">
                        {foreach from=$plugin.files item=file}
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-monospace">
                                    <i class="fas fa-file me-2"></i>{$file.name}
                                    {if $file.name == "{$plugin.name}.php"}
                                        <span class="badge bg-primary ms-2">Main</span>
                                    {/if}
                                    {if $file.name == "settings.php"}
                                        <span class="badge bg-info ms-2">Settings</span>
                                    {/if}
                                    {if $file.name == "hooks.php"}
                                        <span class="badge bg-warning ms-2">Hooks</span>
                                    {/if}
                                </span>
                                <span class="badge bg-secondary">
                                    {$file.extension}
                                </span>
                            </div>
                        {/foreach}
                    </div>
                {else}
                    <div class="text-muted">No files found.</div>
                {/if}
            </div>
        </div>

        {* Plugin Compatibility Check *}
        {if $compatibility}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Compatibility Check
                        {if $compatibility.compatible}
                            <span class="badge bg-success">Compatible</span>
                        {else}
                            <span class="badge bg-danger">Issues Found</span>
                        {/if}
                    </h5>
                </div>
                <div class="card-body">
                    {if $compatibility.errors}
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-times-circle me-2"></i>Compatibility Errors</h6>
                            <ul class="mb-0">
                                {foreach from=$compatibility.errors item=error}
                                    <li>{$error}</li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    
                    {if $compatibility.warnings}
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Compatibility Warnings</h6>
                            <ul class="mb-0">
                                {foreach from=$compatibility.warnings item=warning}
                                    <li>{$warning}</li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    
                    <div class="row">
                        <div class="col-md-4">
                            <h6>System Requirements</h6>
                            <ul class="list-unstyled">
                                <li>
                                    {if $compatibility.requirements.system == 'OK'}
                                        <i class="fas fa-check text-success me-2"></i>
                                    {else}
                                        <i class="fas fa-times text-danger me-2"></i>
                                    {/if}
                                    System: {$compatibility.requirements.system}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Dependencies</h6>
                            <ul class="list-unstyled">
                                <li>
                                    {if $compatibility.requirements.dependencies == 'OK'}
                                        <i class="fas fa-check text-success me-2"></i>
                                    {else}
                                        <i class="fas fa-times text-danger me-2"></i>
                                    {/if}
                                    Dependencies: {$compatibility.requirements.dependencies}
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6>Conflicts</h6>
                            <ul class="list-unstyled">
                                <li>
                                    {if $compatibility.requirements.conflicts == 'OK'}
                                        <i class="fas fa-check text-success me-2"></i>
                                    {else}
                                        <i class="fas fa-times text-danger me-2"></i>
                                    {/if}
                                    Conflicts: {$compatibility.requirements.conflicts}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {* Plugin Dependencies *}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Dependencies & Relationships</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Plugin Dependencies</h6>
                        {if $plugin.depends}
                            <ul class="list-unstyled">
                                {foreach from=$plugin.depends item=dep}
                                    <li>
                                        <i class="fas fa-link me-2 text-primary"></i>
                                        <span class="badge bg-info">{$dep}</span>
                                    </li>
                                {/foreach}
                            </ul>
                        {else}
                            <p class="text-muted">No dependencies required</p>
                        {/if}
                    </div>
                    <div class="col-md-6">
                        <h6>Dependent Plugins</h6>
                        {if $dependents}
                            <ul class="list-unstyled">
                                {foreach from=$dependents item=dependent}
                                    <li>
                                        <i class="fas fa-arrow-left me-2 text-secondary"></i>
                                        <span class="badge bg-secondary">{$dependent}</span>
                                    </li>
                                {/foreach}
                            </ul>
                        {else}
                            <p class="text-muted">No plugins depend on this</p>
                        {/if}
                    </div>
                </div>
                
                {if $plugin.conflicts}
                    <hr>
                    <h6>Plugin Conflicts</h6>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This plugin conflicts with:
                        {foreach from=$plugin.conflicts item=conflict name=conflicts}
                            <span class="badge bg-warning text-dark">{$conflict}</span>{if !$smarty.foreach.conflicts.last}, {/if}
                        {/foreach}
                    </div>
                {/if}
            </div>
        </div>

        {* Plugin Requirements *}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>System Requirements</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Version Requirements</h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i>CMS Version: {$plugin.requires|default:'Any'}</li>
                            {if $plugin.requires_php}
                                <li><i class="fas fa-check text-success me-2"></i>PHP Version: {$plugin.requires_php}+</li>
                            {/if}
                            <li><i class="fas fa-check text-success me-2"></i>Database: MySQL/SQLite</li>
                            {if $plugin.tested_up_to}
                                <li><i class="fas fa-info text-info me-2"></i>Tested up to: {$plugin.tested_up_to}</li>
                            {/if}
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Plugin Features</h6>
                        <ul class="list-unstyled">
                            <li>
                                {if $plugin.has_settings}
                                    <i class="fas fa-check text-success me-2"></i>Configurable Settings
                                {else}
                                    <i class="fas fa-times text-muted me-2"></i>No Settings Available
                                {/if}
                            </li>
                            <li>
                                {if $plugin.has_hooks}
                                    <i class="fas fa-check text-success me-2"></i>Uses Hook System
                                {else}
                                    <i class="fas fa-times text-muted me-2"></i>No Hooks Defined
                                {/if}
                            </li>
                            <li>
                                {if $plugin.main_file}
                                    <i class="fas fa-check text-success me-2"></i>Valid Structure
                                {else}
                                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>Missing Main File
                                {/if}
                            </li>
                            {if $plugin.api_version}
                                <li><i class="fas fa-code text-info me-2"></i>API Version: {$plugin.api_version}</li>
                            {/if}
                            {if $plugin.priority}
                                <li><i class="fas fa-sort-numeric-up text-info me-2"></i>Priority: {$plugin.priority}</li>
                            {/if}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* Plugin Information Sidebar *}
    <div class="col-lg-4">
        <div class="card mb-4 plugin-info-card">
            <div class="card-body text-center">
                <div class="plugin-icon-large">
                    <i class="fas fa-puzzle-piece"></i>
                </div>
                <h4 class="card-title">{$plugin.display_name}</h4>
                <p class="card-text">{$plugin.description}</p>
                <hr class="bg-white">
                <div class="row text-center">
                    <div class="col-6">
                        <h6>Version</h6>
                        <p class="mb-0">{$plugin.version}</p>
                    </div>
                    <div class="col-6">
                        <h6>Author</h6>
                        <p class="mb-0">{$plugin.author}</p>
                    </div>
                </div>
            </div>
        </div>

        {* Plugin Statistics *}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Plugin Statistics</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="text-primary">{$plugin.files|@count}</h4>
                            <small>Files</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-center">
                            <h4 class="{if $is_active}text-success{else}text-danger{/if}">
                                {if $is_active}ON{else}OFF{/if}
                            </h4>
                            <small>Status</small>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between">
                        <span>Directory:</span>
                        <code class="small">{$plugin.name}</code>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tested up to:</span>
                        <span class="fw-bold">{$plugin.tested_up_to|default:'Unknown'}</span>
                    </div>
                </div>
            </div>
        </div>

        {* Plugin Actions *}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Plugin Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    {if $is_active}
                        <form method="POST" action="/admin/plugins/deactivate">
                            <input type="hidden" name="plugin" value="{$plugin.name}">
                            <button type="submit" class="btn btn-danger w-100" 
                                    onclick="return confirm('Are you sure you want to deactivate this plugin?')">
                                <i class="fas fa-stop me-2"></i>Deactivate Plugin
                            </button>
                        </form>
                        
                        {if $plugin.has_settings}
                            <a href="/admin/plugins/configure?plugin={$plugin.name}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-cogs me-2"></i>Configure Settings
                            </a>
                        {/if}
                    {else}
                        <form method="POST" action="/admin/plugins/activate">
                            <input type="hidden" name="plugin" value="{$plugin.name}">
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Are you sure you want to activate this plugin?')">
                                <i class="fas fa-play me-2"></i>Activate Plugin
                            </button>
                        </form>
                    {/if}
                    
                    <button class="btn btn-outline-secondary w-100" onclick="exportPlugin()">
                        <i class="fas fa-download me-2"></i>Export Plugin
                    </button>
                    
                    <button class="btn btn-outline-danger w-100" onclick="deletePlugin()">
                        <i class="fas fa-trash me-2"></i>Delete Plugin
                    </button>
                </div>
            </div>
        </div>

        {* Plugin Support *}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p>Having issues with this plugin?</p>
                    <ul class="mb-0">
                        <li>Check the plugin documentation</li>
                        <li>Visit the plugin author's website</li>
                        <li>Review the plugin files for errors</li>
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
    window.exportPlugin = function() {
        alert('Plugin export functionality will be available in a future version.');
    };

    window.deletePlugin = function() {
        if (confirm('Are you sure you want to delete this plugin? This action cannot be undone.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/plugins/delete';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'plugin';
            input.value = '{$plugin.name}';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    };
});
</script>
{/block}
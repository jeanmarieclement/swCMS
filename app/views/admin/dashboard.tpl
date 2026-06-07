{* Admin Dashboard Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Dashboard{/block}

{block name="head"}
<!-- Custom styles for this page -->
<style>
    .card-dashboard {
        border-left: 4px solid;
        border-radius: 4px;
    }
    .border-left-primary { border-left-color: var(--bs-primary); }
    .border-left-success { border-left-color: var(--bs-success); }
    .border-left-info { border-left-color: var(--bs-info); }
    .border-left-warning { border-left-color: var(--bs-warning); }
</style>
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Print</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="fas fa-calendar-alt"></i> This week
        </button>
    </div>
</div>

{* Display messages *}
{if isset($message) && $message}
    <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
        {$message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<!-- Stats Overview -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard border-left-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Articles</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{$stats.articles|default:0}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-newspaper fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard border-left-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Pages</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{$stats.pages|default:0}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard border-left-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{$stats.users|default:0}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard border-left-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Comments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{$stats.comments|default:0}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="{$admin_url}/articles/create" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus-circle me-2"></i> New Article
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{$admin_url}/pages/create" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-plus-circle me-2"></i> New Page
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{$admin_url}/media" class="btn btn-outline-info w-100">
                            <i class="fas fa-upload me-2"></i> Upload Media
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="{$admin_url}/comments" class="btn btn-outline-warning w-100">
                            <i class="fas fa-check-circle me-2"></i> Moderate Comments
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Content & Activity -->
<div class="row">
    <!-- Recent Content -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Recent Content</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th style="width: 90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if isset($recent_content) && $recent_content|@count > 0}
                                {foreach from=$recent_content item=content}
                                    <tr>
                                        <td>{$content.title}</td>
                                        <td>{$content.type}</td>
                                        <td>{$content.date}</td>
                                        <td>
                                            <a href="{$admin_url}/{$content.type}s/edit/{$content.id}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{$site_url}/{$content.type}/{$content.slug}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="4" class="text-center">No recent content found</td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Recent Activity</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    {if isset($recent_activity) && $recent_activity|@count > 0}
                        {foreach from=$recent_activity item=activity}
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1">{$activity.title}</h5>
                                    <small>{$activity.time_ago}</small>
                                </div>
                                <p class="mb-1">{$activity.description}</p>
                                <small>{$activity.user}</small>
                            </a>
                        {/foreach}
                    {else}
                        <div class="list-group-item">No recent activity found</div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold">System Information</h6>
                <a href="{$admin_url}/clear-cache" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to clear the compiled template cache?')">
                    <i class="fas fa-trash-alt me-2"></i>Clear Cache
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>PHP Version:</strong> {$system_info.php_version|default:'Unknown'}</p>
                        <p><strong>Database:</strong> {$system_info.db_type|default:'Unknown'} {$system_info.db_version|default:''}</p>
                        <p><strong>Server:</strong> {$system_info.server|default:'Unknown'}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>CMS Version:</strong> {$system_info.cms_version|default:'Unknown'}</p>
                        <p><strong>Theme:</strong> {$system_info.theme|default:'Default'}</p>
                        <p><strong>Active Plugins:</strong> {$system_info.active_plugins|default:'0'}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Memory Usage:</strong> {$system_info.memory_usage|default:'Unknown'}</p>
                        <p><strong>Upload Max Size:</strong> {$system_info.upload_max_size|default:'Unknown'}</p>
                        <p><strong>Session Timeout:</strong> {$system_info.session_timeout|default:'Unknown'}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
    // Dashboard specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Any dashboard-specific initialization can go here
        console.log('Dashboard loaded');
    });
</script>
{/block}

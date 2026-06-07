{* Admin Page Edit Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Edit Page{/block}

{block name="head"}
{if isset($tinymce_include)}
{$tinymce_include}
{/if}
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/pages">Pages</a></li>
        <li class="breadcrumb-item active">Edit Page</li>
    </ol>
</nav>
{/block}

{block name="content"}
<form method="post" action="{$admin_url}/pages/update/{$page.id}" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Page: {$page.title|default:'Untitled'}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{$admin_url}/pages" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Pages
            </a>
            <a href="{$site_url}/{$page.slug}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                <i class="fas fa-eye me-1"></i> View Page
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="previewBtn">
                <i class="fas fa-search me-1"></i> Preview
            </button>
            <a href="{$admin_url}/pages/revisions/{$page.id}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-history me-1"></i> Revisions
            </a>
        </div>
    </div>

    {* Display messages *}
    {if isset($saved) && $saved}
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> Page saved successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}

    {if isset($error)}
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {$error}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}

    <div class="row">
        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit me-1"></i>
                    Page Details
                </div>
                <div class="card-body">
                    {include file="admin/pages/partials/_page_form.tpl"}
                </div>
            </div>
            
            {include file="admin/pages/partials/_seo_settings.tpl"}
        </div>
        
        <div class="col-lg-3">
            <!-- Sidebar Widgets -->
            {include file="admin/pages/partials/_publishing_options.tpl"}
            {include file="admin/pages/partials/_page_attributes.tpl"}
        </div>
    </div>

    {* Include modals *}
    {include file="admin/pages/partials/_modals.tpl"}
</form>
{/block}

{block name="scripts"}
{if !isset($tinymce_include)}
<!-- TinyMCE -->
<script src="{$site_url}/vendor/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{$site_url}/js/tinymce-init.js"></script>
{/if}
{include file="admin/pages/partials/_page_scripts.tpl"}
{/block}

{* Create New Article Template *}
{extends file="admin/layout.tpl"}

{block name="title"}Create New Article{/block}

{block name="head"}
<!-- TinyMCE CSS -->

{if isset($tinymce_include)}
{$tinymce_include}
{/if}
{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/articles">Articles</a></li>
        <li class="breadcrumb-item active">Create New Article</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Create New Article</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="previewBtn">
                <i class="fas fa-eye"></i> Preview
            </button>
        </div>
    </div>
</div>

{* Display success/error messages *}
{if isset($error)}
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {$error}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}

<div class="row">
    <div class="col-lg-9">
        <!-- Main Content Form -->
        <div class="card shadow mb-4">
            <div class="card-body">
                {include file="admin/articles/partials/_article_form.tpl"}
            </div>
        </div>
    </div>
    
    <div class="col-lg-3">
        <!-- Sidebar Widgets -->
        {include file="admin/articles/partials/_publishing_options.tpl"}
        {include file="admin/articles/partials/_categories_widget.tpl"}
        {include file="admin/articles/partials/_tags_widget.tpl"}
    </div>
</div>

{* Include modals *}
{include file="admin/articles/partials/_modals.tpl"}
{/block}

{block name="scripts"}
{if !isset($tinymce_include)}
<!-- TinyMCE -->
<script src="{$site_url}/vendor/tinymce/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
<script src="{$site_url}/js/tinymce-init.js"></script>
{/if}
{include file="admin/articles/partials/_article_scripts.tpl"}
{/block}

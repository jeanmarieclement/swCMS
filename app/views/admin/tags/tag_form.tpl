{* Admin Tag Form Template *}
{extends file="admin/layout.tpl"}

{block name="title"}{if $action == 'edit'}Edit Tag{else}Create Tag{/if}{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/tags">Tags</a></li>
        <li class="breadcrumb-item active">{if $action == 'edit'}Edit Tag{else}Create Tag{/if}</li>
    </ol>
</nav>
{/block}

{block name="content"}
<form method="post" action="{if $action == 'edit'}{$admin_url}/tags/update{else}{$admin_url}/tags/store{/if}" class="needs-validation" novalidate>
    <input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
    {if $action == 'edit'}
        <input type="hidden" name="id" value="{$tag.id}">
    {/if}

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{if $action == 'edit'}Edit Tag{else}Create New Tag{/if}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{$admin_url}/tags" class="btn btn-sm btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Tags
            </a>
        </div>
    </div>

    {* Display messages *}
    {if isset($message) && $message}
        <div class="alert alert-{if $messageType == 'error'}danger{else}{$messageType}{/if} alert-dismissible fade show" role="alert">
            {$message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {elseif $errors}
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please correct the following errors:
            <ul class="mb-0 mt-2">
                {foreach $errors as $error}
                    <li>{$error}</li>
                {/foreach}
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tag me-1"></i>
                    Tag Details
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tag Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{$tag.name|default:''|escape}" required maxlength="100" autocomplete="off">
                        <div class="form-text">The name is how it appears on your site.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="slug" class="form-control" value="{$tag.slug|default:''|escape}" required maxlength="100" pattern="[a-z0-9-]+">
                        <div class="form-text">The slug is the URL-friendly version of the name. It is usually lowercase and contains only letters, numbers, and hyphens.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="4" placeholder="Optional description for this tag...">{$tag.description|default:''|escape}</textarea>
                        <div class="form-text">The description is not prominently displayed by default; however, some themes may show it.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-save me-1"></i>
                    Publish
                </div>
                <div class="card-body">
                    {if $action == 'edit' && isset($tag.created_at)}
                        <p class="mb-2"><strong>Created:</strong> {$tag.created_at|date_format:"%B %e, %Y at %l:%M %p"}</p>
                        {if isset($tag.updated_at) && $tag.updated_at != $tag.created_at}
                            <p class="mb-3"><strong>Last modified:</strong> {$tag.updated_at|date_format:"%B %e, %Y at %l:%M %p"}</p>
                        {/if}
                    {/if}
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> {if $action == 'edit'}Update Tag{else}Create Tag{/if}
                        </button>
                        <a href="{$admin_url}/tags" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
{/block}

{block name="scripts"}
<script>
    $(document).ready(function() {
        // Auto-generate slug from tag name
        $('#name').on('input', function() {
            var slug = $(this).val()
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/[\s-]+/g, '-')      // Replace spaces and multiple hyphens with single hyphen
                .replace(/^-+|-+$/g, '');    // Remove leading/trailing hyphens
            
            $('#slug').val(slug);
        });

        // Validate form before submit
        $('form.needs-validation').on('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    });
</script>
{/block}

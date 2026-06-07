{* SEO Settings Partial *}
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-search me-1"></i>
        SEO Settings
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="meta_title" class="form-label">Meta Title</label>
            <input type="text" class="form-control" id="meta_title" name="meta_title" value="{if isset($page.meta_title)}{$page.meta_title}{/if}">
        </div>
        
        <div class="mb-3">
            <label for="meta_description" class="form-label">Meta Description</label>
            <textarea class="form-control" id="meta_description" name="meta_description" rows="3">{if isset($page.meta_description)}{$page.meta_description}{/if}</textarea>
        </div>
    </div>
</div>

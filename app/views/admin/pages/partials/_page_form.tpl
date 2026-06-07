{* Page Form Partial - Contains only form fields, not the form tag *}
<div class="mb-3">
    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="title" name="title" value="{$page.title|default:''}" required>
    <div class="invalid-feedback">
        Please enter a title.
    </div>
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Permalink</label>
    <div class="input-group">
        <span class="input-group-text">{$site_url}/</span>
        <input type="text" class="form-control" id="slug" name="slug" value="{$page.slug|default:''}" placeholder="page-slug">
    </div>
    <div class="form-text">Leave blank to generate automatically from title.</div>
</div>

<div class="mb-3">
    <label for="content" class="form-label">Content</label>
    {if isset($editor_html)}
        {$editor_html}
    {else}
        <textarea class="form-control tinymce-editor" id="content" name="content" rows="10">{$page.content|default:''}</textarea>
    {/if}
</div>

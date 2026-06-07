<form action="{$admin_url}/articles/{if isset($article.id)}edit/{$article.id}{else}create{/if}" method="post" id="articleForm">
    <input type="hidden" name="id" value="{$article.id|default:''}">
    
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="{$article.title|default:''}" required>
    </div>
    
    <div class="mb-3">
        <label for="slug" class="form-label">Slug (URL)</label>
        <div class="input-group">
            <span class="input-group-text">{$site_url}/</span>
            <input type="text" class="form-control" id="slug" name="slug" value="{$article.slug|default:''}" aria-describedby="slugHelp">
        </div>
        <div id="slugHelp" class="form-text">Leave empty to generate automatically from title.</div>
    </div>
    
    <div class="mb-3">
        <label for="excerpt" class="form-label">Excerpt</label>
        <textarea class="form-control" id="excerpt" name="excerpt" rows="3" aria-describedby="excerptHelp">{$article.excerpt|default:''}</textarea>
        <div id="excerptHelp" class="form-text">A short summary of the article. If left empty, it will be generated from the content.</div>
    </div>
    
    <div class="mb-4">
        <label for="content" class="form-label">Content</label>
        {if isset($editor_html)}
            {$editor_html}
        {else}
            <textarea class="form-control tinymce-editor" id="content" name="content" rows="15">{$article.content|default:''}</textarea>
        {/if}
    </div>
    
    <div class="mb-3">
        <label for="featured_image" class="form-label">Featured Image</label>
        <div class="input-group">
            <input type="text" class="form-control" id="featured_image" name="featured_image" value="{$article.featured_image|default:''}" readonly>
            <button class="btn btn-outline-secondary" type="button" id="selectImageBtn">Select Image</button>
        </div>
        {if isset($article.featured_image) && $article.featured_image}
            <div class="mt-2">
                <img src="{$article.featured_image}" alt="Featured Image" class="img-thumbnail" style="max-height: 150px;">
            </div>
        {/if}
    </div>
</form>

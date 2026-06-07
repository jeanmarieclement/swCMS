<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Publishing</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status" form="articleForm">
                <option value="draft" {if isset($article.status) && $article.status == 'draft'}selected{/if}>Draft</option>
                <option value="published" {if isset($article.status) && $article.status == 'published'}selected{/if}>Published</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Comment Status</label>
            <select class="form-select" name="comments_enabled" form="articleForm">
                <option value="1" {if !isset($article.comments_enabled) || $article.comments_enabled == '1' || $article.comments_enabled == 1}selected{/if}>Allow Comments</option>
                <option value="0" {if isset($article.comments_enabled) && ($article.comments_enabled == '0' || $article.comments_enabled == 0)}selected{/if}>Disable Comments</option>
            </select>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" form="articleForm" name="save">
                <i class="fas fa-save"></i> Save
            </button>
            
           {if isset($article.id) && (!isset($article.status) || $article.status != 'published')}
               <a href="{$admin_url}/articles/status/{$article.id}/published" class="btn btn-success"
                   onclick="return confirm('Are you sure you want to publish this article?');">
                   <i class="fas fa-paper-plane"></i> Publish
               </a>
           {/if}
            
            <a href="{$admin_url}/articles" class="btn btn-light">
                <i class="fas fa-times"></i> Cancel
            </a>
            
            {if isset($article.id) && (!isset($article.status) || $article.status != 'trash')}
                <a href="{$admin_url}/articles/status/{$article.id}/trash" class="btn btn-danger"
                   onclick="return confirm('Are you sure you want to move this article to trash?');">
                    <i class="fas fa-trash"></i> Move to Trash
                </a>
            {/if}
        </div>
    </div>
</div>

{* Publishing Options Partial *}
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Publishing</h6>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="published" {if isset($page.status) && $page.status == 'published'}selected{/if}>Published</option>
                <option value="draft" {if isset($page.status) && $page.status == 'draft'}selected{/if}>Draft</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="visibility" class="form-label">Visibility</label>
            <select class="form-select" id="visibility" name="visibility">
                <option value="public" {if isset($page.visibility) && $page.visibility == 'public'}selected{/if}>Public</option>
                <option value="private" {if isset($page.visibility) && $page.visibility == 'private'}selected{/if}>Private</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="comments_enabled" class="form-label">Comments</label>
            <select class="form-select" id="comments_enabled" name="comments_enabled">
                <option value="1" {if !isset($page.comments_enabled) || $page.comments_enabled == '1' || $page.comments_enabled == 1}selected{/if}>Allow Comments</option>
                <option value="0" {if isset($page.comments_enabled) && ($page.comments_enabled == '0' || $page.comments_enabled == 0)}selected{/if}>Disable Comments</option>
            </select>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                {if $action == 'edit'}Update{else}Publish{/if} Page
            </button>
        </div>
    </div>
</div>

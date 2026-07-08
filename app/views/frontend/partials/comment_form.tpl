{* Comment form partial *}
{if $comments_enabled}
<div class="comment-form-section mt-4">
    <h4>Leave a Comment</h4>
    
    {* Flash messages *}
    {if $flash_messages}
        {foreach $flash_messages as $message}
            <div class="alert alert-{$message.type} alert-dismissible fade show" role="alert">
                {$message.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/foreach}
    {/if}
    
    <form action="/comments/store" method="POST" class="comment-form">
        <input type="hidden" name="csrf_token" value="{$csrf_token}">
        <input type="hidden" name="post_id" value="{$post.id|default:''}">
        <input type="hidden" name="page_id" value="{$page.id|default:''}">
        <input type="hidden" name="redirect_url" value="{$smarty.server.REQUEST_URI}">
        
        {* If user is not logged in, show name and email fields *}
        {if !$user_id}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="author_name" class="form-label">Name *</label>
                    <input type="text" class="form-control" id="author_name" name="author_name" required>
                </div>
                <div class="col-md-6">
                    <label for="author_email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="author_email" name="author_email" required>
                    <div class="form-text">Your email will not be published.</div>
                </div>
            </div>
        {else}
            <p class="mb-3"><strong>Commenting as:</strong> {$user_display_name}</p>
        {/if}
        
        <div class="mb-3">
            <label for="content" class="form-label">Comment *</label>
            <textarea class="form-control" id="content" name="content" rows="4" required 
                      placeholder="Write your comment here..."></textarea>
            <div class="form-text">HTML is not allowed. Comments will be moderated before publication.</div>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit Comment</button>
    </form>
</div>
{/if}
{extends file="admin/layout.tpl"}
{block name="content"}
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Reply to Comment</h1>
        <a href="/admin/comments" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Back to Comments
        </a>
    </div>
    
    {* Parent comment display *}
    <div class="card mb-4 border-info">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fa fa-comment"></i> Original Comment</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-2">
                        <strong>Author:</strong>
                        {if $parent_comment.user_id}
                            {if $parent_comment.user_display_name}
                                {$parent_comment.user_display_name|escape} <i class="fa fa-registered text-success"></i>
                            {elseif $parent_comment.user_username}
                                {$parent_comment.user_username|escape} <i class="fa fa-registered text-success"></i>
                            {else}
                                Registered User
                            {/if}
                        {else}
                            {if $parent_comment.author_name}
                                {$parent_comment.author_name|escape} <i class="fa fa-user text-muted"></i>
                            {else}
                                Anonymous
                            {/if}
                        {/if}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong>
                        {if $parent_comment.user_id}
                            {$parent_comment.user_email|escape|default:'-'}
                        {else}
                            {$parent_comment.author_email|escape|default:'-'}
                        {/if}
                    </div>
                    <div class="mb-2">
                        <strong>Date:</strong> {$parent_comment.created_at|date_format:"%d/%m/%Y %H:%M"}
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong>
                        {if $parent_comment.status == 'approved'}<span class="badge bg-success">Approved</span>{/if}
                        {if $parent_comment.status == 'pending'}<span class="badge bg-warning text-dark">Pending</span>{/if}
                        {if $parent_comment.status == 'spam'}<span class="badge bg-danger">Spam</span>{/if}
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <strong>Content:</strong>
                <div class="bg-light p-3 rounded mt-2">
                    {$parent_comment.content|escape|nl2br}
                </div>
            </div>
        </div>
    </div>
    
    {* Reply form *}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fa fa-reply"></i> Your Reply</h5>
        </div>
        <div class="card-body">
            {if $flash}
                <div class="alert alert-{if $flash.type == 'error'}danger{else}{$flash.type}{/if} alert-dismissible fade show" role="alert">
                    {$flash.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            {/if}
            
            <form method="POST" action="/admin/comments/reply?id={$parent_comment.id}">
                {App\Helpers\SecurityHelper::csrf_field()}
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="author_name" class="form-label">Author Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="author_name" name="author_name" 
                                   value="{$form_data.author_name|default:$smarty.session.user_display_name|default:$smarty.session.username|escape}" 
                                   required>
                            <div class="form-text">The name that will appear as the reply author</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="author_email" class="form-label">Author Email</label>
                            <input type="email" class="form-control" id="author_email" name="author_email" 
                                   value="{$form_data.author_email|default:$smarty.session.user_email|escape}">
                            <div class="form-text">Optional email address for the reply author</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label">Reply Content <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="content" name="content" rows="6" required>{$form_data.content|default:''|escape}</textarea>
                    <div class="form-text">Your reply to the comment above</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Reply Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="approved" {if $form_data.status|default:'approved' == 'approved' || !$form_data.status}selected{/if}>Approved</option>
                                <option value="pending" {if $form_data.status|default:'approved' == 'pending'}selected{/if}>Pending</option>
                                <option value="spam" {if $form_data.status|default:'approved' == 'spam'}selected{/if}>Spam</option>
                            </select>
                            <div class="form-text">Admin replies are typically approved by default</div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="/admin/comments" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-reply"></i> Send Reply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus the content textarea
    document.getElementById('content').focus();
    
    // Auto-resize textarea based on content
    const textarea = document.getElementById('content');
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});
</script>
{/block}
{* Comment form partial *}
{if $comments_enabled}
<div class="comment-form-section mt-4" id="comment-form">
    <h4 id="comment-form-title">Lascia un commento</h4>
    
    {* Reply info (hidden by default) *}
    <div id="reply-info" class="alert alert-info" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <span>
                <i class="fa fa-reply"></i> 
                Stai rispondendo a <strong id="reply-author"></strong>
            </span>
            <button type="button" class="btn-close" id="cancel-reply"></button>
        </div>
    </div>
    
    {* Flash messages *}
    {if $flash}
        <div class="alert alert-{$flash.type} alert-dismissible fade show" role="alert">
            {$flash.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}
    
    <form action="/comments/store" method="POST" class="comment-form">
        <input type="hidden" name="post_id" value="{$post.id|default:''}">
        <input type="hidden" name="page_id" value="{$page.id|default:''}">
        <input type="hidden" name="parent_id" id="parent_id" value="">
        <input type="hidden" name="redirect_url" value="{$smarty.server.REQUEST_URI}">
        
        {* If user is not logged in, show name and email fields *}
        {if !$user_id}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="author_name" class="form-label">Nome *</label>
                    <input type="text" class="form-control" id="author_name" name="author_name" required>
                </div>
                <div class="col-md-6">
                    <label for="author_email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="author_email" name="author_email" required>
                    <div class="form-text">La tua email non sarà pubblicata.</div>
                </div>
            </div>
        {else}
            <p class="mb-3"><strong>Commentando come:</strong> {if $user_display_name}{$user_display_name|escape}{else}Utente registrato{/if}</p>
        {/if}
        
        <div class="mb-3">
            <label for="content" class="form-label">Commento *</label>
            <textarea class="form-control" id="content" name="content" rows="4" required 
                      placeholder="Scrivi il tuo commento qui..."></textarea>
            <div class="form-text">HTML non è permesso. Il commento sarà moderato prima della pubblicazione.</div>
        </div>
        
        <button type="submit" class="btn btn-primary" id="submit-button">Invia Commento</button>
        <button type="button" class="btn btn-secondary ms-2" id="cancel-reply-form" style="display: none;">Annulla Risposta</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const replyLinks = document.querySelectorAll('.reply-link');
    const replyInfo = document.getElementById('reply-info');
    const replyAuthor = document.getElementById('reply-author');
    const parentIdInput = document.getElementById('parent_id');
    const formTitle = document.getElementById('comment-form-title');
    const submitButton = document.getElementById('submit-button');
    const cancelReplyButton = document.getElementById('cancel-reply');
    const cancelReplyFormButton = document.getElementById('cancel-reply-form');
    const commentForm = document.getElementById('comment-form');
    
    // Handle reply link clicks
    replyLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const parentId = this.dataset.parentId;
            const authorName = this.dataset.author;
            
            // Set up reply mode
            parentIdInput.value = parentId;
            replyAuthor.textContent = authorName;
            replyInfo.style.display = 'block';
            formTitle.textContent = 'Rispondi al commento';
            submitButton.textContent = 'Invia Risposta';
            cancelReplyFormButton.style.display = 'inline-block';
            
            // Scroll to form
            commentForm.scrollIntoView({ behavior: 'smooth' });
            
            // Focus on content textarea
            const contentTextarea = document.getElementById('content');
            if (contentTextarea) {
                contentTextarea.focus();
            }
        });
    });
    
    // Handle cancel reply
    function cancelReply() {
        parentIdInput.value = '';
        replyInfo.style.display = 'none';
        formTitle.textContent = 'Lascia un commento';
        submitButton.textContent = 'Invia Commento';
        cancelReplyFormButton.style.display = 'none';
        
        // Clear content if needed
        const contentTextarea = document.getElementById('content');
        if (contentTextarea) {
            contentTextarea.value = '';
        }
    }
    
    if (cancelReplyButton) {
        cancelReplyButton.addEventListener('click', cancelReply);
    }
    
    if (cancelReplyFormButton) {
        cancelReplyFormButton.addEventListener('click', cancelReply);
    }
});
</script>
{/if}
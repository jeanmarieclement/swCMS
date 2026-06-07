{* Comments list partial *}
{if $comments_enabled && $comments && count($comments) > 0}
<div class="comments-section mt-5">
    <h4>Commenti ({$total_comments})</h4>
    
    <div class="comments-list">
        {function name=displayFrontendComment comment=null level=0}
            <div class="comment mb-4 {if $comment.parent_id}ms-{math equation="x*3" x=$level} reply-comment{/if}">
                <div class="comment-wrapper p-3 border rounded {if $comment.parent_id}border-start border-3 border-primary bg-light{/if}">
                    <div class="comment-meta mb-2">
                        <strong class="comment-author">
                            {if $comment.user_display_name}
                                {$comment.user_display_name}
                            {else}
                                {$comment.author_name}
                            {/if}
                        </strong>
                        <span class="comment-date text-muted ms-2">
                            {$comment.created_at|date_format:"%d/%m/%Y alle %H:%M"}
                        </span>
                        {if $comment.parent_id}
                            <span class="reply-indicator text-info ms-2">
                                <i class="fa fa-reply"></i> Risposta
                            </span>
                        {/if}
                    </div>
                    <div class="comment-content">
                        {$comment.content|nl2br|escape}
                    </div>
                    
                    {* Reply button (could be implemented for logged-in users) *}
                    <div class="comment-actions mt-2">
                        <small>
                            <a href="#comment-form" class="text-primary reply-link" data-parent-id="{$comment.id}" data-author="{if $comment.user_display_name}{$comment.user_display_name}{else}{$comment.author_name}{/if}">
                                <i class="fa fa-reply"></i> Rispondi
                            </a>
                        </small>
                    </div>
                </div>
            </div>
            
            {* Display replies recursively *}
            {if $comment.replies && count($comment.replies) > 0}
                {foreach $comment.replies as $reply}
                    {call displayFrontendComment comment=$reply level=$level+1}
                {/foreach}
            {/if}
        {/function}
        
        {foreach $comments as $comment}
            {call displayFrontendComment comment=$comment level=0}
        {/foreach}
    </div>
    
    {* Pagination for comments if needed *}
    {if $total_pages > 1}
        <nav aria-label="Paginazione commenti">
            <ul class="pagination">
                {if $current_page > 1}
                    <li class="page-item">
                        <a class="page-link" href="?comment_page={$current_page - 1}#comments">Precedente</a>
                    </li>
                {/if}
                
                {for $i=1 to $total_pages}
                    <li class="page-item {if $i == $current_page}active{/if}">
                        <a class="page-link" href="?comment_page={$i}#comments">{$i}</a>
                    </li>
                {/for}
                
                {if $current_page < $total_pages}
                    <li class="page-item">
                        <a class="page-link" href="?comment_page={$current_page + 1}#comments">Successivo</a>
                    </li>
                {/if}
            </ul>
        </nav>
    {/if}
</div>
{elseif $comments_enabled}
<div class="comments-section mt-5">
    <h4>Commenti</h4>
    <p class="text-muted">Nessun commento ancora. Sii il primo a commentare!</p>
</div>
{/if}
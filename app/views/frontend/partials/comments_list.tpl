{* Comments list partial *}
{if $comments_enabled && $comments && count($comments) > 0}
<div class="comments-section mt-5">
    <h4>Commenti ({$total_comments})</h4>
    
    <div class="comments-list">
        {foreach $comments as $comment}
            <div class="comment mb-4 p-3 border rounded">
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
                </div>
                <div class="comment-content">
                    {$comment.content|nl2br|escape}
                </div>
            </div>
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
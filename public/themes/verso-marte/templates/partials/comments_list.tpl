{* Comments list per il tema Verso Marte - Mars Mission Log Style *}
{if isset($comments) && $comments|@count > 0}
    <div class="mission-log-entries">
        <h3 class="log-title">
            <i class="fas fa-comments"></i>
            Log della Missione ({$comments|@count} trasmissioni)
        </h3>
        
        <div class="comments-container">
            {foreach from=$comments item=comment}
                <article class="comment-entry" data-comment-id="{$comment.id}">
                    <div class="comment-header">
                        <div class="astronaut-info">
                            <div class="astronaut-avatar">
                                {if isset($comment.avatar) && $comment.avatar}
                                    <img src="{$comment.avatar}" alt="{$comment.author|escape}" class="avatar-img">
                                {else}
                                    <i class="fas fa-user-astronaut"></i>
                                {/if}
                            </div>
                            <div class="astronaut-details">
                                <h4 class="astronaut-name">
                                    {$comment.author|escape}
                                    {if isset($comment.user_role) && $comment.user_role}
                                        <span class="astronaut-rank {$comment.user_role}">{$comment.user_role|capitalize}</span>
                                    {/if}
                                </h4>
                                <div class="transmission-meta">
                                    <span class="transmission-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        Sol {$comment.created_at|date_format:"%j"} - {$comment.created_at|date_format:"%H:%M"}
                                    </span>
                                    <span class="transmission-id">
                                        <i class="fas fa-satellite-dish"></i>
                                        ID#{$comment.id}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="comment-actions">
                            {if isset($user) && $user && ($user.id == $comment.user_id || $user.role == 'admin')}
                                <button class="action-btn edit-comment" data-comment-id="{$comment.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn delete-comment" data-comment-id="{$comment.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            {/if}
                            <button class="action-btn reply-comment" data-comment-id="{$comment.id}">
                                <i class="fas fa-reply"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="comment-body">
                        <div class="transmission-content">
                            {$comment.content|nl2br}
                        </div>
                        
                        {if isset($comment.edited_at) && $comment.edited_at}
                            <div class="edit-indicator">
                                <i class="fas fa-edit"></i>
                                <span>Trasmissione modificata il {$comment.edited_at|date_format:"%d/%m/%Y alle %H:%M"}</span>
                            </div>
                        {/if}
                    </div>
                    
                    {* Nested replies *}
                    {if isset($comment.replies) && $comment.replies|@count > 0}
                        <div class="comment-replies">
                            {foreach from=$comment.replies item=reply}
                                <article class="comment-entry reply" data-comment-id="{$reply.id}">
                                    <div class="comment-header">
                                        <div class="astronaut-info">
                                            <div class="astronaut-avatar">
                                                {if isset($reply.avatar) && $reply.avatar}
                                                    <img src="{$reply.avatar}" alt="{$reply.author|escape}" class="avatar-img">
                                                {else}
                                                    <i class="fas fa-user-astronaut"></i>
                                                {/if}
                                            </div>
                                            <div class="astronaut-details">
                                                <h4 class="astronaut-name">
                                                    {$reply.author|escape}
                                                    {if isset($reply.user_role) && $reply.user_role}
                                                        <span class="astronaut-rank {$reply.user_role}">{$reply.user_role|capitalize}</span>
                                                    {/if}
                                                </h4>
                                                <div class="transmission-meta">
                                                    <span class="transmission-date">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        Sol {$reply.created_at|date_format:"%j"} - {$reply.created_at|date_format:"%H:%M"}
                                                    </span>
                                                    <span class="reply-indicator">
                                                        <i class="fas fa-reply"></i>
                                                        In risposta a {$comment.author|escape}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="comment-actions">
                                            {if isset($user) && $user && ($user.id == $reply.user_id || $user.role == 'admin')}
                                                <button class="action-btn edit-comment" data-comment-id="{$reply.id}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="action-btn delete-comment" data-comment-id="{$reply.id}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            {/if}
                                        </div>
                                    </div>
                                    
                                    <div class="comment-body">
                                        <div class="transmission-content">
                                            {$reply.content|nl2br}
                                        </div>
                                        
                                        {if isset($reply.edited_at) && $reply.edited_at}
                                            <div class="edit-indicator">
                                                <i class="fas fa-edit"></i>
                                                <span>Trasmissione modificata il {$reply.edited_at|date_format:"%d/%m/%Y alle %H:%M"}</span>
                                            </div>
                                        {/if}
                                    </div>
                                </article>
                            {/foreach}
                        </div>
                    {/if}
                    
                    {* Reply form (hidden by default) *}
                    <div class="reply-form" id="reply-form-{$comment.id}" style="display: none;">
                        <form class="mars-comment-form" data-parent-id="{$comment.id}">
                            <div class="form-header">
                                <h4>
                                    <i class="fas fa-reply"></i>
                                    Risposta a {$comment.author|escape}
                                </h4>
                            </div>
                            <div class="form-group">
                                <label for="reply-content-{$comment.id}">
                                    <i class="fas fa-comment"></i>
                                    Messaggio di risposta:
                                </label>
                                <textarea id="reply-content-{$comment.id}" name="content" required class="form-control" rows="3" 
                                        placeholder="Scrivi la tua risposta alla trasmissione..."></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn mars-btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Invia Risposta
                                </button>
                                <button type="button" class="btn btn-secondary cancel-reply">
                                    <i class="fas fa-times"></i>
                                    Annulla
                                </button>
                            </div>
                        </form>
                    </div>
                </article>
            {/foreach}
        </div>
    </div>
{else}
    <div class="no-comments">
        <div class="empty-log">
            <i class="fas fa-satellite-dish"></i>
            <h3>Nessuna trasmissione ricevuta</h3>
            <p>Sii il primo a lasciare un messaggio nel log della missione!</p>
        </div>
    </div>
{/if}

<style>
/* Mars Comments List Styles */
.mission-log-entries {
    margin-top: 2rem;
}

.log-title {
    font-family: var(--font-heading);
    color: var(--mars-orange);
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    text-align: center;
    padding: 1rem;
    background: rgba(30, 58, 138, 0.2);
    border-radius: var(--border-radius);
    border: 1px solid rgba(30, 58, 138, 0.3);
}

.log-title i {
    color: var(--gold-accent);
    margin-right: 0.5rem;
}

.comments-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Comment Entry */
.comment-entry {
    background: rgba(26, 26, 46, 0.7);
    border: 1px solid rgba(205, 92, 92, 0.3);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
}

.comment-entry:hover {
    border-color: var(--mars-orange);
    box-shadow: var(--shadow-mars);
}

.comment-entry.reply {
    margin-left: 2rem;
    border-left: 4px solid var(--mars-red);
    background: rgba(30, 58, 138, 0.2);
    border-color: rgba(30, 58, 138, 0.3);
}

/* Comment Header */
.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.astronaut-info {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.astronaut-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--mars-gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: var(--shadow-mars);
}

.astronaut-avatar i {
    color: white;
    font-size: 1.5rem;
}

.avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.astronaut-details {
    flex-grow: 1;
}

.astronaut-name {
    color: var(--gold-accent);
    font-family: var(--font-heading);
    font-size: 1.1rem;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.astronaut-rank {
    font-size: 0.8rem;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.astronaut-rank.admin {
    background: var(--mars-gradient);
    color: white;
}

.astronaut-rank.editor {
    background: var(--gold-accent);
    color: var(--deep-space);
}

.astronaut-rank.author {
    background: rgba(30, 58, 138, 0.3);
    color: var(--starlight);
    border: 1px solid var(--deep-navy);
}

.transmission-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--mars-dust);
}

.transmission-date,
.transmission-id,
.reply-indicator {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.transmission-date i,
.transmission-id i,
.reply-indicator i {
    color: var(--mars-orange);
}

/* Comment Actions */
.comment-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 1px solid rgba(205, 92, 92, 0.3);
    background: rgba(26, 26, 46, 0.7);
    color: var(--starlight);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover {
    border-color: var(--mars-orange);
    background: var(--mars-gradient);
    color: white;
    transform: translateY(-2px);
}

.action-btn.delete-comment:hover {
    background: #ff4757;
    border-color: #ff4757;
}

/* Comment Body */
.comment-body {
    margin-top: 1rem;
}

.transmission-content {
    color: var(--starlight);
    line-height: 1.6;
    padding: 1rem;
    background: rgba(26, 26, 46, 0.5);
    border-radius: var(--border-radius);
    border-left: 3px solid var(--mars-red);
}

.edit-indicator {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: var(--mars-dust);
    opacity: 0.7;
    font-style: italic;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.edit-indicator i {
    color: var(--mars-orange);
}

/* Reply Form */
.reply-form {
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(30, 58, 138, 0.1);
    border-radius: var(--border-radius);
    border: 1px solid rgba(30, 58, 138, 0.2);
}

.mars-comment-form .form-header {
    margin-bottom: 1rem;
}

.mars-comment-form .form-header h4 {
    color: var(--mars-orange);
    font-family: var(--font-heading);
    font-size: 1rem;
    margin: 0;
}

.mars-comment-form .form-header i {
    color: var(--gold-accent);
    margin-right: 0.5rem;
}

.mars-comment-form .form-group {
    margin-bottom: 1rem;
}

.mars-comment-form label {
    display: block;
    color: var(--starlight);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.mars-comment-form label i {
    color: var(--mars-orange);
    margin-right: 0.5rem;
}

.mars-comment-form .form-control {
    width: 100%;
    padding: 0.75rem;
    background: rgba(26, 26, 46, 0.8);
    border: 1px solid rgba(205, 92, 92, 0.3);
    border-radius: var(--border-radius);
    color: var(--starlight);
    font-family: var(--font-body);
    resize: vertical;
}

.mars-comment-form .form-control:focus {
    outline: none;
    border-color: var(--mars-orange);
    box-shadow: 0 0 0 2px rgba(205, 92, 92, 0.2);
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.mars-comment-form .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.btn-secondary {
    background: rgba(230, 230, 250, 0.1);
    border: 1px solid rgba(230, 230, 250, 0.2);
    color: var(--starlight);
}

.btn-secondary:hover {
    background: rgba(230, 230, 250, 0.2);
    color: var(--starlight);
}

/* Comment Replies */
.comment-replies {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* No Comments State */
.no-comments {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-log {
    background: rgba(26, 26, 46, 0.7);
    border-radius: var(--border-radius);
    padding: 2rem;
    border: 2px dashed rgba(205, 92, 92, 0.3);
}

.empty-log i {
    font-size: 3rem;
    color: var(--mars-orange);
    margin-bottom: 1rem;
    opacity: 0.7;
}

.empty-log h3 {
    color: var(--gold-accent);
    margin-bottom: 1rem;
    font-family: var(--font-heading);
}

.empty-log p {
    color: var(--starlight);
    opacity: 0.8;
}

/* Responsive */
@media (max-width: 768px) {
    .comment-entry.reply {
        margin-left: 1rem;
    }
    
    .comment-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .astronaut-info {
        align-items: center;
    }
    
    .transmission-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .astronaut-avatar {
        width: 40px;
        height: 40px;
    }
    
    .astronaut-avatar i {
        font-size: 1.2rem;
    }
}
</style>
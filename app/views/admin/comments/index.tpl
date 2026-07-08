{extends file="admin/layout.tpl"}

{block name="title"}Comments{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Comments</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="container-fluid">
    <h1>Comments Management</h1>
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="btn-group" role="group">
                <a href="/admin/comments" class="btn btn-outline-secondary {if !$status}active{/if}">All</a>
                <a href="/admin/comments?status=approved" class="btn btn-outline-success {if $status=='approved'}active{/if}">Approved</a>
                <a href="/admin/comments?status=pending" class="btn btn-outline-warning {if $status=='pending'}active{/if}">Pending</a>
                <a href="/admin/comments?status=spam" class="btn btn-outline-danger {if $status=='spam'}active{/if}">Spam</a>
            </div>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group" role="group">
                <a href="/admin/comments?hierarchical=true{if $status}&status={$status}{/if}" 
                   class="btn btn-outline-info {if $hierarchical=='true'}active{/if}">
                   <i class="fa fa-sitemap"></i> Hierarchical
                </a>
                <a href="/admin/comments?hierarchical=false{if $status}&status={$status}{/if}" 
                   class="btn btn-outline-info {if $hierarchical=='false'}active{/if}">
                   <i class="fa fa-list"></i> List
                </a>
            </div>
        </div>
    </div>
    {if $hierarchical == 'true'}
        {* Hierarchical View *}
        <div class="comments-hierarchical">
            {function name=displayComment comment=null level=0}
                <div class="comment-item {if $comment.parent_id}ms-{math equation="x*3" x=$level} border-start border-2{/if} mb-3">
                    <div class="card {if $comment.parent_id}bg-light{/if}">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex align-items-center mb-2">
                                        <strong class="me-2">#{$comment.id}</strong>
                                        <span class="me-2">
                                            {if $comment.user_id}
                                                {if $comment.user_display_name}
                                                    {$comment.user_display_name|escape} <i class="fa fa-registered text-success"></i>
                                                {elseif $comment.user_username}
                                                    {$comment.user_username|escape} <i class="fa fa-registered text-success"></i>
                                                {else}
                                                    Utente registrato
                                                {/if}
                                            {else}
                                                {if $comment.author_name}
                                                    {$comment.author_name|escape} <i class="fa fa-user text-muted"></i>
                                                {else}
                                                    Anonimo
                                                {/if}
                                            {/if}
                                        </span>
                                        {if $comment.status == 'approved'}<span class="badge bg-success me-2">Approved</span>{/if}
                                        {if $comment.status == 'pending'}<span class="badge bg-warning text-dark me-2">Pending</span>{/if}
                                        {if $comment.status == 'spam'}<span class="badge bg-danger me-2">Spam</span>{/if}
                                        <small class="text-muted">{$comment.created_at|date_format:"%d/%m/%Y %H:%M"}</small>
                                    </div>
                                    {if $comment.parent_id}
                                        <div class="mb-2">
                                            <small class="text-info">
                                                <i class="fa fa-reply"></i> In risposta a: 
                                                {if $comment.parent_author_name}
                                                    <strong>{$comment.parent_author_name|escape}</strong>
                                                {else}
                                                    commento #{$comment.parent_id}
                                                {/if}
                                            </small>
                                        </div>
                                    {/if}
                                    <div class="mb-2">
                                        <p class="mb-1">{$comment.content|escape|nl2br}</p>
                                        <small class="text-muted">Post: <strong>{$comment.post_title|escape}</strong></small>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        {if $comment.status != 'approved'}
                                            <form method="POST" action="/admin/comments/approve" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                                <input type="hidden" name="id" value="{$comment.id}">
                                                <button type="submit" class="btn btn-outline-success" title="Approve">
                                                    <i class="fa fa-check-circle"></i> Approve
                                                </button>
                                            </form>
                                        {/if}
                                        {if $comment.status != 'spam'}
                                            <form method="POST" action="/admin/comments/spam" class="d-inline">
                                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                                <input type="hidden" name="id" value="{$comment.id}">
                                                <button type="submit" class="btn btn-outline-warning" title="Mark as Spam">
                                                    <i class="fa fa-flag"></i> Spam
                                                </button>
                                            </form>
                                        {/if}
                                        {if $comment.status == 'approved'}
                                            <a href="/admin/comments/reply?id={$comment.id}" class="btn btn-outline-primary" title="Reply">
                                                <i class="fa fa-reply"></i> Reply
                                            </a>
                                        {/if}
                                        <form method="POST" action="/admin/comments/delete" class="d-inline"
                                              onsubmit="return confirm('Delete this comment?');">
                                            <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                            <input type="hidden" name="id" value="{$comment.id}">
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {* Display replies recursively *}
                {if $comment.replies}
                    {foreach $comment.replies as $reply}
                        {call displayComment comment=$reply level=$level+1}
                    {/foreach}
                {/if}
            {/function}
            
            {if $comments}
                {foreach $comments as $comment}
                    {call displayComment comment=$comment level=0}
                {/foreach}
            {else}
                <div class="alert alert-info mt-3">No comments found.</div>
            {/if}
        </div>
    {else}
        {* List View *}
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Email</th>
                    <th>Content</th>
                    <th>Post</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {if !$comments}
                <tr><td colspan="8" class="text-center text-muted py-4">No comments found.</td></tr>
                {/if}
                {foreach $comments as $comment}
                <tr>
                    <td>{$comment.id}</td>
                    <td>
                        {if $comment.user_id}
                            {* User is registered *}
                            {if $comment.user_display_name}
                                {$comment.user_display_name|escape} <i class="fa fa-registered text-success"></i>
                            {elseif $comment.user_username}
                                {$comment.user_username|escape} <i class="fa fa-registered text-success"></i>
                            {else}
                                Utente registrato
                            {/if}
                        {else}
                            {* User is guest *}
                            {if $comment.author_name}
                                {$comment.author_name|escape} <i class="fa fa-user text-muted"></i>
                            {else}
                                Anonimo
                            {/if}
                        {/if}
                    </td>
                    <td>
                        {if $comment.user_id}
                            {* User is registered - show user email *}
                            {if $comment.user_email}
                                {$comment.user_email|escape}
                            {else}
                                -
                            {/if}
                        {else}
                            {* User is guest - show author email *}
                            {if $comment.author_email}
                                {$comment.author_email|escape}
                            {else}
                                -
                            {/if}
                        {/if}
                    </td>
                    <td>{$comment.content|truncate:60|escape}</td>
                    <td>{$comment.post_title|escape}</td>
                    <td>
                        {if $comment.status == 'approved'}<span class="badge bg-success">Approved</span>{/if}
                        {if $comment.status == 'pending'}<span class="badge bg-warning text-dark">Pending</span>{/if}
                        {if $comment.status == 'spam'}<span class="badge bg-danger">Spam</span>{/if}
                    </td>
                    <td>{$comment.created_at|date_format:"%d/%m/%Y %H:%M"}</td>
                    <td>
                        {if $comment.status != 'approved'}
                            <form method="POST" action="/admin/comments/approve" class="d-inline">
                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                <input type="hidden" name="id" value="{$comment.id}">
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Approve"><i class="fa fa-check-circle"></i></button>
                            </form>
                        {/if}
                        {if $comment.status != 'spam'}
                            <form method="POST" action="/admin/comments/spam" class="d-inline">
                                <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                <input type="hidden" name="id" value="{$comment.id}">
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Mark as Spam"><i class="fa fa-flag"></i></button>
                            </form>
                        {/if}
                        {if $comment.status == 'approved'}
                            <a href="/admin/comments/reply?id={$comment.id}" class="btn btn-sm btn-outline-primary" title="Reply"><i class="fa fa-reply"></i></a>
                        {/if}
                        <form method="POST" action="/admin/comments/delete" class="d-inline" onsubmit="return confirm('Delete this comment?');">
                            <input type="hidden" name="csrf_token" value="{$csrf_token}">
                            <input type="hidden" name="id" value="{$comment.id}">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    {/if}
</div>
{/block}

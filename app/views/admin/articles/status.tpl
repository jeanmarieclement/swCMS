{* Change Article Status Template *}
{include file="admin/header.tpl" title=$title}

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-2 col-md-3 d-md-block bg-light sidebar collapse" id="sidebarMenu">
            <div class="position-sticky pt-3">
                {include file="admin/sidebar.tpl" active="articles"}
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="col-lg-10 col-md-9 ms-sm-auto px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Change Article Status</h1>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold 
                                {if $new_status == 'published'}text-success
                                {elseif $new_status == 'draft'}text-warning
                                {elseif $new_status == 'trash'}text-danger
                                {else}text-primary{/if}">
                                Confirm Status Change
                            </h6>
                        </div>
                        <div class="card-body">
                            {if isset($error)}
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i> {$error}
                                </div>
                            {else}
                                <div class="alert 
                                    {if $new_status == 'published'}alert-success
                                    {elseif $new_status == 'draft'}alert-warning
                                    {elseif $new_status == 'trash'}alert-danger
                                    {else}alert-info{/if}" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i> 
                                    Are you sure you want to change the status of this article to 
                                    <strong>
                                        {if $new_status == 'published'}Published
                                        {elseif $new_status == 'draft'}Draft
                                        {elseif $new_status == 'trash'}Trash
                                        {else}{$new_status|capitalize}{/if}
                                    </strong>?
                                </div>
                                
                                <div class="mb-4">
                                    <h5 class="font-weight-bold">{$article.title|escape}</h5>
                                    <p class="text-muted">
                                        <small>
                                            Current Status: 
                                            {if $article.status == 'published'}
                                                <span class="badge bg-success">Published</span>
                                            {elseif $article.status == 'draft'}
                                                <span class="badge bg-warning text-dark">Draft</span>
                                            {elseif $article.status == 'trash'}
                                                <span class="badge bg-danger">Trash</span>
                                            {else}
                                                <span class="badge bg-secondary">{$article.status|capitalize}</span>
                                            {/if}
                                            | Created: {$article.created_at|date_format:"%b %e, %Y at %H:%M"}
                                            {if $article.status == 'published' && isset($article.published_at)}
                                                | Published: {$article.published_at|date_format:"%b %e, %Y at %H:%M"}
                                            {/if}
                                        </small>
                                    </p>
                                    
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <p class="card-text">{$article.excerpt|truncate:200|escape}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                {if $new_status == 'trash'}
                                    <div class="alert alert-warning" role="alert">
                                        <i class="fas fa-info-circle me-2"></i> Moving to trash doesn't permanently delete the article. You can restore it later or permanently delete it from the trash.
                                    </div>
                                {elseif $new_status == 'published'}
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i> Publishing this article will make it visible to the public immediately.
                                    </div>
                                {/if}
                                
                                <div class="d-flex justify-content-between">
                                    <a href="{$site_url}/admin/articles" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Cancel
                                    </a>
                                    
                                    <form action="{$site_url}/admin/articles/status" method="post">
                                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                                        <input type="hidden" name="id" value="{$article.id}">
                                        <input type="hidden" name="status" value="{$new_status}">
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn 
                                            {if $new_status == 'published'}btn-success
                                            {elseif $new_status == 'draft'}btn-warning
                                            {elseif $new_status == 'trash'}btn-danger
                                            {else}btn-primary{/if}">
                                            <i class="fas 
                                                {if $new_status == 'published'}fa-check-circle
                                                {elseif $new_status == 'draft'}fa-file
                                                {elseif $new_status == 'trash'}fa-trash
                                                {else}fa-sync{/if} me-1"></i> 
                                            Confirm
                                        </button>
                                    </form>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{include file="admin/footer.tpl"}

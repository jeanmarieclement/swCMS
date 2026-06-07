{if isset($article.id) && isset($article.created_at)}
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Article Information</h6>
    </div>
    <div class="card-body">
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Created
                <span>{$article.created_at|date_format:"%b %e, %Y at %H:%M"}</span>
            </li>
            {if isset($article.updated_at)}
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Last Updated
                <span>{$article.updated_at|date_format:"%b %e, %Y at %H:%M"}</span>
            </li>
            {/if}
            {if isset($article.published_at) && $article.status == 'published'}
            <li class="list-group-item d-flex justify-content-between align-items-center">
                Published
                <span>{$article.published_at|date_format:"%b %e, %Y at %H:%M"}</span>
            </li>
            {/if}
        </ul>
    </div>
</div>
{/if}

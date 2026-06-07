{extends file="frontend/layout.tpl"}

{block name="content"}
<div class="row">
    <div class="col-md-8">
        {if isset(
            $homepage_page) && $homepage_page}
            <h1>{$homepage_page.title|escape}</h1>
            <div class="page-content">
                {$homepage_page.content nofilter}
            </div>
        {else}
            <h1>Latest Articles</h1>
            {* Loop through the latest_posts array and display each post as a Bootstrap card *}
            {if isset($latest_posts) && $latest_posts|@count > 0}
                {foreach from=$latest_posts item=post}
                    <div class="card mb-4">
                        {if $post.featured_image}
                            <img src="{$post.featured_image}" class="card-img-top" alt="{$post.title}">
                        {/if}
                        <div class="card-body">
                            <h2 class="card-title">
                                <a href="/article/{$post.slug}">{$post.title|escape}</a>
                            </h2>
                            <p class="card-text">{$post.excerpt|escape}</p>
                            <a href="/article/{$post.slug}" class="btn btn-primary">Read more</a>
                        </div>
                        <div class="card-footer text-muted">
                            Posted on {$post.published_at|date_format:"%d/%m/%Y"} by {$post.author|escape}
                        </div>
                    </div>
                {/foreach}
            {else}
                <div class="alert alert-info">No articles found.</div>
            {/if}
        {/if}
    </div>
    <div class="col-md-4">
        {* Sidebar with search and categories (placeholders for now) *}
        <div class="card mb-4">
            <div class="card-header">Search</div>
            <div class="card-body">
                <form method="get" action="/search">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search articles...">
                        <button class="btn btn-outline-secondary" type="submit">Go</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">Categories</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Category 1</li>
                <li class="list-group-item">Category 2</li>
                <li class="list-group-item">Category 3</li>
            </ul>
        </div>
    </div>
</div>
{/block}

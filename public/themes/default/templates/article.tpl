{* Template per visualizzare un articolo *}
{extends file="layout.tpl"}

{block name="title"}{$article.title|escape} - {$settings.SITE_NAME|default:"swCMS"}{/block}

{block name="description"}
{if isset($meta_description)}{$meta_description|escape}{else}{$article.title|escape}{/if}
{/block}

{block name="content"}
<article class="article-detail">
    <header class="article-header mb-4">
        <h1 class="article-title display-4 mb-3">{$article.title|escape}</h1>
        
        <div class="article-meta text-muted mb-3">
            <span class="author">
                <i class="fas fa-user"></i> 
                {if isset($article.author)}{$article.author|escape}{else}Admin{/if}
            </span>
            
            {if isset($article.published_at)}
            <span class="date ms-3">
                <i class="fas fa-calendar"></i> 
                {$article.published_at|date_format:"%d/%m/%Y alle %H:%M"}
            </span>
            {/if}
        </div>
        
        {* Categories *}
        {if isset($article.categories) && $article.categories|@count > 0}
        <div class="article-categories mb-3">
            <span class="text-muted">Categorie:</span>
            {foreach from=$article.categories item=category name=cats}
                <span class="badge bg-primary ms-1">{$category.name|escape}</span>
                {if !$smarty.foreach.cats.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </header>
    
    {* Featured Image *}
    {if isset($article.featured_image) && $article.featured_image}
    <div class="article-featured-image mb-4">
        <img src="{$article.featured_image|escape}" 
             alt="{$article.title|escape}" 
             class="img-fluid w-100 rounded shadow-sm"
             style="max-height: 400px; object-fit: cover;">
    </div>
    {/if}
    
    <div class="article-content">
        {$article.content nofilter}
    </div>
    
    {* Tags *}
    {if isset($article.tags) && $article.tags|@count > 0}
    <footer class="article-footer mt-5 pt-4 border-top">
        <div class="article-tags">
            <strong>Tags:</strong>
            {foreach from=$article.tags item=tag name=tags}
                <span class="badge bg-secondary me-1">{$tag.name|escape}</span>
            {/foreach}
        </div>
    </footer>
    {/if}
</article>

{* Comments Section *}
<div id="comments" class="comments-section mt-5">
    {include file="partials/comments_list.tpl"}
    {include file="partials/comment_form.tpl"}
</div>

{* Navigation or related articles could go here *}
<div class="article-navigation mt-5">
    <a href="{$settings.SITE_URL|default:'/'}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Torna alla home
    </a>
</div>
{/block}

{block name="footer_extra"}
<script>
// Eventuali script specifici per l'articolo
document.addEventListener('DOMContentLoaded', function() {
    // Esempio: Smooth scroll per i link interni
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>
{/block}
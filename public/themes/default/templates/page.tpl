{* Template per visualizzare una pagina statica *}
{extends file="layout.tpl"}

{block name="title"}{$page.title|escape} - {$settings.SITE_NAME|default:"swCMS"}{/block}

{block name="description"}
{if isset($meta_description)}{$meta_description|escape}{else}{$page.title|escape}{/if}
{/block}

{block name="content"}
<div class="page-detail">
    <header class="page-header mb-4">
        <h1 class="page-title display-4 mb-3">{$page.title|escape}</h1>
        
        {if isset($page.published_at)}
        <div class="page-meta text-muted mb-3">
            <span class="date">
                <i class="fas fa-calendar"></i> 
                Pubblicato il {$page.published_at|date_format:"%d/%m/%Y"}
            </span>
            
            {if isset($page.updated_at) && $page.updated_at != $page.published_at}
            <span class="updated ms-3">
                <i class="fas fa-edit"></i> 
                Aggiornato il {$page.updated_at|date_format:"%d/%m/%Y"}
            </span>
            {/if}
        </div>
        {/if}
    </header>
    
    <div class="page-content">
        {$page.content nofilter}
    </div>
    
    {* Optional page-specific information *}
    {if isset($page.author)}
    <footer class="page-footer mt-5 pt-4 border-top text-muted">
        <small>Pagina creata da: {$page.author|escape}</small>
    </footer>
    {/if}
</div>

{* Comments Section *}
<div id="comments" class="comments-section mt-5">
    {include file="partials/comments_list.tpl"}
    {include file="partials/comment_form.tpl"}
</div>

{* Navigation could go here *}
<div class="page-navigation mt-5">
    <a href="{$settings.SITE_URL|default:'/'}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Torna alla home
    </a>
</div>
{/block}

{block name="footer_extra"}
<script>
// Eventuali script specifici per la pagina
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
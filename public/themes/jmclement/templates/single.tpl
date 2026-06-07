{extends file="header.tpl"}
{block name="content"}
    <article class="single-article">
        <h1>{$article.title}</h1>
        <div class="meta">
            Pubblicato il {$article.date|date_format:"%d/%m/%Y"}
        </div>
        <div class="content">
            {$article.content nofilter}
        </div>
    </article>
{/block}
{include file="footer.tpl"}

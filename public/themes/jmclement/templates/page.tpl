{extends file="header.tpl"}
{block name="content"}
    <article class="single-article">
        <h1>{$page.title}</h1>
        <div class="content">
            {$page.content nofilter}
        </div>
    </article>
{/block}
{include file="footer.tpl"}

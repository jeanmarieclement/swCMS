{extends file="header.tpl"}
{block name="hero"}
    {include file="partials/hero.tpl"}
{/block}
{block name="content"}
    <section class="articles-list">
        {foreach $articles as $article}
            {include file="partials/article_card.tpl" article=$article}
        {/foreach}
    </section>
{/block}
{include file="footer.tpl"}

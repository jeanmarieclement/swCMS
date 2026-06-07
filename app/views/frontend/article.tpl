{* Visualizzazione articolo pubblico *}
{extends file="layout.tpl"}
{block name="content"}
    <h1>{$article.title|escape}</h1>
    <div>{$article.content nofilter}</div>
{/block}

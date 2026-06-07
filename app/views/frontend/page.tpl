{* Public static page display *}
{extends file="layout.tpl"}
{block name="content"}
    <h1>{$page.title|escape}</h1>
    <div>{$page.content nofilter}</div>
{/block}

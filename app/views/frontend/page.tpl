{* Public static page display *}
{extends file="frontend/layout.tpl"}
{block name="content"}
    <h1>{$page.title|escape}</h1>
    <div>{$page.content nofilter}</div>
    {if $comments_enabled}
        <div id="comments" class="comments-section mt-5">
            {include file="frontend/partials/comments_list.tpl"}
            {include file="frontend/partials/comment_form.tpl"}
        </div>
    {/if}
{/block}

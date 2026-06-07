{* admin/_menu_item.tpl *}
{* Ricorsivo: stampa una voce di menu e le sue eventuali sottovoci *}
<li class="nav-item{if isset($item.children) && $item.children|@count > 0} dropdown{/if}">
    <a class="nav-link{if isset($item.children) && $item.children|@count > 0} dropdown-toggle" href="#" data-bs-toggle="dropdown" role="button" aria-expanded="false"{else}" href="{$item.url|escape}"{/if}>
        {if $item.icon}<i class="{$item.icon|escape} me-2"></i>{/if}
        {$item.label|escape}
    </a>
    {if isset($item.children) && $item.children|@count > 0}
        <ul class="dropdown-menu">
            {foreach from=$item.children item=subitem}
                {include file="admin/_menu_item.tpl" item=$subitem}
            {/foreach}
        </ul>
    {/if}
</li>

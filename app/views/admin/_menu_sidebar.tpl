{* admin/_menu_sidebar.tpl *}
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        {foreach from=$admin_menu item=block}
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span>{$block.name|escape}</span>
            </h6>
            <ul class="nav flex-column{if $block@last} mb-2{/if}">
                {foreach from=$block.items item=item}
                    {include file="admin/_menu_item.tpl" item=$item}
                {/foreach}
            </ul>
        {/foreach}
    </div>
</nav>

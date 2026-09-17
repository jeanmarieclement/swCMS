{* admin/_menu_sidebar.tpl *}
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" aria-label="Menu principale amministratore">
    <div class="sidebar-sticky pt-3">
        {foreach from=$admin_menu item=block}
            {assign var="groupId" value="sidebar-group-`$block.id`"}
            <div class="sidebar-group mb-2">
                <h6 class="sidebar-heading mb-0 px-2">
                    <button class="sidebar-group-toggle btn d-flex justify-content-between align-items-center w-100 px-2 py-1 text-start text-uppercase text-muted border-0 bg-transparent fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{$groupId}"
                            aria-expanded="true"
                            aria-controls="{$groupId}"
                            id="heading-{$groupId}">
                        <span>{$block.name|escape}</span>
                        <i class="fas fa-chevron-down sidebar-group-icon" aria-hidden="true"></i>
                    </button>
                </h6>
                <div id="{$groupId}" class="sidebar-group-collapse collapse show" aria-labelledby="heading-{$groupId}" data-group-id="{$block.id}">
                    <ul class="nav flex-column">
                        {foreach from=$block.items item=item}
                            {include file="admin/_menu_item.tpl" item=$item}
                        {/foreach}
                    </ul>
                </div>
            </div>
        {/foreach}
    </div>
</nav>

<nav class="site-nav">
    <ul class="nav-menu">
        {foreach $header_menu as $item}
            <li class="nav-item{if $item.css_class} {$item.css_class}{/if}">
                <a href="{$item.url|escape}" 
                   target="{$item.target|default:'_self'}"
                   class="nav-link{if $item.active_page} active{/if}">
                    {$item.title|escape}
                </a>
                {if $item.children}
                    <ul class="nav-submenu">
                        {foreach $item.children as $child}
                            <li class="nav-subitem{if $child.css_class} {$child.css_class}{/if}">
                                <a href="{$child.url|escape}" 
                                   target="{$child.target|default:'_self'}"
                                   class="nav-sublink{if $child.active_page} active{/if}">
                                    {$child.title|escape}
                                </a>
                            </li>
                        {/foreach}
                    </ul>
                {/if}
            </li>
        {/foreach}
    </ul>
</nav>

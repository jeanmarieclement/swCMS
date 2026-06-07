{* Header partial per il tema Verso Marte - Mars & Space Inspired *}
<header class="site-header">
    <div class="container">
        <div class="site-title">
            <h1>
                <a href="{$settings.SITE_URL}/" style="color: inherit; text-decoration: none;">
                    <i class="fas fa-rocket"></i>
                    {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
                    <span class="mars-icon"></span>
                </a>
            </h1>
            {if isset($settings.site_description)}
                <p class="site-description">{$settings.site_description|escape}</p>
            {else}
                <p class="site-description">Il viaggio verso il pianeta rosso inizia qui</p>
            {/if}
        </div>
    </div>
    
    {* Main Navigation *}
    <nav class="main-nav">
        <div class="container">
            <ul class="nav-menu">
                {if isset($header_menu) && $header_menu|@count > 0}
                    {foreach from=$header_menu item=menuitem}
                        <li>
                            <a href="{$menuitem.url}" class="nav-link">
                                {if $menuitem.icon}
                                    <i class="{$menuitem.icon}"></i>
                                {/if}
                                {$menuitem.title|escape}
                            </a>
                        </li>
                    {/foreach}
                {else}
                    {* Default Mars-themed menu items *}
                    <li><a href="{$settings.SITE_URL}/" class="nav-link"><i class="fas fa-home"></i> Base Terra</a></li>
                    <li><a href="{$settings.SITE_URL}/articles" class="nav-link"><i class="fas fa-newspaper"></i> Missioni</a></li>
                    <li><a href="{$settings.SITE_URL}/categories" class="nav-link"><i class="fas fa-tags"></i> Settori</a></li>
                    <li><a href="{$settings.SITE_URL}/pages" class="nav-link"><i class="fas fa-scroll"></i> Documenti</a></li>
                    <li><a href="{$settings.SITE_URL}/about" class="nav-link"><i class="fas fa-user-astronaut"></i> Equipaggio</a></li>
                    <li><a href="{$settings.SITE_URL}/contact" class="nav-link"><i class="fas fa-satellite"></i> Contatto</a></li>
                {/if}
                
                {* User menu for logged in users *}
                {if isset($user) && $user}
                    <li class="nav-user-menu">
                        <a href="#" class="nav-link user-menu-toggle">
                            <i class="fas fa-user-astronaut"></i>
                            {$user.username|escape}
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        <ul class="user-submenu">
                            <li><a href="{$settings.SITE_URL}/admin" class="nav-link"><i class="fas fa-rocket"></i> Mission Control</a></li>
                            <li><a href="{$settings.SITE_URL}/profile" class="nav-link"><i class="fas fa-user"></i> Profilo</a></li>
                            <li><a href="{$settings.SITE_URL}/logout" class="nav-link"><i class="fas fa-sign-out-alt"></i> Disconnessione</a></li>
                        </ul>
                    </li>
                {else}
                    <li><a href="{$settings.SITE_URL}/login" class="nav-link"><i class="fas fa-sign-in-alt"></i> Accesso Base</a></li>
                {/if}
            </ul>
            
            {* Mobile menu toggle *}
            <button class="mobile-menu-toggle" type="button" data-toggle="mobile-menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </nav>
</header>

<style>
/* Mobile Navigation Styles */
.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    flex-direction: column;
    padding: 0.5rem;
    cursor: pointer;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

.hamburger-line {
    width: 25px;
    height: 3px;
    background: var(--starlight);
    margin: 2px 0;
    transition: 0.3s;
    border-radius: 2px;
}

/* User Menu Dropdown */
.nav-user-menu {
    position: relative;
}

.user-submenu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--space-surface);
    border: 1px solid var(--mars-red);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-mars);
    list-style: none;
    padding: 0;
    margin: 0;
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.nav-user-menu:hover .user-submenu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-submenu li {
    margin: 0;
}

.user-submenu .nav-link {
    display: block;
    padding: 0.75rem 1rem;
    border-radius: 0;
    margin: 0;
}

.user-submenu .nav-link:hover {
    background: var(--mars-gradient);
}

/* Mobile Styles */
@media (max-width: 768px) {
    .main-nav {
        position: relative;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    .nav-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--space-surface);
        flex-direction: column;
        align-items: stretch;
        padding: 1rem 0;
        border-top: 2px solid var(--mars-red);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 999;
    }
    
    .nav-menu.mobile-active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .nav-menu li {
        margin: 0;
    }
    
    .nav-menu .nav-link {
        padding: 1rem;
        margin: 0;
        border-radius: 0;
        border-bottom: 1px solid rgba(230, 230, 250, 0.1);
    }
    
    .user-submenu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        background: rgba(26, 26, 46, 0.7);
        border: none;
        box-shadow: none;
    }
    
    .mobile-menu-toggle.active .hamburger-line:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }
    
    .mobile-menu-toggle.active .hamburger-line:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-toggle.active .hamburger-line:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }
}
</style>
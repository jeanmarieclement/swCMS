{* Layout base per il tema Verso Marte - Mars & Space Inspired Theme *}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{block name="title"}{if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}{/block}</title>
    <meta name="description" content="{block name="description"}Esplora il viaggio verso Marte attraverso scienza, tecnologia e immaginazione{/block}">
    <meta name="keywords" content="{block name="keywords"}Marte, spazio, esplorazione spaziale, scienza, tecnologia{/block}">

    {* Canonical URL: set by the controllers that know the page's own address *}
    {if isset($canonical) && $canonical}
        <link rel="canonical" href="{$canonical|escape}">
        <meta property="og:url" content="{$canonical|escape}">
    {/if}
    
    {* CSS Dependencies *}
    <link rel="stylesheet" href="{$settings.SITE_URL}/themes/verso-marte/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    {* Font Awesome for Icons *}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {* Additional head content *}
    {block name="head_extra"}{/block}
    
    {* Favicon *}
    {if isset($settings.site_favicon) && $settings.site_favicon}
        <link rel="icon" type="image/x-icon" href="{$settings.site_favicon}">
    {else}
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚀</text></svg>">
    {/if}
</head>
<body>
    {* Site Header with Cosmic Theme *}
    <header class="site-header">
        <div class="container">
            <div class="site-title">
                <h1>
                    <i class="fas fa-rocket"></i>
                    {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
                    <span class="mars-icon"></span>
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
                    <li><a href="{$settings.SITE_URL}/" class="nav-link"><i class="fas fa-home"></i> Base Terra</a></li>
                    <li><a href="{$settings.SITE_URL}/articles" class="nav-link"><i class="fas fa-newspaper"></i> Missioni</a></li>
                    <li><a href="{$settings.SITE_URL}/categories" class="nav-link"><i class="fas fa-tags"></i> Settori</a></li>
                    <li><a href="{$settings.SITE_URL}/about" class="nav-link"><i class="fas fa-user-astronaut"></i> Equipaggio</a></li>
                    <li><a href="{$settings.SITE_URL}/contact" class="nav-link"><i class="fas fa-satellite"></i> Contatto</a></li>
                </ul>
            </div>
        </nav>
    </header>

    {* Main Content Container *}
    <div class="container">
        <div class="main-content">
            <main class="content-area">
                {* Flash Messages with Space Theme *}
                {if isset($flash_messages) && $flash_messages|@count > 0}
                    <div class="flash-messages">
                        {foreach from=$flash_messages item=message}
                            <div class="alert alert-{$message.type}">
                                <i class="fas fa-satellite-dish"></i>
                                {$message.message|escape}
                                <button type="button" class="alert-close" onclick="this.parentElement.style.display='none';">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        {/foreach}
                    </div>
                {/if}
                
                {* Hero Section for Special Pages *}
                {block name="hero_section"}{/block}
                
                {* Main Page Content *}
                {block name="content"}
                    <div class="welcome-mission">
                        <h2><i class="fas fa-space-shuttle"></i> Benvenuto nella Missione Mars</h2>
                        <p>Preparati per un viaggio epico verso il pianeta rosso. Qui troverai tutto quello che c'è da sapere sulla nostra missione verso Marte.</p>
                        <div class="mission-stats">
                            <div class="stat-item">
                                <i class="fas fa-globe-americas"></i>
                                <span>Distanza da Terra: 225 milioni km</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>Durata viaggio: 7-9 mesi</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-thermometer-half"></i>
                                <span>Temperatura media: -80°C</span>
                            </div>
                        </div>
                    </div>
                {/block}
            </main>
            
            {* Sidebar with Mars Theme *}
            <aside class="sidebar">
                {* Mission Control Widget *}
                <div class="widget">
                    <h3 class="widget-title"><i class="fas fa-satellite"></i> Mission Control</h3>
                    <div class="mission-status">
                        <p><strong>Status:</strong> <span class="status-active">Operativo</span></p>
                        <p><strong>Sol (giorno marziano):</strong> {$smarty.now|date_format:"%j"}</p>
                        <p><strong>Prossima finestra di lancio:</strong> 2026</p>
                    </div>
                </div>
                
                {* Recent Articles Widget *}
                {if isset($recent_articles) && $recent_articles}
                    <div class="widget">
                        <h3 class="widget-title"><i class="fas fa-rss"></i> Ultime Trasmissioni</h3>
                        <ul class="recent-articles">
                            {foreach from=$recent_articles item=article}
                                <li>
                                    <a href="{$settings.SITE_URL}/article/{$article.id}">
                                        <i class="fas fa-broadcast-tower"></i>
                                        {$article.title|escape}
                                    </a>
                                    <small>{$article.created_at|date_format:"%d/%m/%Y"}</small>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                
                {* Categories Widget *}
                {if isset($categories) && $categories}
                    <div class="widget">
                        <h3 class="widget-title"><i class="fas fa-project-diagram"></i> Settori Missione</h3>
                        <ul class="categories-list">
                            {foreach from=$categories item=category}
                                <li>
                                    <a href="{$settings.SITE_URL}/category/{$category.slug}">
                                        <i class="fas fa-rocket"></i>
                                        {$category.name|escape}
                                        <span class="post-count">({$category.post_count})</span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
                
                {* Mars Weather Widget *}
                <div class="widget">
                    <h3 class="widget-title"><i class="fas fa-cloud-sun"></i> Meteo su Marte</h3>
                    <div class="mars-weather">
                        <div class="weather-item">
                            <i class="fas fa-thermometer-half"></i>
                            <span>Temperatura: -63°C</span>
                        </div>
                        <div class="weather-item">
                            <i class="fas fa-wind"></i>
                            <span>Venti: 97 km/h</span>
                        </div>
                        <div class="weather-item">
                            <i class="fas fa-eye"></i>
                            <span>Visibilità: Tempesta di sabbia</span>
                        </div>
                        <div class="weather-item">
                            <i class="fas fa-compress-arrows-alt"></i>
                            <span>Pressione: 0.6 kPa</span>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {* Site Footer *}
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <p>
                    <i class="fas fa-rocket"></i>
                    &copy; {$smarty.now|date_format:"%Y"} {if isset($settings.site_title)}{$settings.site_title|escape}{else}Verso Marte{/if}
                    - Missione verso il pianeta rosso
                    <i class="fas fa-user-astronaut"></i>
                </p>
                <p class="mars-quote">
                    "Marte è lì, aspettando di essere raggiunto." - Buzz Aldrin
                </p>
            </div>
        </div>
    </footer>

    {* JavaScript Dependencies *}
    <script src="{$settings.SITE_URL}/vendor/jquery/jquery-3.7.1.min.js"></script>
    <script src="{$settings.SITE_URL}/themes/verso-marte/js/mars-theme.js"></script>
    
    {* Additional footer scripts *}
    {block name="footer_extra"}{/block}
    
    {* Loading indicator for Mars theme *}
    <div id="mars-loading" class="loading" style="display: none;">
        Caricamento trasmissione da Marte...
        <i class="fas fa-satellite fa-spin"></i>
    </div>
</body>
</html>
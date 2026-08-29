{* Base layout Smarty per il tema default - Blue Theme with Boxed Content *}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{block name="title"}{if isset($settings.site_title)}{$settings.site_title|escape}{else}swCMS{/if}{/block}</title>
    <meta name="description" content="{block name="description"}Un moderno CMS costruito con PHP{/block}">
    <meta name="keywords" content="{block name="keywords"}CMS, PHP, Content Management{/block}">

    {* Canonical URL: set by the controllers that know the page's own address *}
    {if isset($canonical) && $canonical}
        <link rel="canonical" href="{$canonical|escape}">
        <meta property="og:url" content="{$canonical|escape}">
    {/if}
    
    {* CSS Dependencies *}
    <link rel="stylesheet" href="{$settings.SITE_URL}/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{$settings.SITE_URL}/themes/default/css/style.css">
    
    {* Additional head content *}
    {block name="head_extra"}{/block}
    
    {* Favicon *}
    {if isset($settings.site_favicon) && $settings.site_favicon}
        <link rel="icon" type="image/x-icon" href="{$settings.site_favicon}">
    {/if}
</head>
<body>
    {* Fixed Navigation Header *}
    {include file="partials/header.tpl"}
    
    {* Main Content Container - Boxed Layout *}
    <div class="content-container">
        {* Hero Section (optional, can be overridden in templates) *}
        {block name="hero_section"}{/block}
        
        {* Main Content Area *}
        <main class="main-content">
            {* Flash Messages *}
            {if isset($flash_messages) && $flash_messages|@count > 0}
                <div class="flash-messages mb-4">
                    {foreach from=$flash_messages item=message}
                        <div class="alert alert-{$message.type} alert-dismissible fade show" role="alert">
                            {$message.message|escape}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    {/foreach}
                </div>
            {/if}
            
            {* Page Content Block *}
            {block name="content"}
                <div class="alert alert-info">
                    <h4>Benvenuto in swCMS!</h4>
                    <p>Il contenuto principale della pagina verrà visualizzato qui.</p>
                </div>
            {/block}
        </main>
    </div>
    
    {* Site Footer *}
    {include file="partials/footer.tpl"}
    
    {* JavaScript Dependencies *}
    <script src="{$settings.SITE_URL}/vendor/jquery/jquery-3.7.1.min.js"></script>
    <script src="{$settings.SITE_URL}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{$settings.SITE_URL}/themes/default/js/main.js"></script>
    
    {* Additional footer scripts *}
    {block name="footer_extra"}{/block}
</body>
</html>

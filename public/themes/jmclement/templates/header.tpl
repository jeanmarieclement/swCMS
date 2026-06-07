<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{$page_title|default:"Home"} - {$site_title|default:"jmclement"}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/themes/jmclement/css/custom.css">
    <script src="/themes/jmclement/js/main.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="{$base_url}" class="logo">
                <img src="/themes/jmclement/images/logo.png" alt="Logo" height="48">
            </a>
            {include file="partials/menu.tpl"}
        </div>
    </header>
    {block name="hero"}{/block}
    <main class="site-main container">

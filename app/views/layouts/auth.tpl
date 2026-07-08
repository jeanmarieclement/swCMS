<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{block name="title"}Authentication{/block} - {$site_name|escape}</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="{$site_url}/css/normalize.css">
    <link rel="stylesheet" href="{$site_url}/css/main.css">
    <link rel="stylesheet" href="{$site_url}/css/auth.css">
    
    <!-- Favicon -->
    <link rel="icon" href="{$site_url}/favicon.ico">
    
    {block name="head"}{/block}
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <header class="auth-header">
            <div class="container">
                <div class="logo">
                    <a href="{$site_url}">
                        <h1>{$site_name|escape}</h1>
                    </a>
                </div>
            </div>
        </header>
        
        <main class="auth-main">
            <div class="container">
                {block name="content"}{/block}
            </div>
        </main>
        
        <footer class="auth-footer">
            <div class="container">
                <p>&copy; {$smarty.now|date_format:"%Y"} {$site_name|escape}. All rights reserved.</p>
            </div>
        </footer>
    </div>
    
    <!-- JavaScript -->
    <script src="{$site_url}/js/main.js"></script>
    {block name="scripts"}{/block}
</body>
</html>

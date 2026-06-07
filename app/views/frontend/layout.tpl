{* Layout base per il frontend *}
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>{$title|default:'Homepage'} - swCMS</title>
    <link rel="stylesheet" href="{$site_url}/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="{$site_url}/css/frontend.css">
</head>
<body>
    {include file="../partials/header.tpl"}
    <main class="container mt-4">
        {block name="content"}{/block}
    </main>
    {include file="../partials/footer.tpl"}
    <script src="{$site_url}/vendor/jquery/jquery-3.7.1.min.js"></script>
    <script src="{$site_url}/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="{$site_url}/vendor/fontawesome/js/all.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="swCMS Admin Panel">
    <meta name="author" content="swCMS">
    <title>{block name="title"}Admin Panel{/block} | {$site_name|escape}</title>
    
    <!-- Bootstrap CSS -->
    <link href="{$site_url}/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="{$site_url}/vendor/fontawesome/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link href="{$site_url}/vendor/datatables/datatables.min.css" rel="stylesheet">
    
    <!-- Admin styles -->
    <link href="{$site_url}/css/admin.css" rel="stylesheet">
    
    <!-- Media Library styles -->
    <link href="{$site_url}/css/admin/media.css" rel="stylesheet">
    
    <!-- Select2 -->
    <link href="{$site_url}/vendor/select2/css/select2.min.css" rel="stylesheet" />

    <!-- Additional head content -->
    {block name="head"}{/block}
</head>
<body>
    <!-- Top Navigation -->
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="{$admin_url}">{$site_name|escape} Admin</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <input class="form-control form-control-dark w-100" type="text" placeholder="Search" aria-label="Search">
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <a class="nav-link px-3" href="{$site_url}" target="_blank">
                    <i class="fas fa-external-link-alt me-1"></i> View Site
                </a>
            </div>
        </div>
        <div class="navbar-nav">
            <div class="nav-item dropdown">
                <a class="nav-link dropdown-toggle px-3" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-1"></i> {if isset($smarty.session.user_email)}{$smarty.session.user_email}{else}Admin{/if}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{$admin_url}/profile"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="{$admin_url}/settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{$admin_url}/logout"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            {include file="admin/_menu_sidebar.tpl"}

                
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                {* Flash Message *}
                {if $flash}
                    {assign var="alertClass" value="info"}
                    {assign var="alertIcon" value="fas fa-info-circle"}
                    {if $flash.type == "success"}
                        {assign var="alertClass" value="success"}
                        {assign var="alertIcon" value="fas fa-check-circle"}
                    {/if}
                    {if $flash.type == "error"}
                        {assign var="alertClass" value="danger"}
                        {assign var="alertIcon" value="fas fa-exclamation-circle"}
                    {/if}
                    {if $flash.type == "warning"}
                        {assign var="alertClass" value="warning"}
                        {assign var="alertIcon" value="fas fa-exclamation-triangle"}
                    {/if}
                    <div class="alert alert-{$alertClass} alert-dismissible fade show mt-3" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="{$alertIcon} me-2"></i>
                            <div class="flex-grow-1">
                                <strong>
                                    {if $flash.type == "success"}Successo{elseif $flash.type == "error"}Errore{elseif $flash.type == "warning"}Attenzione{else}Informazione{/if}:
                                </strong>
                                {$flash.message|escape}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                {/if}
                {block name="breadcrumbs"}{/block}
                {block name="content"}{/block}
                
                <!-- Footer -->
                {block name="footer"}
                    {include file="admin/footer.tpl"}
                {/block}
            </main>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="{$site_url}/vendor/jquery/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="{$site_url}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="{$site_url}/vendor/datatables/datatables.min.js"></script>
    
    <!-- TinyMCE -->
    <script src="{$site_url}/vendor/tinymce/js/tinymce/tinymce.min.js"></script>
    <script src="{$site_url}/js/tinymce-init.js"></script>
    
    <!-- Admin scripts -->
    <script src="{$site_url}/js/admin.js"></script>
    
    <!-- Media Library scripts -->
    <script src="{$site_url}/js/admin/media.js"></script>

    <!-- Font Awesome -->
    <script src="{$site_url}/vendor/fontawesome/js/all.js"></script>
    
    <!-- Select2 -->
    <script src="{$site_url}/vendor/select2/js/select2.min.js"></script>
    
    
    <!-- Custom scripts -->
    {block name="scripts"}{/block}
    
    <script>
        // Set active menu item based on current URL
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && href === '{$site_url}' + currentPath) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>

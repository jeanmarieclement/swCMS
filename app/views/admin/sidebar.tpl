<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Content Management</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {if $active == 'dashboard'}active{/if}" href="{$site_url}/admin">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'articles'}active{/if}" href="{$site_url}/admin/articles">
                    <i class="fas fa-newspaper me-2"></i> Articles
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'pages'}active{/if}" href="{$site_url}/admin/pages">
                    <i class="fas fa-file-alt me-2"></i> Pages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'categories'}active{/if}" href="{$site_url}/admin/categories">
                    <i class="fas fa-folder me-2"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'tags'}active{/if}" href="{$site_url}/admin/tags">
                    <i class="fas fa-tags me-2"></i> Tags
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'comments'}active{/if}" href="{$site_url}/admin/comments">
                    <i class="fas fa-comments me-2"></i> Comments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'media'}active{/if}" href="{$site_url}/admin/media">
                    <i class="fas fa-images me-2"></i> Media Library
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>User Management</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {if $active == 'users'}active{/if}" href="{$site_url}/admin/users">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'roles'}active{/if}" href="{$site_url}/admin/roles">
                    <i class="fas fa-user-tag me-2"></i> Roles & Permissions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'profile'}active{/if}" href="{$site_url}/admin/profile">
                    <i class="fas fa-user-circle me-2"></i> Your Profile
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Appearance</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {if $active == 'themes'}active{/if}" href="{$site_url}/admin/themes">
                    <i class="fas fa-palette me-2"></i> Themes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'menus'}active{/if}" href="{$site_url}/admin/menus">
                    <i class="fas fa-bars me-2"></i> Menus
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'widgets'}active{/if}" href="{$site_url}/admin/widgets">
                    <i class="fas fa-th-large me-2"></i> Widgets
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Extensions</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {if $active == 'plugins'}active{/if}" href="{$site_url}/admin/plugins">
                    <i class="fas fa-puzzle-piece me-2"></i> Plugins
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>System</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {if $active == 'settings'}active{/if}" href="{$site_url}/admin/settings">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'tools'}active{/if}" href="{$site_url}/admin/tools">
                    <i class="fas fa-tools me-2"></i> Tools
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {if $active == 'logs'}active{/if}" href="{$site_url}/admin/logs">
                    <i class="fas fa-clipboard-list me-2"></i> Logs
                </a>
            </li>
        </ul>
    </div>
</nav>

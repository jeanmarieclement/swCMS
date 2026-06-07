{* Header partial - Fixed Navigation *}
<header>
  <nav class="navbar navbar-expand-lg navbar-fixed">
    <div class="container">
      {* Logo on the left *}
      <a class="navbar-brand" href="/">
        {if isset($settings.site_logo) && $settings.site_logo}
          <img src="{$settings.site_logo}" alt="{$settings.site_title|escape}" height="30" class="d-inline-block align-text-top me-2">
        {/if}
        {$settings.site_title|escape}
      </a>
      
      {* Mobile toggle button *}
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
              aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      {* Navigation menu - centered *}
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          {if isset($header_menu) && $header_menu|@count > 0}
            {foreach from=$header_menu item=menuitem}
              <li class="nav-item">
                <a class="nav-link" href="{$menuitem.url}">{$menuitem.title|escape}</a>
              </li>
            {/foreach}
          {else}
            {* Default menu items if no menu is configured *}
            <li class="nav-item">
              <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/articles">Articoli</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/pages">Pagine</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/contact">Contatti</a>
            </li>
          {/if}
        </ul>
        
        {* Optional right-side elements (search, user menu, etc.) *}
        <div class="navbar-nav ms-auto">
          {if isset($user) && $user}
            <div class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Benvenuto, {$user.username|escape}
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/admin">Dashboard</a></li>
                <li><a class="dropdown-item" href="/logout">Logout</a></li>
              </ul>
            </div>
          {else}
            <li class="nav-item">
              <a class="nav-link" href="/login">Login</a>
            </li>
          {/if}
        </div>
      </div>
    </div>
  </nav>
</header>

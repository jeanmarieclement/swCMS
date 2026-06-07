{extends file="layout.tpl"}

{block name="title"}
  {if isset($homepage_page) && $homepage_page && is_array($homepage_page)}
    {$homepage_page.title|escape} - {$settings.site_title|escape}
  {else}
    Home - {$settings.site_title|escape}
  {/if}
{/block}

{block name="description"}
  {if isset($homepage_page) && $homepage_page && is_array($homepage_page) && isset($homepage_page.excerpt)}
    {$homepage_page.excerpt|escape}
  {else}
    Benvenuto su {$settings.site_title|escape} - Il tuo portale di contenuti
  {/if}
{/block}

{block name="hero_section"}
  {if isset($homepage_page) && $homepage_page && is_array($homepage_page)}
    {* Hero section for custom homepage *}
    <div class="hero-section">
      <h1 class="hero-title">{$homepage_page.title|escape}</h1>
      {if isset($homepage_page.excerpt) && $homepage_page.excerpt}
        <p class="hero-subtitle">{$homepage_page.excerpt|escape}</p>
      {/if}
    </div>
  {else}
    {* Default hero section for article listing *}
    <div class="hero-section">
      <h1 class="hero-title">Benvenuto su {$settings.site_title|escape}</h1>
      <p class="hero-subtitle">Scopri i nostri ultimi articoli e contenuti</p>
    </div>
  {/if}
{/block}

{block name="content"}
  {if isset($homepage_page) && $homepage_page && is_array($homepage_page)}
    {* Custom homepage content *}
    <div class="homepage-content">
      <div class="content-wrapper">
        {$homepage_page.content nofilter}
      </div>
      
      {* Optional: Show recent articles below homepage content *}
      {if isset($show_recent_articles) && $show_recent_articles && isset($latest_articles) && $latest_articles|@count > 0}
        <div class="recent-articles-section mt-5">
          <h2 class="section-title text-center mb-4">Articoli Recenti</h2>
          <div class="articles-grid">
            {foreach from=$latest_articles item=article name=recent_loop}
              {if $smarty.foreach.recent_loop.index < 6}{* Limit to 6 recent articles *}
                <article class="article-card">
                  {if isset($article.featured_image) && $article.featured_image}
                    <img src="{$article.featured_image}" alt="{$article.title|escape}" loading="lazy">
                  {elseif isset($article.image) && $article.image}
                    <img src="{$article.image}" alt="{$article.title|escape}" loading="lazy">
                  {/if}
                  <div class="article-card-body">
                    <h3 class="article-card-title">

                      <a href="{if isset($article.slug)}/article/{$article.slug}{else}/article/{$article.id}{/if}">
                        {$article.title|escape}
                      </a>
                    </h3>
                    {if isset($article.excerpt) && $article.excerpt}
                      <p class="article-card-text">{$article.excerpt|truncate:190:"..."|escape}</p>
                    {/if}
                    {if isset($article.created_at) || isset($article.author)}
                      <div class="article-meta">
                        {if isset($article.author)}
                          <span class="author">di {$article.author|escape}</span>
                        {/if}
                        {if isset($article.created_at)}
                          {if isset($article.author)} · {/if}
                          <time datetime="{$article.created_at}">{$article.created_at|date_format:'%d/%m/%Y'}</time>
                        {/if}
                      </div>
                    {/if}
                  </div>
                </article>
              {/if}
            {/foreach}
          </div>
          <div class="text-center mt-4">
            <a href="/articles" class="btn btn-outline-primary">Vedi tutti gli articoli</a>
          </div>
        </div>
      {/if}
    </div>
    
  {elseif isset($latest_articles) && $latest_articles|@count > 0}
    {* Article listing page *}
    <div class="articles-listing">
      <div class="articles-grid">
        {foreach from=$latest_articles item=article}
          <article class="article-card">
            {if isset($article.featured_image) && $article.featured_image}
              <img src="{$article.featured_image}" alt="{$article.title|escape}" loading="lazy">
            {elseif isset($article.image) && $article.image}
              <img src="{$article.image}" alt="{$article.title|escape}" loading="lazy">
            {/if}
            <div class="article-card-body">
              <h2 class="article-card-title">
                <a href="{if isset($article.slug)}/article/{$article.slug}{else}/article/{$article.id}{/if}">
                  {$article.title|escape}
                </a>
              </h2>
              {if isset($article.excerpt) && $article.excerpt}
                <p class="article-card-text">{$article.excerpt|truncate:190:"..."|escape}</p>
              {/if}
              {if isset($article.created_at) || isset($article.author) || isset($article.category)}
                <div class="article-meta">
                  {if isset($article.author)}
                    <span class="author">di {$article.author|escape}</span>
                  {/if}
                  {if isset($article.category)}
                    {if isset($article.author)} · {/if}
                    <span class="category">in <a href="/category/{$article.category.slug}">{$article.category.name|escape}</a></span>
                  {/if}
                  {if isset($article.created_at)}
                    {if isset($article.author) || isset($article.category)} · {/if}
                    <time datetime="{$article.created_at}">{$article.created_at|date_format:'%d/%m/%Y'}</time>
                  {/if}
                </div>
              {/if}
            </div>
          </article>
        {/foreach}
      </div>
      
      {* Pagination *}
      {if isset($pagination) && ($pagination.total_pages > 1)}
        <nav aria-label="Navigazione pagine" class="mt-5">
          <ul class="pagination justify-content-center">
            {if $pagination.current_page > 1}
              <li class="page-item">
                <a class="page-link" href="?page={$pagination.current_page-1}" aria-label="Precedente">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            {/if}
            
            {for $page=1 to $pagination.total_pages}
              {if $page == $pagination.current_page}
                <li class="page-item active">
                  <span class="page-link">{$page}</span>
                </li>
              {elseif $page <= 3 || $page > $pagination.total_pages-3 || ($page >= $pagination.current_page-2 && $page <= $pagination.current_page+2)}
                <li class="page-item">
                  <a class="page-link" href="?page={$page}">{$page}</a>
                </li>
              {elseif $page == 4 || $page == $pagination.total_pages-3}
                <li class="page-item disabled">
                  <span class="page-link">...</span>
                </li>
              {/if}
            {/for}
            
            {if $pagination.current_page < $pagination.total_pages}
              <li class="page-item">
                <a class="page-link" href="?page={$pagination.current_page+1}" aria-label="Successivo">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            {/if}
          </ul>
        </nav>
      {/if}
    </div>
    
  {else}
    {* No content available *}
    <div class="no-content-message text-center py-5">
      <div class="alert alert-info">
        <h4 class="alert-heading">Benvenuto!</h4>
        <p class="mb-0">Non ci sono ancora contenuti disponibili. Torna presto per scoprire nuovi articoli!</p>
      </div>
    </div>
  {/if}
{/block}

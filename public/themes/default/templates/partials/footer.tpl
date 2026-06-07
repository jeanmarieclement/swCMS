{* Footer partial - Blue Theme *}
<footer class="site-footer">
  <div class="container">
    <div class="row">
      <div class="col-md-6 text-center text-md-start">
        <p class="mb-0">
          &copy; {$smarty.now|date_format:"%Y"} 
          {if isset($settings.site_title) && $settings.site_title}
            {$settings.site_title|escape}
          {else}
            swCMS
          {/if}
        </p>
      </div>
      <div class="col-md-6 text-center text-md-end">
        <p class="mb-0">
          Powered by <strong>swCMS</strong>
        </p>
      </div>
    </div>
  </div>
</footer>

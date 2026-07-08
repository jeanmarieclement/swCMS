{extends file="admin/layout.tpl"}

{block name="title"}Visualizza Media{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/media">Media Library</a></li>
        <li class="breadcrumb-item active">Dettagli Media</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card mt-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{$media->title|escape}</h5>
                    <span class="badge bg-secondary">{$media->filetype|escape}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3 text-center">
                        {if $media->filetype|substr:0:6 == 'image/'}
                            <img src="{$site_url}/uploads/media/{$media->filepath}{$media->filename}" class="img-fluid rounded" alt="{$media->alt_text|escape}">
                        {elseif $media->filetype|substr:0:6 == 'video/'}
                            <video controls class="w-100 rounded">
                                <source src="{$site_url}/uploads/media/{$media->filepath}{$media->filename}" type="{$media->filetype|escape}">
                            </video>
                        {elseif $media->filetype|substr:0:6 == 'audio/'}
                            <audio controls class="w-100">
                                <source src="{$site_url}/uploads/media/{$media->filepath}{$media->filename}" type="{$media->filetype|escape}">
                            </audio>
                        {else}
                            <i class="fas fa-file fa-4x text-muted"></i>
                        {/if}
                    </div>
                    <dl class="row small">
                        <dt class="col-sm-4">Nome file</dt>
                        <dd class="col-sm-8">{$media->filename|escape}</dd>
                        <dt class="col-sm-4">Descrizione</dt>
                        <dd class="col-sm-8">{$media->description|escape|default:'—'}</dd>
                        <dt class="col-sm-4">Testo alternativo</dt>
                        <dd class="col-sm-8">{$media->alt_text|escape|default:'—'}</dd>
                        <dt class="col-sm-4">URL</dt>
                        <dd class="col-sm-8"><code>{$site_url}/uploads/media/{$media->filepath}{$media->filename}</code></dd>
                    </dl>
                    <a href="{$site_url}/admin/media/edit/{$media->id}" class="btn btn-primary">Modifica</a>
                    <a href="{$site_url}/admin/media" class="btn btn-secondary ms-2">Torna alla libreria</a>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

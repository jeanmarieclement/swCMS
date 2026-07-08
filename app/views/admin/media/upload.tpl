{extends file="admin/layout.tpl"}

{block name="title"}Carica Media{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/media">Media Library</a></li>
        <li class="breadcrumb-item active">Carica Media</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Carica nuovi file</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{$site_url}/admin/media/upload" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="{$csrf_token}">
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="files[]" class="form-control" multiple required>
                            <div class="form-text">
                                Formati consentiti: immagini (jpg, png, gif, webp), documenti (pdf, doc, xls, ppt, txt, csv),
                                video (mp4, webm, ogv), audio (mp3, wav, ogg). Max 50MB per file.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrizione</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Testo alternativo (alt)</label>
                            <input type="text" name="alt_text" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Carica</button>
                        <a href="{$site_url}/admin/media" class="btn btn-secondary ms-2">Annulla</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

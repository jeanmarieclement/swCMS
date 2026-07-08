{extends file="admin/layout.tpl"}

{block name="title"}Edit Media{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{$admin_url}/media">Media Library</a></li>
        <li class="breadcrumb-item active">Edit Media</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Modifica Media</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{$site_url}/admin/media/update/{$media->id}" id="media-edit-form">
                        {$csrf_field}
                        <div class="mb-3">
                            <label class="form-label">Titolo</label>
                            <input type="text" name="title" class="form-control" value="{$media->title|escape}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descrizione</label>
                            <textarea name="description" class="form-control" rows="3">{$media->description|escape}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Testo alternativo (alt)</label>
                            <input type="text" name="alt_text" class="form-control" value="{$media->alt_text|escape}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Anteprima</label><br>
                            {if $media->filetype|substr:0:6 == 'image/'}
                                <img src="{$site_url}/uploads/media/{$media->filepath}{$media->filename}" class="img-fluid rounded" style="max-width:200px;">
                            {else}
                                <span class="badge bg-secondary">{$media->filetype}</span>
                            {/if}
                        </div>
                        <button type="submit" class="btn btn-primary">Salva modifiche</button>
<a href="{$site_url}/admin/media" class="btn btn-secondary ms-2">Annulla</a>
<button type="button" class="btn btn-danger float-end" id="btn-delete-media" data-id="{$media->id}" data-title="{$media->title}">Elimina</button>

<!-- Modal conferma eliminazione -->
<div class="modal fade" id="deleteMediaModal" tabindex="-1" aria-labelledby="deleteMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMediaModalLabel">Conferma eliminazione</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                Sei sicuro di voler eliminare il media <strong>{$media->title|escape}</strong>?<br>
                Questa azione non può essere annullata.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-media" data-id="{$media->id}">Elimina</button>
            </div>
        </div>
    </div>
</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
{/block}

{block name="scripts"}
<script>
$(document).on('click', '#btn-delete-media', function(e) {
    e.preventDefault();
    $('#deleteMediaModal').modal('show');
});

$(document).on('click', '#confirm-delete-media', function(e) {
    var mediaId = $(this).data('id');
    var modal = $('#deleteMediaModal');
    $.ajax({
        url: '{$site_url}/admin/media/delete/' + mediaId,
        type: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        data: {
            csrf_token: '{$csrf_token}'
        },
        success: function(resp) {
            if (resp.success) {
                modal.modal('hide');
                showToast('Media eliminato con successo', 'success');
                setTimeout(function() {
                    window.location.href = '{$site_url}/admin/media';
                }, 1000);
            } else {
                showToast(resp.message || 'Errore durante l\'eliminazione', 'danger');
            }
        },
        error: function(xhr) {
            showToast('Errore durante l\'eliminazione', 'danger');
        }
    });
});
</script>
{/block}


{extends file="admin/layout.tpl"}

{block name="content"}
<div class="container-fluid">
    {if isset($smarty.get.notfound) && $smarty.get.notfound == 1}
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Media not found.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    {/if}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Media Library</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-upload me-2"></i>Upload File
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-2">
                <div class="card-header">
                    <h6 class="mb-0">Filters</h6>
                </div>
                <div class="card-body">
                    <form id="media-filter-form" method="get" action="{$site_url}/admin/media">
                        <div class="row">
                            <div class="mb-3 col-md-4">
                                <label for="search" class="form-label">Cerca</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    value="{$filters.search|default:''}" placeholder="Cerca...">
                            </div>
                            <div class="mb-3 col-md-4">
                                <label for="type" class="form-label">Tipo di file</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">Tutti i tipi</option>
                                    <option value="image" {if $filters.type == 'image'}selected{/if}>Immagini</option>
                                    <option value="document" {if $filters.type == 'document'}selected{/if}>Documenti
                                    </option>
                                    <option value="video" {if $filters.type == 'video'}selected{/if}>Video</option>
                                    <option value="audio" {if $filters.type == 'audio'}selected{/if}>Audio</option>
                                </select>
                            </div>
                            <div class="col-md-4 pt-4">
                                <button type="submit" class="btn btn-primary w-100">Applica filtri</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    {if !empty($items)}
                        <div class="row g-3" id="media-grid">
                            {foreach $items as $item}
                                <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
                                    <div class="card media-item" data-id="{$item->id}">
                                        <div class="ratio ratio-1x1 bg-light">
                                            {if $item->filetype|substr:0:6 == 'image/'}
                                                <img src="{$site_url}/uploads/media/{$item->filepath}thumbs/{$item->filename}" 
                                                     class="card-img-top" 
                                                     style="object-fit: cover;"
                                                     alt="{$item->alt_text}"
                                                     onerror="this.onerror=null; this.src='{$site_url}/uploads/media/{$item->filepath}{$item->filename}'; this.style.objectFit='contain'; this.style.padding='10px';">
                                            {else}
                                                <div class="d-flex align-items-center justify-content-center h-100">
                                                    <i class="fas fa-file fa-3x text-muted"></i>
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="small text-truncate" title="{$item->title}">
                                                {$item->title}
                                            </div>
                                            <div class="text-muted small">
                                                {($item->filesize / 1024)|number_format:1} KB
                                            </div>
                                            <div class="d-flex justify-content-end gap-1 mt-2">
                                                <a href="{$site_url}/admin/media/edit/{$item->id}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger btn-delete-media" data-id="{$item->id}" data-title="{$item->title}" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Delete confirmation modal -->
                                <div class="modal fade" id="deleteMediaModal-{$item->id}" tabindex="-1" aria-labelledby="deleteMediaModalLabel-{$item->id}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteMediaModalLabel-{$item->id}">Confirm Deletion</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete the media <strong>{$item->title|escape}</strong>?<br>
                                                This action cannot be undone.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-danger confirm-delete-media" data-id="{$item->id}">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        {if $totalPages > 1}
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    {if $page > 1}
                                        <li class="page-item">
                                            <a class="page-link" href="?page={$page-1}{if $filters.search}&search={$filters.search}{/if}{if $filters.type}&type={$filters.type}{/if}">
                                                &laquo; Previous
                                            </a>
                                        </li>
                                    {/if}
                                    
                                    {for $p=1 to $totalPages}
                                        <li class="page-item {if $p == $page}active{/if}">
                                            <a class="page-link" href="?page={$p}{if $filters.search}&search={$filters.search}{/if}{if $filters.type}&type={$filters.type}{/if}">
                                                {$p}
                                            </a>
                                        </li>
                                    {/for}
                                    
                                    {if $page < $totalPages}
                                        <li class="page-item">
                                            <a class="page-link" href="?page={$page+1}{if $filters.search}&search={$filters.search}{/if}{if $filters.type}&type={$filters.type}{/if}">
                                                Successiva &raquo;
                                            </a>
                                        </li>
                                    {/if}
                                </ul>
                            </nav>
                        {/if}
                    {else}
                        <div class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nessun file trovato</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-2"></i>Carica il tuo primo file
                            </button>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal per il caricamento file -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="upload-form" action="{$site_url}/admin/media/upload" method="post" enctype="multipart/form-data">
                {App\Helpers\SecurityHelper::csrf_field()}
                <div class="modal-header">
                    <h5 class="modal-title">Carica file</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <div class="upload-area p-5 text-center border rounded-3 mb-4">
                        <input type="file" name="files[]" id="file-input" class="d-none" multiple>
                        <div id="drop-area">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Trascina i file qui</h5>
                            <p class="text-muted">oppure</p>
                            <button type="button" class="btn btn-outline-primary" id="browse-btn">
                                Seleziona file
                            </button>
                            <p class="small text-muted mt-2">Dimensioni massime: 50MB</p>
                        </div>
                        <div id="file-list" class="mt-3 text-start d-none">
                            <h6>File selezionati:</h6>
                            <ul class="list-group" id="selected-files"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-primary" id="upload-btn" disabled>
                        <i class="fas fa-upload me-2"></i>Upload File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template per i file selezionati -->
<template id="file-item-template">
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <div class="file-info">
            <i class="fas fa-file me-2"></i>
            <span class="file-name"></span>
            <small class="text-muted file-size ms-2"></small>
        </div>
        <button type="button" class="btn-close btn-remove-file" aria-label="Rimuovi"></button>
    </li>
</template>

{/block}

{block name="scripts"}
<script>
$(document).ready(function() {
    
    // Delete media via AJAX
    $('body').on('click', '.btn-delete-media', function(e) {
        e.preventDefault();
        console.log('Deleting media with ID: ' + $(this).data('id'));
        var mediaId = $(this).data('id');
        $('#deleteMediaModal-' + mediaId).modal('show');
    });

    // Delete media via AJAX
    $('body').on('click', '.confirm-delete-media', function(e) {
        var mediaId = $(this).data('id');
        var modal = $('#deleteMediaModal-' + mediaId);
        $.ajax({
            url: '{$site_url}/admin/media/delete/' + mediaId,
            type: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: {
                csrf_token: '{App\Helpers\SecurityHelper::csrf_token()}'
            },
            success: function(resp) {
                if (resp.success) {
                    $('.media-item[data-id="' + mediaId + '"]').parent().remove();
                    modal.modal('hide');
                    showToast('Media deleted successfully', 'success');
                } else {
                    showToast(resp.message || 'Error during deletion', 'danger');
                }
            },
            error: function(xhr) {
                showToast('Error during deletion', 'danger');
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Disabilita il click sulle card media-item per evitare caricamenti view o redirect
    document.querySelectorAll('.media-item').forEach(function(card) {
        card.style.cursor = 'default';
        card.addEventListener('click', function(e) {
            // Permetti click su pulsanti/link interni
            if (
                e.target.closest('.btn-delete-media') ||
                e.target.closest('.confirm-delete-media') ||
                e.target.closest('a')
            ) {
                // Non bloccare il click su questi elementi
                return;
            }
            e.preventDefault();
            e.stopPropagation();
        });
    });
    const dropArea = document.getElementById('drop-area');
    const fileInput = document.getElementById('file-input');
    const browseBtn = document.getElementById('browse-btn');
    const fileList = document.getElementById('file-list');
    const selectedFiles = document.getElementById('selected-files');
    const uploadForm = document.getElementById('upload-form');
    const uploadBtn = document.getElementById('upload-btn');
    const fileItemTemplate = document.getElementById('file-item-template');
    let files = [];

    // Gestione drag & drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight() {
        dropArea.classList.add('border-primary', 'bg-light');
    }

    function unhighlight() {
        dropArea.classList.remove('border-primary', 'bg-light');
    }

    dropArea.addEventListener('drop', handleDrop, false);
    browseBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', handleFiles);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const droppedFiles = dt.files;
        handleFiles({ target: { files: droppedFiles } });
    }

    function handleFiles(e) {
        const newFiles = Array.from(e.target.files);
        files = [...files, ...newFiles];
        updateFileList();
        fileInput.value = ''; // Reset per permettere di selezionare nuovamente lo stesso file
    }

    function updateFileList() {
        selectedFiles.innerHTML = '';
        
        if (files.length === 0) {
            fileList.classList.add('d-none');
            uploadBtn.disabled = true;
            return;
        }
        
        fileList.classList.remove('d-none');
        uploadBtn.disabled = false;
        
        files.forEach((file, index) => {
            const fileItem = fileItemTemplate.content.cloneNode(true);
            const fileName = fileItem.querySelector('.file-name');
            const fileSize = fileItem.querySelector('.file-size');
            const removeBtn = fileItem.querySelector('.btn-remove-file');
            
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            removeBtn.addEventListener('click', () => {
                files.splice(index, 1);
                updateFileList();
            });
            
            selectedFiles.appendChild(fileItem);
        });
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    // Gestione invio del form
    uploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (files.length === 0) {
            alert('Seleziona almeno un file da caricare');
            return;
        }
        
        const formData = new FormData();
        files.forEach(file => {
            formData.append('files[]', file);
        });
        // Add CSRF token
        formData.append('csrf_token', '{App\Helpers\SecurityHelper::csrf_token()}');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                console.log(percentComplete + '% uploaded');
                // Qui puoi aggiungere una barra di avanzamento se necessario
            }
        };
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    // Close modal
                    var uploadModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('uploadModal'));
                    uploadModal.hide();
                    showToast('Upload completed!', 'success');
                    // Optionally update media grid dynamically
                    if (response.files && Array.isArray(response.files)) {
                        response.files.forEach(function(item) {
                            addMediaItemToGrid(item);
                        });
                    }
                    files = [];
                    updateFileList();
                } else {
                    showToast('Upload error: ' + (response.message || 'Unknown error'), 'danger');
                }
            } else {
                showToast('Upload error: server or network problem', 'danger');
            }
        };
        
        xhr.send(formData);
    });
    
    // Chiudi la modale dopo il caricamento
    const uploadModal = document.getElementById('uploadModal');
    uploadModal.addEventListener('hidden.bs.modal', function () {
        files = [];
        updateFileList();
    });
});
// Toast notification (Bootstrap 5)
function showToast(msg, type) {
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.position = 'fixed';
        toastContainer.style.top = '1rem';
        toastContainer.style.right = '1rem';
        toastContainer.style.zIndex = '11000';
        document.body.appendChild(toastContainer);
    }
    const toast = document.createElement('div');
    toast.className = 'alert alert-' + type + ' alert-dismissible fade show';
    toast.role = 'alert';
    toast.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    toastContainer.appendChild(toast);
    setTimeout(function() {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 4000);
}

// Optionally add new media item to grid (minimal, extend as needed)
function addMediaItemToGrid(item) {
    var grid = document.getElementById('media-grid');
    if (!grid || !item) return;
    var col = document.createElement('div');
    col.className = 'col-6 col-sm-4 col-lg-3 col-xl-2';
    var html = '';
    html += '<div class="card media-item" data-id="' + item.id + '">';
    html +=   '<div class="ratio ratio-1x1 bg-light">';
    if (item.filetype && item.filetype.indexOf('image/') === 0) {
        // Use thumbnail version for images with fallback to original
        var thumbPath = item.filepath;
        if (thumbPath.charAt(0) !== '/') {
            thumbPath = '/' + thumbPath;
        }
        var originalPath = thumbPath + item.filename;
        thumbPath += 'thumbs/' + item.filename;
        console.log(thumbPath);
        html += '<img src="' + thumbPath + '" class="card-img-top" style="object-fit: cover;" alt="' + (item.alt_text || '') + '" onerror="this.onerror=null; this.src=\'' + originalPath + '\'; this.style.objectFit=\'contain\'; this.style.padding=\'10px\';">';
    } else {
        html += '<div class="d-flex align-items-center justify-content-center h-100"><i class="fas fa-file fa-3x text-muted"></i></div>';
    }
    html +=   '</div>';
    html +=   '<div class="card-body p-2">';
    html +=     '<div class="small text-truncate" title="' + item.title + '">' + item.title + '</div>';
    html +=     '<div class="text-muted small">' + (item.filesize/1024).toFixed(1) + ' KB</div>';
    html +=   '</div>';
    html += '</div>';
    col.innerHTML = html;
    grid.prepend(col);
}


</script>
{/block}

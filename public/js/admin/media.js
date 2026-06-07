/**
 * Gestione della libreria media
 */

document.addEventListener('DOMContentLoaded', function() {
    // Gestione submit form modifica media
    const editForm = document.getElementById('media-edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            fetch(editForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Media aggiornato con successo', 'success');
                } else {
                    showToast(data.message || 'Errore durante il salvataggio', 'danger');
                }
            })
            .catch(() => showToast('Errore di rete', 'danger'));
        });
    }
    // Inizializza i tooltip di Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestione selezione file
    const mediaGrid = document.getElementById('media-grid');
    if (mediaGrid) {
        mediaGrid.addEventListener('click', function(e) {
            const selectBtn = e.target.closest('.media-select');
            if (selectBtn) {
                e.preventDefault();
                const mediaId = selectBtn.dataset.id;
                selectMedia(mediaId);
            }

            const deleteBtn = e.target.closest('.media-delete');
            if (deleteBtn) {
                e.preventDefault();
                const mediaId = deleteBtn.dataset.id;
                deleteMedia(mediaId);
            }
        });
    }
    
    // Gestione filtri
    const filterForm = document.getElementById('media-filter-form');
    if (filterForm) {
        const typeSelect = filterForm.querySelector('select[name="type"]');
        
        typeSelect.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    // Gestione anteprima immagine al passaggio del mouse
    const mediaItems = document.querySelectorAll('.media-item');
    mediaItems.forEach(item => {
        const img = item.querySelector('img');
        if (img) {
            item.addEventListener('mouseenter', function() {
                const preview = document.createElement('div');
                preview.className = 'media-preview';
                preview.innerHTML = `
                    <div class="media-preview-inner">
                        <img src="${img.src}" class="img-fluid" alt="">
                    </div>
                `;
                document.body.appendChild(preview);

                const rect = item.getBoundingClientRect();
                preview.style.top = `${rect.top + window.scrollY}px`;
                preview.style.left = `${rect.right + 20}px`;
            });

            item.addEventListener('mouseleave', function() {
                const preview = document.querySelector('.media-preview');
                if (preview) {
                    preview.remove();
                }
            });
        }
    });
});

/**
 * Seleziona un media da utilizzare
 */
function selectMedia(mediaId) {
    // Questa funzione verrà chiamata quando si seleziona un media da utilizzare
    // in un articolo o in una pagina
    if (window.opener) {
        // Se aperto da un editor esterno
        window.opener.postMessage({
            action: 'selectMedia',
            mediaId: mediaId
        }, '*');
        window.close();
    } else {
        // Gestione interna
        console.log('Media selezionato:', mediaId);
        // Qui puoi implementare la logica per utilizzare il media selezionato
    }
}

/**
 * Elimina un media
 */
function deleteMedia(mediaId) {
    if (!confirm('Sei sicuro di voler eliminare questo file?\nQuesta azione non può essere annullata.')) {
        return;
    }

    fetch(`${BASE_URL}admin/media/delete/${mediaId}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            _token: CSRF_TOKEN
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Rimuovi l'elemento dalla griglia
            const mediaItem = document.querySelector(`.media-item[data-id="${mediaId}"]`);
            if (mediaItem) {
                mediaItem.closest('.col-6').remove();
                
                // Se non ci sono più elementi, mostra il messaggio di nessun file
                const mediaGrid = document.getElementById('media-grid');
                if (mediaGrid && mediaGrid.children.length === 0) {
                    mediaGrid.innerHTML = `
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nessun file trovato</p>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                    <i class="fas fa-upload me-2"></i>Carica il tuo primo file
                                </button>
                            </div>
                        </div>
                    `;
                }
            }
            
            // Mostra un messaggio di successo
            showToast('File eliminato con successo', 'success');
        } else {
            throw new Error(data.message || 'Errore durante l\'eliminazione del file');
        }
    })
    .catch(error => {
        console.error('Errore:', error);
        showToast(error.message || 'Errore durante l\'eliminazione del file', 'error');
    });
}

/**
 * Mostra un messaggio di notifica
 */
function showToast(message, type = 'info') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
    toastContainer.style.zIndex = '11';
    
    const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
            </div>
        </div>
    `;
    
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);
    
    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, {
        autohide: true,
        delay: 3000
    });
    
    toast.show();
    
    // Rimuovi il toast dal DOM dopo l'animazione
    toastEl.addEventListener('hidden.bs.toast', function () {
        toastContainer.remove();
    });
}

// Esponi le funzioni globali
window.selectMedia = selectMedia;

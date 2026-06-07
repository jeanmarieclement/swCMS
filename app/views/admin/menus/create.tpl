{extends file="admin/layout.tpl"}

{block name="content"}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>New Menu</h1>
                <a href="/admin/menus" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            {if isset($smarty.session.flash_errors)}
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        {foreach $smarty.session.flash_errors as $error}
                            <li>{$error}</li>
                        {/foreach}
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            {/if}

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="/admin/menus/store">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Titolo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="{$smarty.session.old_input.title|default:''|escape}" required>
                                    <div class="form-text">Il testo che apparirà nel menu</div>
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipo di Menu <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" onchange="toggleMenuFields()">
                                        {foreach $menu_types as $key => $label}
                                            <option value="{$key}" 
                                                {if ($smarty.session.old_input.type|default:'custom') == $key}selected{/if}>
                                                {$label}
                                            </option>
                                        {/foreach}
                                    </select>
                                    <div class="form-text">Seleziona il tipo di collegamento per questo menu</div>
                                </div>

                                <!-- Campo URL Personalizzato -->
                                <div class="mb-3" id="custom-url-field">
                                    <label for="url" class="form-label">URL <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="url" name="url" 
                                           value="{$smarty.session.old_input.url|default:''|escape}" 
                                           placeholder="/pagina o https://example.com">
                                    <div class="form-text">URL interno (inizia con /) o esterno (https://)</div>
                                </div>

                                <!-- Campo Pagina -->
                                <div class="mb-3" id="page-field" style="display: none;">
                                    <label for="page_id" class="form-label">Seleziona Pagina <span class="text-danger">*</span></label>
                                    
                                    <select class="form-select" id="page_id" name="page_id">
                                        <option value="">-- Seleziona una Pagina --</option>
                                        {foreach $pages as $page}
                                            <option value="{$page.id}" 
                                                {if ($smarty.session.old_input.page_id|default:'') == $page.id}selected{/if}>
                                                {$page.title|escape}
                                            </option>
                                        {/foreach}
                                    </select>
                                    <div class="form-text">Seleziona la pagina da collegare</div>
                                </div>

                                <!-- Campo Articolo -->
                                <div class="mb-3" id="post-field" style="display: none;">
                                    <label for="post_id" class="form-label">Seleziona Articolo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="post_id" name="post_id">
                                        <option value="">-- Seleziona un Articolo --</option>
                                        {foreach $posts as $post}
                                            <option value="{$post.id}" 
                                                {if ($smarty.session.old_input.post_id|default:'') == $post.id}selected{/if}>
                                                {$post.title|escape}
                                            </option>
                                        {/foreach}
                                    </select>
                                    <div class="form-text">Seleziona l'articolo da collegare</div>
                                </div>

                                <div class="mb-3">
                                    <label for="css_class" class="form-label">Classe CSS</label>
                                    <input type="text" class="form-control" id="css_class" name="css_class" 
                                           value="{$smarty.session.old_input.css_class|default:''|escape}" 
                                           placeholder="menu-item-special">
                                    <div class="form-text">Classi CSS aggiuntive per il menu</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Posizione</label>
                                    <select class="form-select" id="location" name="location">
                                        {foreach $locations as $loc}
                                            <option value="{$loc}" {if $smarty.session.old_input.location|default:'header' == $loc}selected{/if}>
                                                {$loc|capitalize}
                                            </option>
                                        {/foreach}
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Menu Genitore</label>
                                    <select class="form-select" id="parent_id" name="parent_id">
                                        <option value="">Nessuno (Menu principale)</option>
                                        {foreach $parent_menus as $parent}
                                            {if !$parent.parent_id}
                                                <option value="{$parent.id}" 
                                                    {if $smarty.session.old_input.parent_id|default:'' == $parent.id}selected{/if}>
                                                    {$parent.title|escape}
                                                </option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                    <div class="form-text">Lasciare vuoto per menu di primo livello</div>
                                </div>

                                <div class="mb-3">
                                    <label for="position" class="form-label">Ordine</label>
                                    <input type="number" class="form-control" id="position" name="position" 
                                           value="{$smarty.session.old_input.position|default:'0'}" min="0">
                                    <div class="form-text">0 = aggiungi in fondo</div>
                                </div>

                                <div class="mb-3">
                                    <label for="target" class="form-label">Destinazione Link</label>
                                    <select class="form-select" id="target" name="target">
                                        <option value="_self" {if $smarty.session.old_input.target|default:'_self' == '_self'}selected{/if}>
                                            Stessa finestra
                                        </option>
                                        <option value="_blank" {if $smarty.session.old_input.target|default:'' == '_blank'}selected{/if}>
                                            Nuova finestra
                                        </option>
                                    </select>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="active" name="active" 
                                           {if $smarty.session.old_input.active|default:'1' == '1'}checked{/if}>
                                    <label class="form-check-label" for="active">
                                        Menu attivo
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="/admin/menus" class="btn btn-secondary">Annulla</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salva Menu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleMenuFields() {
    const type = document.getElementById('type').value;
    const customUrlField = document.getElementById('custom-url-field');
    const pageField = document.getElementById('page-field');
    const postField = document.getElementById('post-field');
    const urlInput = document.getElementById('url');
    const pageSelect = document.getElementById('page_id');
    const postSelect = document.getElementById('post_id');

    // Nascondi tutti i campi
    customUrlField.style.display = 'none';
    pageField.style.display = 'none';
    postField.style.display = 'none';

    // Rimuovi required da tutti
    urlInput.removeAttribute('required');
    pageSelect.removeAttribute('required');
    postSelect.removeAttribute('required');

    // Mostra il campo appropriato
    switch(type) {
        case 'custom':
            customUrlField.style.display = 'block';
            urlInput.setAttribute('required', 'required');
            break;
        case 'page':
            pageField.style.display = 'block';
            pageSelect.setAttribute('required', 'required');
            break;
        case 'post':
            postField.style.display = 'block';
            postSelect.setAttribute('required', 'required');
            break;
    }
}

// Inizializza al caricamento della pagina
document.addEventListener('DOMContentLoaded', function() {
    toggleMenuFields();
});
</script>

{/block}
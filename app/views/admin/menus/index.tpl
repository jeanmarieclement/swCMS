{extends file="admin/layout.tpl"}

{block name="title"}Menus{/block}

{block name="breadcrumbs"}
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{$admin_url}">Dashboard</a></li>
        <li class="breadcrumb-item active">Menus</li>
    </ol>
</nav>
{/block}

{block name="content"}
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Gestione Menu</h1>
                <a href="/admin/menus/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuovo Menu
                </a>
            </div>

            {if isset($smarty.session.flash_message)}
                <div class="alert alert-{if $smarty.session.flash_message.type == 'error'}danger{else}{$smarty.session.flash_message.type}{/if} alert-dismissible fade show" role="alert">
                    {$smarty.session.flash_message.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            {/if}

            <div class="card">
                <div class="card-body">
                    {if $menus}
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titolo</th>
                                        <th>Tipo</th>
                                        <th>URL</th>
                                        <th>Posizione</th>
                                        <th>Ordine</th>
                                        <th>Genitore</th>
                                        <th>Stato</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {foreach $menus as $menu}
                                        <tr>
                                            <td>{$menu.id}</td>
                                            <td>
                                                {if $menu.parent_id}
                                                    <span class="text-muted">└─</span>
                                                {/if}
                                                {$menu.title|escape}
                                            </td>
                                            <td>
                                                {assign var="type_label" value="Personalizzato"}
                                                {if $menu.type == 'page'}
                                                    {assign var="type_label" value="Pagina"}
                                                    <span class="badge bg-info">Pagina</span>
                                                {elseif $menu.type == 'post'}
                                                    {assign var="type_label" value="Articolo"}
                                                    <span class="badge bg-success">Articolo</span>
                                                {else}
                                                    <span class="badge bg-secondary">Personalizzato</span>
                                                {/if}
                                            </td>
                                            <td>
                                                <small class="text-muted">{$menu.url|escape}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{$menu.location|escape}</span>
                                            </td>
                                            <td>{$menu.position}</td>
                                            <td>
                                                {if $menu.parent_id}
                                                    <small class="text-muted">ID: {$menu.parent_id}</small>
                                                {else}
                                                    <span class="text-muted">─</span>
                                                {/if}
                                            </td>
                                            <td>
                                                {if $menu.active}
                                                    <span class="badge bg-success">Attivo</span>
                                                {else}
                                                    <span class="badge bg-danger">Disattivo</span>
                                                {/if}
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="/admin/menus/edit/{$menu.id}" class="btn btn-outline-primary" title="Modifica">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="confirmDelete({$menu.id}, '{$menu.title|escape:javascript}')" 
                                                            title="Elimina">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    {else}
                        <div class="text-center py-4">
                            <h5 class="text-muted">Nessun menu trovato</h5>
                            <p class="text-muted">Inizia creando il tuo primo menu.</p>
                            <a href="/admin/menus/create" class="btn btn-primary">Crea Menu</a>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    if (confirm('Sei sicuro di voler eliminare il menu "' + title + '"?\n\nQuesta azione eliminerà anche tutti i sottomenu.')) {
        window.location.href = '/admin/menus/delete/' + id;
    }
}
</script>
{/block}
{* Template per errore 404 *}
{extends file="layout.tpl"}

{block name="title"}Pagina Non Trovata - {$site_name}{/block}

{block name="content"}
<div class="container error-container text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="bg-white p-5 rounded shadow">
                <h1 class="display-1 text-primary fw-bold">404</h1>
                <h2 class="h3 text-muted mb-4">Pagina Non Trovata</h2>
                <p class="mb-4">
                    {if isset($error)}
                        {$error|escape}
                    {else}
                        La pagina che stai cercando non esiste o è stata spostata.
                    {/if}
                </p>
                <p class="text-muted mb-4">
                    Verifica che l'URL inserito sia corretto o utilizza il menu di navigazione.
                </p>
                <div class="mt-4">
                    <a href="{$site_url|default:'/'}" class="btn btn-primary btn-lg">
                        <i class="fas fa-home"></i> Torna alla Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
}
.display-1 {
    font-size: 8rem;
    line-height: 1;
}
</style>
{/block}
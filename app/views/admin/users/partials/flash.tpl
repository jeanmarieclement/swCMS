{* Display success/error messages *}
{if isset($smarty.session.flash_message)}
    <div class="alert alert-{$smarty.session.flash_message.type} alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> {$smarty.session.flash_message.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
{/if}
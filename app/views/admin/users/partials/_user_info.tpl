{* User Info Partial *}
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-user me-1"></i>
        User Information
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Username:</div>
            <div class="col-md-9">{if isset($user.username)}{$user.username}{else}Unknown{/if}</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Email:</div>
            <div class="col-md-9">{if isset($user.email)}{$user.email}{else}Not provided{/if}</div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Registered:</div>
            <div class="col-md-9">
                {if isset($user.created_at) && $user.created_at}
                    {$user.created_at|date_format:"%B %e, %Y at %l:%M %p"}
                {else}
                    Unknown
                {/if}
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-3 fw-bold">Last Login:</div>
            <div class="col-md-9">
                {if isset($user.last_login) && $user.last_login}
                    {$user.last_login|date_format:"%B %e, %Y at %l:%M %p"}
                {else}
                    <em class="text-muted">Never logged in</em>
                {/if}
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 fw-bold">Status:</div>
            <div class="col-md-9">
                {if isset($user.status)}
                    {if $user.status == 'active'}
                        <span class="badge bg-success">Active</span>
                    {elseif $user.status == 'inactive'}
                        <span class="badge bg-secondary">Inactive</span>
                    {else}
                        <span class="badge bg-warning">{$user.status|capitalize}</span>
                    {/if}
                {elseif isset($user.active)}
                    {if $user.active}
                        <span class="badge bg-success">Active</span>
                    {else}
                        <span class="badge bg-secondary">Inactive</span>
                    {/if}
                {else}
                    <span class="badge bg-warning">Status Unknown</span>
                {/if}
            </div>
        </div>
    </div>
</div>

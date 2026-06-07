{* Modern Role Permissions Partial *}
{assign var="permissions" value=$role.permissions}

<div class="permission-badges">
    {if isset($permissions[0]) && $permissions[0] === "*"}
        <span class="badge bg-danger mb-1">
            <i class="fas fa-star me-1"></i>Full Access
        </span>
    {elseif isset($permissions) && $permissions|@count > 0}
        {foreach from=$permissions item=permission name=perm_loop}
            {if $smarty.foreach.perm_loop.index < 3}
                <span class="badge bg-primary mb-1">
                    <i class="fas fa-check me-1"></i>{$permission|capitalize}
                </span>
            {elseif $smarty.foreach.perm_loop.index == 3}
                <span class="badge bg-secondary mb-1">
                    +{$permissions|@count - 3} more
                </span>
                {break}
            {/if}
        {/foreach}
    {else}
        <span class="badge bg-secondary mb-1">
            <i class="fas fa-times me-1"></i>No Permissions
        </span>
    {/if}
</div>
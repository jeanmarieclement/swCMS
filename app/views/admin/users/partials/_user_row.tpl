{* User Row Partial *}
<tr>
    <td>
        <a href="{$admin_url}/users/edit/{$user.id}" class="fw-bold text-decoration-none">{$user.username}</a>
    </td>
    <td>{$user.email}</td>
    <td>{$user.display_name}</td>
    <td>
        <span class="badge {if $user.role == 'admin'}bg-danger{elseif $user.role == 'editor'}bg-warning{elseif $user.role == 'author'}bg-info{else}bg-secondary{/if}">
            {$user.role|capitalize}
        </span>
    </td>
    <td>
        <span class="badge {if $user.status == 'active'}bg-success{else}bg-secondary{/if}">
            {$user.status|capitalize}
        </span>
    </td>
    <td>
        {if isset($user.last_login) && $user.last_login}
            {$user.last_login|date_format:"%e %b %Y"}
        {else}
            <em class="text-muted">Never</em>
        {/if}
    </td>
    <td>
        <div class="btn-group" role="group">
            {if $canEdit || !isset($canEdit)}
                <a href="{$admin_url}/users/edit/{$user.id}" class="btn btn-sm btn-outline-primary" title="Edit User">
                    <i class="fas fa-edit"></i>
                </a>
            {/if}
            {if $canDelete && $user.id != $current_user_id}
                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{$user.id}" title="Delete User">
                    <i class="fas fa-trash"></i>
                </button>
            {/if}
        </div>
    </td>
</tr>

{* Include modal for this specific user *}
{if $canDelete && $user.id != $current_user_id}
    {include file="admin/users/partials/_user_modals.tpl" user=$user admin_url=$admin_url current_user_id=$current_user_id canDelete=$canDelete}
{/if}

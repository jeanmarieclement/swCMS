{* User Roles Partial *}
<div class="form-group mb-3">
    <label class="form-label">User Role <span class="text-danger">*</span></label>
    <select class="form-select" name="role" required>
        {if isset($roles) && $roles}
            {foreach from=$roles item=role}
                <option value="{if isset($role.id)}{$role.id}{else}{$role}{/if}" 
                        {if isset($user.role_id) && isset($role.id) && $user.role_id == $role.id}selected{elseif isset($user.role) && isset($role.name) && $user.role == $role.name}selected{elseif isset($user.role) && $user.role == $role}selected{/if}>
                    {if isset($role.name)}{$role.name}{else}{$role}{/if}
                </option>
            {/foreach}
        {else}
            <option value="subscriber" {if isset($user.role) && $user.role == 'subscriber'}selected{/if}>Subscriber</option>
            <option value="author" {if isset($user.role) && $user.role == 'author'}selected{/if}>Author</option>
            <option value="editor" {if isset($user.role) && $user.role == 'editor'}selected{/if}>Editor</option>
            <option value="admin" {if isset($user.role) && $user.role == 'admin'}selected{/if}>Administrator</option>
        {/if}
    </select>
    <div class="form-text">Select the role for this user.</div>
</div>

{if isset($permissions) && $permissions}
<div class="form-group">
    <label class="form-label">Additional Permissions</label>
    {foreach from=$permissions item=permission}
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="permissions[]" 
                   id="perm_{if isset($permission.id)}{$permission.id}{else}{$permission@index}{/if}" 
                   value="{if isset($permission.id)}{$permission.id}{else}{$permission}{/if}">
            <label class="form-check-label" for="perm_{if isset($permission.id)}{$permission.id}{else}{$permission@index}{/if}">
                {if isset($permission.name)}{$permission.name}{else}{$permission}{/if}
            </label>
        </div>
    {/foreach}
</div>
{/if}

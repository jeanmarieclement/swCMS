{* User Form Fields Partial *}
<div class="row mb-3">
    <div class="col-md-6">
        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
        <input class="form-control" id="username" name="username" type="text" value="{if isset($user.username)}{$user.username}{/if}" required />
        <div class="form-text">The username must be unique and cannot be changed after creation.</div>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
        <input class="form-control" id="email" name="email" type="email" value="{if isset($user.email)}{$user.email}{/if}" required />
        <div class="form-text">Used for login and notifications.</div>
    </div>
</div>

<div class="mb-3">
    <label for="display_name" class="form-label">Display Name</label>
    <input class="form-control" id="display_name" name="display_name" type="text" value="{if isset($user.display_name)}{$user.display_name}{/if}" />
    <div class="form-text">The name shown publicly (optional, defaults to username).</div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="password" class="form-label">New Password</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="new-password" />
        <div class="form-text">Leave blank to keep current password.</div>
    </div>
    <div class="col-md-6">
        <label for="password_confirm" class="form-label">Confirm New Password</label>
        <input class="form-control" id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" />
        <div class="form-text">Re-enter the new password to confirm.</div>
    </div>
</div>

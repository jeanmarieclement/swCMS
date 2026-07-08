{extends file="../layouts/auth.tpl"}

{block name="title"}{$title}{/block}

{block name="content"}
<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Reset Password</h2>

        <p class="auth-description">
            Enter your new password below.
        </p>

        {if isset($flash) && $flash}
        <div class="alert alert-{if $flash.type == 'error'}danger{elseif $flash.type == 'success'}success{else}info{/if}">
            {$flash.message|escape}
        </div>
        {/if}
        {if $error}
        <div class="alert alert-danger">
            {$error}
        </div>
        {/if}

        <form method="post" action="{$site_url}/auth/process-password-reset" class="auth-form">
            {$csrf_field}
            <input type="hidden" name="token" value="{$token}">
            <input type="hidden" name="email" value="{$email}">

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small class="form-text text-muted">
                    Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.
                </small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="8">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </div>

            <div class="auth-links">
                <p>Remember your password? <a href="{$site_url}/auth/login">Login</a></p>
            </div>
        </form>
    </div>
</div>
{/block}

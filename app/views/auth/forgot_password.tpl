{extends file="../layouts/auth.tpl"}

{block name="title"}{$title}{/block}

{block name="content"}
<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Forgot Password</h2>

        <p class="auth-description">
            Enter your email address and we'll send you a link to reset your password.
        </p>

        {if $error}
        <div class="alert alert-danger">
            {$error}
        </div>
        {/if}

        <form method="post" action="{$site_url}/auth/request-password-reset" class="auth-form">
            {$csrf_field}

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </div>

            <div class="auth-links">
                <p>Remember your password? <a href="{$site_url}/auth/login">Login</a></p>
            </div>
        </form>
    </div>
</div>
{/block}

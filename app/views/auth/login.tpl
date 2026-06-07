{extends file="../layouts/auth.tpl"}

{block name="title"}{$title}{/block}

{block name="content"}
<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Login to {$site_name}</h2>
        
        {if $error}
        <div class="alert alert-danger">
            {$error}
        </div>
        {/if}
        
        <form method="post" action="{$site_url}/auth/login" class="auth-form">
            {$csrf_field}

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </div>

            <div class="auth-links">
                <p><a href="{$site_url}/auth/forgot-password">Forgot Password?</a></p>
                {if $allow_registration}
                <p>Don't have an account? <a href="{$site_url}/auth/register">Register</a></p>
                {/if}
            </div>
        </form>
    </div>
</div>
{/block}

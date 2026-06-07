{extends file="../layouts/auth.tpl"}

{block name="title"}{$title}{/block}

{block name="content"}
<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Create an Account</h2>
        
        {if $error}
        <div class="alert alert-danger">
            {$error}
        </div>
        {/if}
        
        {if $success}
        <div class="alert alert-success">
            Registration successful! You can now <a href="{$site_url}/auth/login">login</a>.
        </div>
        {else}
        <form method="post" action="{$site_url}/auth/register" class="auth-form">
            {$csrf_field}

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
                <small class="form-text text-muted">Used for login. Cannot be changed later.</small>
            </div>
            
            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" class="form-control">
                <small class="form-text text-muted">How your name will appear publicly. If left empty, username will be used.</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8">
                <small class="form-text text-muted">Password must be at least 8 characters long</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </div>
            
            <div class="auth-links">
                <p>Already have an account? <a href="{$site_url}/auth/login">Login</a></p>
            </div>
        </form>
        {/if}
    </div>
</div>
{/block}

{extends file="../layouts/auth.tpl"}

{block name="title"}Access Denied{/block}

{block name="content"}
<div class="auth-container">
    <div class="auth-box">
        <h2 class="auth-title">Access Denied</h2>
        
        <div class="alert alert-danger">
            <p>You do not have permission to access this page.</p>
        </div>
        
        <div class="auth-links">
            <p><a href="{$site_url}">Return to Homepage</a></p>
            {if !isset($smarty.session.user_id)}
                <p><a href="{$site_url}/auth/login">Login</a></p>
            {/if}
        </div>
    </div>
</div>
{/block}

{extends file="admin/layout.tpl"}
{block name="content"}
<div class="container mt-4">
    <h1>Site Settings</h1>
    {if isset($smarty.session.success)}
        <div class="alert alert-success">{$smarty.session.success}</div>
    {/if}
    <form method="post" action="{$admin_url}/settings/save">
        <!-- SEZIONE GENERALI -->
        <div class="card mb-3">
            <div class="card-header">General</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="site_title" class="form-label">Site Title</label>
                    <input type="text" class="form-control" id="site_title" name="settings[site_title]" value="{$settings.site_title|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="site_description" class="form-label">Description</label>
                    <input type="text" class="form-control" id="site_description" name="settings[site_description]" value="{$settings.site_description|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="SITE_NAME" class="form-label">System Name</label>
                    <input type="text" class="form-control" id="SITE_NAME" name="settings[SITE_NAME]" value="{$settings.SITE_NAME|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="SITE_URL" class="form-label">Site URL</label>
                    <input type="text" class="form-control" id="SITE_URL" name="settings[SITE_URL]" value="{$settings.SITE_URL|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="ADMIN_URL" class="form-label">URL Admin</label>
                    <input type="text" class="form-control" id="ADMIN_URL" name="settings[ADMIN_URL]" value="{$settings.ADMIN_URL|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="THEME_ACTIVE" class="form-label">Active Theme</label>
                    <input type="text" class="form-control" id="THEME_ACTIVE" name="settings[THEME_ACTIVE]" value="{$settings.THEME_ACTIVE|default:''}">
                </div>
            </div>
        </div>

        <!-- SEZIONE HOMEPAGE -->
        <div class="card mb-3">
            <div class="card-header">Homepage</div>
            <div class="card-body">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="settings[homepage_mode]" id="home_latest" value="latest" {if $settings.homepage_mode == 'latest'}checked{/if}>
                    <label class="form-check-label" for="home_latest">Show Latest Articles</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="settings[homepage_mode]" id="home_page" value="page" {if $settings.homepage_mode == 'page'}checked{/if}>
                    <label class="form-check-label" for="home_page">Use Specific Page</label>
                </div>
                <div class="mt-2">
                    <select class="form-select" name="settings[homepage_page]">
                        <option value="">-- Select Page --</option>
                        {foreach $pages as $page}
                            <option value="{$page.id}" {if $settings.homepage_page == $page.id}selected{/if}>{$page.title}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>

        <!-- SEZIONE SEO -->
        <div class="card mb-3">
            <div class="card-header">SEO</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="meta_description" class="form-label">Global Meta Description</label>
                    <input type="text" class="form-control" id="meta_description" name="settings[meta_description]" value="{$settings.meta_description|default:''}">
                </div>
                <div class="col-md-6">
                    <label for="meta_keywords" class="form-label">Global Meta Keywords</label>
                    <input type="text" class="form-control" id="meta_keywords" name="settings[meta_keywords]" value="{$settings.meta_keywords|default:''}">
                </div>
            </div>
        </div>

        <!-- SEZIONE COMMENTI E POST -->
        <div class="card mb-3">
            <div class="card-header">Articles and Comments</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="posts_per_page" class="form-label">Articles per Page</label>
                    <input type="number" class="form-control" id="posts_per_page" name="settings[posts_per_page]" value="{$settings.posts_per_page|default:'10'}">
                </div>
                <div class="col-md-6">
                    <label for="comments_enabled" class="form-label">Enable Comments</label>
                    <select class="form-select" id="comments_enabled" name="settings[comments_enabled]">
                        <option value="1" {if $settings.comments_enabled == '1'}selected{/if}>Yes</option>
                        <option value="0" {if $settings.comments_enabled == '0'}selected{/if}>No</option>
                    </select>
                    <div class="form-text">Enable or disable comments globally. Individual posts and pages can override this setting.</div>
                </div>
            </div>
        </div>

        <!-- SEZIONE UTENTI/REGISTRAZIONE -->
        <div class="card mb-3">
            <div class="card-header">Users and Registration</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="ALLOW_REGISTRATION" class="form-label">Allow User Registration</label>
                    <select class="form-select" id="ALLOW_REGISTRATION" name="settings[ALLOW_REGISTRATION]">
                        <option value="1" {if $settings.ALLOW_REGISTRATION == '1'}selected{/if}>Yes</option>
                        <option value="0" {if $settings.ALLOW_REGISTRATION == '0'}selected{/if}>No</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- SEZIONE EMAIL/SMTP -->
        <div class="card mb-3">
            <div class="card-header">Email & SMTP</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label for="MAIL_FROM" class="form-label">Sender Email</label>
                    <input type="text" class="form-control" id="MAIL_FROM" name="settings[MAIL_FROM]" value="{$settings.MAIL_FROM|default:''}">
                </div>
                <div class="col-md-4">
                    <label for="MAIL_FROM_NAME" class="form-label">Sender Name</label>
                    <input type="text" class="form-control" id="MAIL_FROM_NAME" name="settings[MAIL_FROM_NAME]" value="{$settings.MAIL_FROM_NAME|default:''}">
                </div>
                <div class="col-md-4">
                    <label for="SMTP_HOST" class="form-label">SMTP Host</label>
                    <input type="text" class="form-control" id="SMTP_HOST" name="settings[SMTP_HOST]" value="{$settings.SMTP_HOST|default:''}">
                </div>
                <div class="col-md-3">
                    <label for="SMTP_PORT" class="form-label">SMTP Port</label>
                    <input type="text" class="form-control" id="SMTP_PORT" name="settings[SMTP_PORT]" value="{$settings.SMTP_PORT|default:''}">
                </div>
                <div class="col-md-3">
                    <label for="SMTP_USER" class="form-label">SMTP User</label>
                    <input type="text" class="form-control" id="SMTP_USER" name="settings[SMTP_USER]" value="{$settings.SMTP_USER|default:''}">
                </div>
                <div class="col-md-3">
                    <label for="SMTP_PASS" class="form-label">SMTP Password</label>
                    <input type="password" class="form-control" id="SMTP_PASS" name="settings[SMTP_PASS]" value="{$settings.SMTP_PASS|default:''}">
                </div>
            </div>
        </div>

        <!-- SEZIONE SISTEMA -->
        <div class="card mb-3">
            <div class="card-header">System</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label for="SESSION_TIMEOUT" class="form-label">Session Timeout (seconds)</label>
                    <input type="number" class="form-control" id="SESSION_TIMEOUT" name="settings[SESSION_TIMEOUT]" value="{$settings.SESSION_TIMEOUT|default:'1800'}">
                </div>
                <div class="col-md-4">
                    <label for="DEBUG_MODE" class="form-label">Debug mode</label>
                    <select class="form-select" id="DEBUG_MODE" name="settings[DEBUG_MODE]">
                        <option value="1" {if $settings.DEBUG_MODE == '1'}selected{/if}>Yes</option>
                        <option value="0" {if $settings.DEBUG_MODE == '0'}selected{/if}>No</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="TIMEZONE" class="form-label">System Timezone</label>
                    <input type="text" class="form-control" id="TIMEZONE" name="settings[TIMEZONE]" value="{$settings.TIMEZONE|default:''}">
                </div>
                <div class="col-md-4">
                    <label for="LANGUAGE" class="form-label">System Language</label>
                    <input type="text" class="form-control" id="LANGUAGE" name="settings[LANGUAGE]" value="{$settings.LANGUAGE|default:''}">
                </div>
            </div>
        </div>

        <div class="mt-3 text-end">
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
    </form>

</div>
{/block}

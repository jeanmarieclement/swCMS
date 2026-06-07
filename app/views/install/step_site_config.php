<h2 class="step-title">Site Configuration</h2>
<p class="step-description">
    Configure the basic settings for your website.
</p>

<form method="post">
    <div class="form-group">
        <label for="site_name">Site Name *</label>
        <input type="text" name="site_name" id="site_name" value="<?php echo htmlspecialchars($config['site']['name'] ?? 'My swCMS Site'); ?>" placeholder="My swCMS Site" required>
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            This will appear in the browser title and throughout your site.
        </small>
    </div>
    
    <div class="form-group">
        <label for="site_url">Site URL *</label>
        <input type="text" name="site_url" id="site_url" value="<?php echo htmlspecialchars($config['site']['url'] ?? 'http://' . $_SERVER['HTTP_HOST']); ?>" placeholder="http://yoursite.com" required>
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            The full URL where your site will be accessible (without trailing slash).
        </small>
    </div>
    
    <div class="form-group">
        <label for="site_description">Site Description</label>
        <textarea name="site_description" id="site_description" rows="3" placeholder="A brief description of your website"><?php echo htmlspecialchars($config['site']['description'] ?? ''); ?></textarea>
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            This will be used for SEO meta descriptions and site information.
        </small>
    </div>
    
    <div class="actions">
        <button type="submit" name="continue" class="btn">Continue to Admin Account</button>
    </div>
</form>
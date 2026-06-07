<h2 class="step-title">Administrator Account</h2>
<p class="step-description">
    Create the main administrator account for your CMS. This account will have full access to all features.
</p>

<form method="post">
    <div class="form-group">
        <label for="admin_username">Admin Username *</label>
        <input type="text" name="admin_username" id="admin_username" value="<?php echo htmlspecialchars($config['admin']['username'] ?? 'admin'); ?>" placeholder="admin" required>
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            This will be used to log into the admin panel.
        </small>
    </div>
    
    <div class="form-group">
        <label for="admin_email">Admin Email *</label>
        <input type="email" name="admin_email" id="admin_email" value="<?php echo htmlspecialchars($config['admin']['email'] ?? ''); ?>" placeholder="admin@yoursite.com" required>
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            Used for notifications and password recovery.
        </small>
    </div>
    
    <div class="form-group">
        <label for="admin_password">Admin Password *</label>
        <input type="password" name="admin_password" id="admin_password" placeholder="Enter a strong password" required minlength="8">
        <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
            Must be at least 8 characters long. Use a mix of letters, numbers, and symbols.
        </small>
    </div>
    
    <div class="form-group">
        <label for="admin_password_confirm">Confirm Password *</label>
        <input type="password" name="admin_password_confirm" id="admin_password_confirm" placeholder="Confirm your password" required>
    </div>
    
    <div class="actions">
        <button type="submit" name="continue" class="btn">Complete Installation</button>
    </div>
</form>

<script>
document.getElementById('admin_password_confirm').addEventListener('input', function() {
    const password = document.getElementById('admin_password').value;
    const confirm = this.value;
    
    if (password && confirm && password !== confirm) {
        this.style.borderColor = '#dc2626';
    } else {
        this.style.borderColor = '#e5e7eb';
    }
});
</script>
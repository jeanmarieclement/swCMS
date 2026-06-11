<?php if (!isset($success)) : ?>
    <h2 class="step-title">Ready to Install</h2>
    <p class="step-description">
        Everything is configured. Click the button below to complete the installation.
    </p>

    <div class="form-group" style="background:#f9fafb;padding:15px;border-radius:8px;">
        <p><strong>Database:</strong> <?php echo htmlspecialchars($config['database']['driver'] ?? 'N/A'); ?></p>
        <p><strong>Site Name:</strong> <?php echo htmlspecialchars($config['site']['name'] ?? 'N/A'); ?></p>
        <p><strong>Site URL:</strong> <?php echo htmlspecialchars($config['site']['url'] ?? 'N/A'); ?></p>
        <p><strong>Admin Username:</strong> <?php echo htmlspecialchars($config['admin']['username'] ?? 'N/A'); ?></p>
    </div>

    <form method="POST" action="?step=6">
        <div class="actions">
            <button type="submit" name="continue" value="1" class="btn">Complete Installation</button>
            <a href="?step=5" class="btn btn-secondary">Back</a>
        </div>
    </form>

<?php elseif ($success) : ?>
    <h2 class="step-title">Installation Complete!</h2>
    <p class="step-description">
        swCMS has been successfully installed and configured.
    </p>

    <div class="success">
        <strong>Installation completed successfully!</strong><br>
        Your CMS is now ready to use.
    </div>

    <div class="form-group">
        <h3>What's Next?</h3>
        <ul style="margin-left: 20px; margin-top: 15px; line-height: 1.8;">
            <li><strong>Access your site:</strong> <a href="<?php echo htmlspecialchars($config['site']['url'] ?? '/'); ?>" target="_blank"><?php echo htmlspecialchars($config['site']['url'] ?? 'Your Site'); ?></a></li>
            <li><strong>Admin Panel:</strong> <a href="<?php echo htmlspecialchars($config['site']['url'] ?? ''); ?>/admin" target="_blank">Login to Admin Panel</a></li>
            <li><strong>Security:</strong> Consider restricting access to this installer</li>
        </ul>
    </div>

    <?php if (isset($config['migration_results'])) : ?>
    <div class="form-group">
        <h3>Database Setup:</h3>
        <div style="background:#f0fdf4;padding:15px;border-radius:8px;margin-top:15px;border-left:4px solid #16a34a;">
            <p><strong>Migrations:</strong> <?php echo $config['migration_results']['total']; ?> total,
               <?php echo $config['migration_results']['applied']; ?> applied,
               <?php echo $config['migration_results']['skipped']; ?> skipped</p>
            <details style="margin-top:10px;">
                <summary style="cursor:pointer;font-weight:bold;">View Details</summary>
                <div style="margin-top:10px;font-size:12px;">
                    <?php foreach ($config['migration_results']['results'] as $result) : ?>
                        <div style="margin:2px 0;padding:2px 8px;background:<?php echo $result['status'] === 'success' ? '#dcfce7' : ($result['status'] === 'error' ? '#fef2f2' : '#f3f4f6'); ?>;border-radius:4px;">
                            <strong><?php echo $result['status'] === 'success' ? '✅' : ($result['status'] === 'error' ? '❌' : '⏭️'); ?></strong>
                            <?php echo htmlspecialchars($result['file']); ?> - <?php echo htmlspecialchars($result['message']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?php echo htmlspecialchars($config['site']['url'] ?? '/'); ?>" class="btn">Visit Your Site</a>
        <a href="<?php echo htmlspecialchars($config['site']['url'] ?? ''); ?>/admin" class="btn btn-secondary">Go to Admin Panel</a>
    </div>

<?php else : ?>
    <h2 class="step-title">Installation Failed</h2>
    <p class="step-description">
        There was an error during the installation process.
    </p>

    <?php if (!empty($errors)) : ?>
        <div class="error">
            <strong>Installation errors:</strong><br>
            <?php foreach ($errors as $error) : ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <h3>What to do next:</h3>
        <ul style="margin-left:20px;margin-top:15px;line-height:1.8;">
            <li>Check the error messages above</li>
            <li>Verify database credentials and permissions</li>
            <li>Ensure all directories are writable</li>
            <li>Check server error logs for more details</li>
        </ul>
    </div>

    <div class="actions">
        <a href="?step=1" class="btn">Start Over</a>
    </div>
<?php endif; ?>

<h2 class="step-title">System Requirements</h2>
<p class="step-description">
    Checking your server configuration to ensure swCMS can run properly.
</p>

<div class="form-group">
    <?php foreach ($checks as $check): ?>
        <div class="check-item">
            <div class="check-status <?php echo $check['passed'] ? 'pass' : 'fail'; ?>">
                <?php echo $check['passed'] ? '✓' : '✗'; ?>
            </div>
            <div class="check-details">
                <div class="check-name">
                    <?php echo htmlspecialchars($check['name']); ?>
                    <?php if ($check['required']): ?>
                        <span style="color: #dc2626;">(Required)</span>
                    <?php else: ?>
                        <span style="color: #059669;">(Optional)</span>
                    <?php endif; ?>
                </div>
                <div class="check-description"><?php echo htmlspecialchars($check['description']); ?></div>
                <div class="check-value">Current: <?php echo htmlspecialchars($check['value']); ?></div>
                <?php if (isset($check['info']) && !empty($check['info'])): ?>
                    <div class="check-info" style="font-size: 12px; color: #6b7280; margin-top: 5px;">
                        <strong>Info:</strong> <?php echo htmlspecialchars($check['info']); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$canContinue = true;
foreach ($checks as $check) {
    if ($check['required'] && !$check['passed']) {
        $canContinue = false;
        break;
    }
}
?>

<?php if (!$canContinue): ?>
    <div class="error">
        <strong>Required system requirements are not met.</strong><br>
        Please fix the issues marked as "Required" above before continuing with the installation.
    </div>
<?php endif; ?>

<form method="post">
    <div class="actions">
        <button type="submit" name="continue" class="btn" <?php echo !$canContinue ? 'disabled' : ''; ?>>
            Continue to Database Setup
        </button>
    </div>
</form>
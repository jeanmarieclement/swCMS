<h2 class="step-title">Database Configuration</h2>
<p class="step-description">
    Configure your database connection. swCMS supports both MySQL and SQLite databases.
</p>

<form method="post" id="dbForm">
    <div class="form-group">
        <label for="db_driver">Database Type</label>
        <select name="db_driver" id="db_driver" onchange="toggleDatabaseFields()">
            <option value="mysql" <?php echo ($config['database']['driver'] ?? 'mysql') === 'mysql' ? 'selected' : ''; ?>>MySQL</option>
            <option value="sqlite" <?php echo ($config['database']['driver'] ?? '') === 'sqlite' ? 'selected' : ''; ?>>SQLite</option>
        </select>
    </div>
    
    <div id="mysql_fields">
        <div class="form-group">
            <label for="db_host">Database Host</label>
            <input type="text" name="db_host" id="db_host" value="<?php echo htmlspecialchars($config['database']['host'] ?? 'localhost'); ?>" placeholder="localhost">
        </div>
        
        <div class="form-group">
            <label for="db_port">Database Port</label>
            <input type="number" name="db_port" id="db_port" value="<?php echo htmlspecialchars($config['database']['port'] ?? '3306'); ?>" placeholder="3306">
        </div>
        
        <div class="form-group">
            <label for="db_name">Database Name</label>
            <input type="text" name="db_name" id="db_name" value="<?php echo htmlspecialchars($config['database']['name'] ?? 'swcms'); ?>" placeholder="swcms">
        </div>
        
        <div class="form-group">
            <label for="db_user">Database Username</label>
            <input type="text" name="db_user" id="db_user" value="<?php echo htmlspecialchars($config['database']['user'] ?? ''); ?>" placeholder="username">
        </div>
        
        <div class="form-group">
            <label for="db_pass">Database Password</label>
            <input type="password" name="db_pass" id="db_pass" value="<?php echo htmlspecialchars($config['database']['pass'] ?? ''); ?>" placeholder="password">
        </div>
    </div>
    
    <div id="sqlite_fields" style="display: none;">
        <div class="form-group">
            <label for="db_sqlite_path">SQLite Database Path</label>
            <input type="text" name="db_sqlite_path" id="db_sqlite_path" value="<?php echo htmlspecialchars($config['database']['sqlite_path'] ?? DATA_PATH . '/database.sqlite'); ?>" placeholder="<?php echo DATA_PATH; ?>/database.sqlite">
            <small style="color: #6b7280; font-size: 14px; margin-top: 5px; display: block;">
                The directory must be writable. Database file will be created automatically.
            </small>
        </div>
    </div>
    
    <?php if (isset($config['db_connection_success']) && $config['db_connection_success']): ?>
        <div class="success">
            Database connection test successful! You can proceed to the next step.
        </div>
    <?php endif; ?>
    
    <div class="actions">
        <button type="submit" name="test_connection" class="btn btn-test">Test Connection</button>
        <button type="submit" name="continue" class="btn">Continue to Site Configuration</button>
    </div>
</form>

<script>
function toggleDatabaseFields() {
    const driver = document.getElementById('db_driver').value;
    const mysqlFields = document.getElementById('mysql_fields');
    const sqliteFields = document.getElementById('sqlite_fields');
    
    if (driver === 'mysql') {
        mysqlFields.style.display = 'block';
        sqliteFields.style.display = 'none';
    } else {
        mysqlFields.style.display = 'none';
        sqliteFields.style.display = 'block';
    }
}

// Initialize on page load
toggleDatabaseFields();
</script>
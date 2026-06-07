# Backup Manager Plugin for swCMS

A comprehensive backup solution for swCMS that supports database, files, and combined backups with scheduling and management interface.

## Features

### ✨ Core Backup Functionality
- **Database Backups**: Export MySQL/SQLite databases to SQL format
- **File Backups**: Create ZIP archives of application files
- **Full Backups**: Combined database and file backups
- **Compression**: Configurable compression levels (0-9)
- **Large File Support**: Handles large databases and file systems

### 📅 Scheduling System
- **Automated Backups**: Daily, weekly, and monthly schedules
- **Flexible Timing**: Configure exact times for backup execution
- **Multiple Schedules**: Create different schedules for different backup types
- **Schedule Management**: Enable/disable, edit, and delete schedules

### 🎛️ Management Interface
- **Dashboard Widget**: Quick backup status and controls on admin dashboard
- **Backup History**: View all backups with status, size, and timestamps
- **Download/Restore**: Download backup files or restore from backups
- **Real-time Updates**: AJAX-powered interface with live status updates

### ⚙️ Advanced Configuration
- **Retention Management**: Automatic cleanup of old backups
- **Include/Exclude Patterns**: Fine-grained control over what gets backed up
- **Email Notifications**: Get notified when backups complete or fail
- **Performance Tuning**: Configurable timeouts and resource limits

## Installation

1. **Upload Plugin Files**
   ```bash
   # Copy the backup-manager folder to your plugins directory
   cp -r backup-manager /path/to/swcms/plugins/
   ```

2. **Activate Plugin**
   - Go to Admin → Plugins
   - Find "Backup Manager" in the plugin list
   - Click "Activate"

3. **Configure Settings**
   - Navigate to Admin → Tools → Backup Manager
   - Go to Settings tab
   - Configure backup preferences
   - Set up email notifications if desired

## Configuration

### Basic Settings

The plugin creates several configuration options in the system settings:

```php
$settings = [
    'enabled' => true,                    // Enable/disable plugin
    'show_dashboard_widget' => true,      // Show dashboard widget
    'auto_cleanup' => true,               // Auto-delete old backups
    'retention_days' => 30,               // Days to keep backups
    'max_backup_size' => '500MB',         // Maximum backup size
    'backup_timeout' => 300,              // Timeout in seconds
    'compression_level' => 6,             // ZIP compression (0-9)
    'email_notifications' => false,       // Send email alerts
    'notification_email' => '',           // Email for notifications
    'include_uploads' => true,            // Include uploaded files
    'include_themes' => true,             // Include theme files
    'include_plugins' => false,           // Include plugin files
    'exclude_patterns' => [               // Files to exclude
        'node_modules/*',
        '.git/*',
        'vendor/*',
        '*.log',
        'cache/*',
        'tmp/*',
        'backups/*'
    ]
];
```

### Database Configuration

The plugin automatically detects your database configuration:

- **MySQL**: Uses existing DB_HOST, DB_NAME, DB_USER, DB_PASS constants
- **SQLite**: Uses DB_SQLITE_PATH constant or defaults to `/data/database.sqlite`

### File System Requirements

- **Backup Directory**: `/backups` (auto-created with proper permissions)
- **Write Permissions**: Web server must have write access to backup directory
- **PHP Extensions**: ZIP extension required for file backups
- **Memory**: Adequate PHP memory_limit for large backups

## Usage

### Creating Manual Backups

1. **Quick Backup**
   - Use dashboard widget "Quick Backup" button
   - Or go to Backup Manager → click backup type button

2. **Custom Backup**
   - Navigate to Admin → Tools → Backup Manager
   - Choose backup type (Database, Files, or Full)
   - Monitor progress in real-time

### Setting Up Scheduled Backups

1. **Create Schedule**
   ```
   - Go to Backup Manager → Schedules
   - Click "Create Schedule"
   - Fill in schedule details:
     * Name: Descriptive name
     * Type: database/files/full
     * Frequency: daily/weekly/monthly
     * Time: When to run (24-hour format)
   ```

2. **Schedule Management**
   - View all schedules in schedules tab
   - Toggle active/inactive status
   - Edit or delete existing schedules
   - Monitor next run times

### Backup Management

1. **Download Backups**
   - Click download button next to completed backups
   - Files are served with proper headers for browser download

2. **Delete Backups**
   - Individual deletion via delete button
   - Bulk cleanup via cleanup function
   - Automatic cleanup based on retention settings

3. **Restore Backups** *(Future feature)*
   - Will support automated restore from backup files
   - Currently shows placeholder interface

## API Reference

### BackupService Methods

```php
// Create a backup
$result = $backupService->createBackup('full', [
    'compression' => true,
    'exclude_patterns' => ['*.log', 'cache/*']
]);

// Get backup list
$backups = $backupService->getBackupList(20, 0);

// Delete backup
$success = $backupService->deleteBackup($backupId);

// Clean old backups
$deleted = $backupService->cleanOldBackups(30);
```

### AJAX Endpoints

All AJAX endpoints are available under `/admin/backup-manager/`:

- `POST /create` - Create new backup
- `GET /download?id={id}` - Download backup file
- `POST /delete` - Delete backup
- `POST /restore` - Restore backup (placeholder)
- `POST /schedule` - Manage schedules
- `GET /list` - Get backup list
- `GET /stats` - Get statistics
- `POST /cleanup` - Clean old backups

### JavaScript API

```javascript
// Access the backup manager instance
window.backupManager.createBackup('full');
window.backupManager.deleteBackup(123);
window.backupManager.cleanupBackups();

// Quick backup function (dashboard widget)
createQuickBackup();
```

## Database Schema

The plugin creates two tables:

### backup_jobs
```sql
CREATE TABLE backup_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('database', 'files', 'full') NOT NULL,
    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
    filename VARCHAR(255),
    file_size BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    error_message TEXT,
    settings JSON,
    created_by INT,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
);
```

### backup_schedules
```sql
CREATE TABLE backup_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('database', 'files', 'full') NOT NULL,
    frequency ENUM('daily', 'weekly', 'monthly') NOT NULL,
    time TIME NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    settings JSON,
    last_run TIMESTAMP NULL,
    next_run TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_active (active),
    INDEX idx_next_run (next_run)
);
```

## Cron Integration

For scheduled backups to work, you need to set up a cron job:

```bash
# Add to your crontab (runs every minute)
* * * * * cd /path/to/swcms && php -f cron.php backup-manager
```

Or create a custom cron script:

```php
<?php
// cron-backup.php
require_once 'App/Config/config.php';
require_once 'plugins/backup-manager/services/BackupScheduler.php';

$scheduler = new \BackupManager\Services\BackupScheduler();
$ran = $scheduler->runDue();

echo "Processed $ran scheduled backup(s)\n";
```

## Troubleshooting

### Common Issues

1. **Backup Directory Not Writable**
   ```bash
   # Fix permissions
   chmod 755 /path/to/swcms/backups
   chown www-data:www-data /path/to/swcms/backups
   ```

2. **ZIP Extension Missing**
   ```bash
   # Install ZIP extension (Ubuntu/Debian)
   sudo apt-get install php-zip
   sudo systemctl restart apache2
   ```

3. **Memory Limit Errors**
   ```php
   // Increase PHP memory limit in php.ini
   memory_limit = 512M
   
   // Or set in plugin settings
   ini_set('memory_limit', '512M');
   ```

4. **Timeout Issues**
   ```php
   // Increase execution time
   max_execution_time = 600
   
   // Or adjust in backup settings
   'backup_timeout' => 600
   ```

### Debug Mode

Enable debug logging by setting:

```php
// In backup settings
'debug_mode' => true
```

Logs will be written to `/logs/backup-manager.log`

### Performance Optimization

1. **Large Databases**
   - Use database-only backups during peak hours
   - Schedule full backups during off-peak times
   - Consider increasing PHP memory and execution limits

2. **Large File Systems**
   - Use exclude patterns to skip unnecessary files
   - Consider separate file and database backups
   - Monitor disk space usage

3. **Network Storage**
   - Test backup/restore times with remote storage
   - Consider compression vs. transfer time trade-offs
   - Implement proper monitoring and alerting

## Security Considerations

### File Protection

The plugin implements several security measures:

1. **Directory Protection**
   ```apache
   # .htaccess in backup directory
   Order deny,allow
   Deny from all
   ```

2. **Direct Access Prevention**
   ```php
   // index.php in backup directory
   <?php
   // Silence is golden.
   ```

3. **Permission Checks**
   - User must have 'manage_backups' permission
   - Session validation for all operations
   - CSRF protection on forms

### Best Practices

1. **Access Control**
   - Limit backup manager access to trusted administrators
   - Use strong passwords and 2FA where possible
   - Regularly audit user permissions

2. **Backup Security**
   - Store backups in secure, off-site locations
   - Encrypt sensitive backups before storage
   - Use secure transfer methods (SFTP, encrypted cloud storage)

3. **System Security**
   - Keep PHP and extensions updated
   - Monitor backup directory for unauthorized access
   - Implement proper logging and monitoring

## Contributing

This plugin follows the swCMS plugin development standards:

1. **Code Style**: PSR-4 autoloading, PSR-12 coding standards
2. **Architecture**: MVC pattern with service layer
3. **Database**: PDO with prepared statements
4. **Security**: Input validation, output escaping, permission checks
5. **Documentation**: Inline comments and README documentation

## License

This plugin is licensed under the same terms as swCMS.

## Support

For support and bug reports:

1. Check the troubleshooting section above
2. Enable debug mode and check logs
3. Report issues through the swCMS support channels
4. Include relevant error messages and system information

## Changelog

### Version 1.0.0
- Initial release
- Database and file backup functionality
- Scheduling system with cron integration
- Admin interface with dashboard widget
- AJAX-powered management interface
- Configurable settings and email notifications
- Automatic cleanup and retention management
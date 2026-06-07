<?php
/**
 * Plugin Name: Backup Manager
 * Description: Complete backup solution for swCMS - supports database, files, and combined backups with scheduling and management interface
 * Version: 1.0.0
 * Author: swCMS Development Team
 * Author URI: https://swcms.example.com
 * Plugin URI: https://swcms.example.com/plugins/backup-manager
 * Requires: 1.0.0
 * Tested up to: 1.5.0
 * Requires PHP: 7.4.0
 * API Version: 1.0
 * Priority: 15
 * Network: false
 * Depends: 
 * Conflicts: 
 * 
 * Menu Config: {
 *   "items": [
 *     {
 *       "block_key": "tools",
 *       "label": "Backup Manager",
 *       "url": "/admin/backup-manager",
 *       "icon": "fas fa-download",
 *       "permission_key": "manage_backups",
 *       "position": 40
 *     }
 *   ]
 * }
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    exit('Direct access denied');
}

/**
 * BackupManagerPlugin Main Class
 */
class BackupManagerPlugin {
    
    const VERSION = '1.0.0';
    private static $instance = null;
    private $pluginDir;
    private $backupDir;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->pluginDir = __DIR__;
        $this->backupDir = ROOT_PATH . '/backups';
        $this->init();
    }
    
    private function init() {
        $this->createBackupDirectory();
        $this->loadServices();
        $this->registerHooks();
        $this->initializeAdmin();
    }
    
    
    private function createBackupDirectory() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
            
            $htaccess = "Order deny,allow\nDeny from all\n";
            file_put_contents($this->backupDir . '/.htaccess', $htaccess);
            
            $index = "<?php\n// Silence is golden.\n";
            file_put_contents($this->backupDir . '/index.php', $index);
        }
    }
    
    private function loadServices() {
        if (file_exists($this->pluginDir . '/services/BackupService.php')) {
            require_once $this->pluginDir . '/services/BackupService.php';
        }
        if (file_exists($this->pluginDir . '/services/BackupController.php')) {
            require_once $this->pluginDir . '/services/BackupController.php';
        }
        if (file_exists($this->pluginDir . '/services/BackupScheduler.php')) {
            require_once $this->pluginDir . '/services/BackupScheduler.php';
        }
    }
    
    private function registerHooks() {
        if (class_exists('App\Core\HookSystem')) {
            $hookSystem = App\Core\HookSystem::getInstance();
            
            $hookSystem->addAction('init', array(__CLASS__, 'onInit'));
            $hookSystem->addAction('admin_init', array(__CLASS__, 'onAdminInit'));
            $hookSystem->addAction('admin_dashboard_widgets', array(__CLASS__, 'addDashboardWidget'));
            $hookSystem->addAction('admin_head', array(__CLASS__, 'addAdminStyles'));
            $hookSystem->addAction('admin_footer', array(__CLASS__, 'addAdminScripts'));
        }
    }
    
    private function initializeAdmin() {
        if ($this->isAdminArea()) {
            $this->initializeDatabase();
        }
    }
    
    private function isAdminArea() {
        return isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin') !== false;
    }
    
    private function initializeDatabase() {
        try {
            if (class_exists('App\Core\Database\Database')) {
                $db = App\Core\Database\Database::getInstance();
                
                // Check if we're using SQLite or MySQL
                $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
                
                if ($driver === 'sqlite') {
                    // SQLite version
                    $sql = "CREATE TABLE IF NOT EXISTS backup_jobs (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        type TEXT NOT NULL CHECK (type IN ('database', 'files', 'full')),
                        status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'running', 'completed', 'failed')),
                        filename TEXT,
                        file_size INTEGER,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        completed_at DATETIME,
                        error_message TEXT,
                        settings TEXT,
                        created_by INTEGER
                    )";
                    $db->exec($sql);
                    
                    // SQLite indexes
                    $db->exec("CREATE INDEX IF NOT EXISTS idx_backup_jobs_status ON backup_jobs(status)");
                    $db->exec("CREATE INDEX IF NOT EXISTS idx_backup_jobs_created_at ON backup_jobs(created_at)");
                    $db->exec("CREATE INDEX IF NOT EXISTS idx_backup_jobs_type ON backup_jobs(type)");
                    
                    // Create backup_schedules table for SQLite
                    $sql = "CREATE TABLE IF NOT EXISTS backup_schedules (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        type TEXT NOT NULL CHECK (type IN ('database', 'files', 'full')),
                        frequency TEXT NOT NULL CHECK (frequency IN ('daily', 'weekly', 'monthly')),
                        time TEXT NOT NULL,
                        active INTEGER DEFAULT 1,
                        settings TEXT,
                        last_run DATETIME,
                        next_run DATETIME,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                        created_by INTEGER
                    )";
                    $db->exec($sql);
                    
                    // SQLite indexes for schedules
                    $db->exec("CREATE INDEX IF NOT EXISTS idx_backup_schedules_active ON backup_schedules(active)");
                    $db->exec("CREATE INDEX IF NOT EXISTS idx_backup_schedules_next_run ON backup_schedules(next_run)");
                } else {
                    // MySQL version
                    $sql = "CREATE TABLE IF NOT EXISTS backup_jobs (
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
                    )";
                    $db->exec($sql);
                    
                    // Create backup_schedules table for MySQL
                    $sql = "CREATE TABLE IF NOT EXISTS backup_schedules (
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
                    )";
                    $db->exec($sql);
                }
            }
        } catch (Exception $e) {
            error_log('Backup Manager: Database initialization failed - ' . $e->getMessage());
        }
    }
    
    public static function onInit() {
        $settings = self::getSettings();
        if ($settings['enabled']) {
            self::scheduleCleanup();
        }
    }
    
    public static function onAdminInit() {
        // Admin initialization
    }
    
    
    public static function addDashboardWidget() {
        $settings = self::getSettings();
        if (!$settings['enabled'] || !$settings['show_dashboard_widget']) {
            return;
        }
        
        echo self::renderDashboardWidget();
    }
    
    /**
     * Add dashboard widget content to admin dashboard
     */
    public static function addDashboardContent() {
        // This will add content directly to the dashboard
        echo self::renderDashboardWidget();
    }
    
    private static function renderDashboardWidget() {
        try {
            if (class_exists('App\Core\Database\Database')) {
                $db = App\Core\Database\Database::getInstance();
                
                $stmt = $db->prepare("
                    SELECT * FROM backup_jobs 
                    WHERE status = 'completed' 
                    ORDER BY completed_at DESC 
                    LIMIT 1
                ");
                $stmt->execute();
                $latestBackup = $stmt->fetch();
                
                $stmt = $db->prepare("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                        SUM(file_size) as total_size
                    FROM backup_jobs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ");
                $stmt->execute();
                $stats = $stmt->fetch();
                
                ob_start();
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-download me-2"></i>Backup Manager
                                <div class="float-end">
                                    <button class="btn btn-light btn-sm" onclick="createQuickBackup()" title="Quick Backup">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if ($latestBackup): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Last Backup:</span>
                                    <span class="fw-bold"><?php echo date('M j, Y H:i', strtotime($latestBackup['completed_at'])); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-muted">Type:</span>
                                    <span class="badge bg-info"><?php echo ucfirst($latestBackup['type']); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted mb-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No backups found
                                </div>
                            <?php endif; ?>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-0 text-primary"><?php echo $stats['total'] ?? 0; ?></div>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-success"><?php echo $stats['completed'] ?? 0; ?></div>
                                    <small class="text-muted">Success</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-danger"><?php echo $stats['failed'] ?? 0; ?></div>
                                    <small class="text-muted">Failed</small>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="/admin/backup-manager" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-cogs"></i> Manage
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
        } catch (Exception $e) {
            return '<div class="col-md-6 mb-4"><div class="alert alert-danger">Backup widget error: ' . $e->getMessage() . '</div></div>';
        }
    }
    
    public static function addAdminStyles() {
        if (self::isBackupManagerPage()) {
            echo '<link rel="stylesheet" href="/plugins/backup-manager/assets/css/backup-manager.css">';
        }
    }
    
    public static function addAdminScripts() {
        if (self::isBackupManagerPage()) {
            echo '<script src="/plugins/backup-manager/assets/js/backup-manager.js"></script>';
        }
        
        echo '<script>
        function createQuickBackup() {
            if(confirm("Create a quick full backup now?")) {
                fetch("/admin/backup-manager/create", {
                    method: "POST",
                    headers: {"Content-Type": "application/json"},
                    body: JSON.stringify({type: "full", quick: true})
                }).then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert("Backup started successfully!");
                        location.reload();
                    } else {
                        alert("Backup failed: " + data.message);
                    }
                });
            }
        }
        </script>';
    }
    
    private static function isBackupManagerPage() {
        return isset($_GET['page']) && strpos($_GET['page'], 'backup-manager') !== false;
    }
    
    private static function scheduleCleanup() {
        $settings = self::getSettings();
        if ($settings['auto_cleanup'] && $settings['retention_days'] > 0) {
            // This would be handled by cron
        }
    }
    
    public static function getSettings() {
        if (class_exists('App\Helpers\SystemSettingsHelper')) {
            $settings = App\Helpers\SystemSettingsHelper::get('PLUGIN_BACKUP_MANAGER_SETTINGS');
            return $settings ? json_decode($settings, true) : self::getDefaultSettings();
        }
        return self::getDefaultSettings();
    }
    
    public static function getDefaultSettings() {
        return array(
            'enabled' => true,
            'version' => self::VERSION,
            'show_dashboard_widget' => true,
            'auto_cleanup' => true,
            'retention_days' => 30,
            'max_backup_size' => '500MB',
            'backup_timeout' => 300,
            'compression_level' => 6,
            'exclude_patterns' => array(
                'node_modules/*',
                '.git/*',
                'vendor/*',
                '*.log',
                'cache/*',
                'tmp/*'
            ),
            'email_notifications' => false,
            'notification_email' => '',
            'include_uploads' => true,
            'include_themes' => true,
            'include_plugins' => false
        );
    }
    
    public static function getBackupDirectory() {
        return self::getInstance()->backupDir;
    }
    
    public static function getPluginDirectory() {
        return self::getInstance()->pluginDir;
    }
}

// Helper functions
function backup_user_has_permission($capability) {
    // Use SessionHelper to access user role properly
    if (!class_exists('App\Helpers\SessionHelper')) {
        return false;
    }
    
    $userRole = \App\Helpers\SessionHelper::getValue('user_role');
    if (!$userRole) {
        return false;
    }
    
    $allowedRoles = array('super_admin', 'admin', 'editor');
    return in_array($userRole, $allowedRoles);
}

function backup_error_response($message) {
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode(array('success' => false, 'message' => $message));
    } else {
        die($message);
    }
    exit;
}

function is_ajax_request() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function backup_manager_activate() {
    error_log('Backup Manager Plugin activated');
    
    if (class_exists('BackupManagerPlugin')) {
        $defaultSettings = BackupManagerPlugin::getDefaultSettings();
        
        if (class_exists('App\Helpers\SystemSettingsHelper')) {
            App\Helpers\SystemSettingsHelper::set('PLUGIN_BACKUP_MANAGER_SETTINGS', json_encode($defaultSettings));
        }
        
        try {
            BackupManagerPlugin::getInstance();
        } catch (Exception $e) {
            error_log('Backup Manager Plugin initialization failed: ' . $e->getMessage());
        }
    }
}

function backup_manager_deactivate() {
    error_log('Backup Manager Plugin deactivated');
}

// Initialize the plugin
try {
    BackupManagerPlugin::getInstance();
} catch (Exception $e) {
    error_log('Backup Manager Plugin failed to initialize: ' . $e->getMessage());
}
?>
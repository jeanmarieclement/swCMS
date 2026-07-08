<?php

namespace App\Controllers\Admin;

use App\Helpers\RedirectHelper;

class BackupManagerController extends AdminController
{
    private $backupService;
    private $backupController;
    private $backupScheduler;

    public function __construct($params = [])
    {
        parent::__construct($params);
        
        // L'autenticazione è già gestita da AdminController
        $this->initializeBackupServices();
    }
    
    private function initializeBackupServices()
    {
        // Carica i servizi del plugin backup-manager
        $pluginPath = __DIR__ . '/../';
        
        // Include main plugin file first to load helper functions
        if (file_exists($pluginPath . 'backup-manager.php')) {
            require_once $pluginPath . 'backup-manager.php';
        }
        
        if (file_exists($pluginPath . 'services/BackupService.php')) {
            require_once $pluginPath . 'services/BackupService.php';
        }
        if (file_exists($pluginPath . 'services/BackupController.php')) {
            require_once $pluginPath . 'services/BackupController.php';
        }
        if (file_exists($pluginPath . 'services/BackupScheduler.php')) {
            require_once $pluginPath . 'services/BackupScheduler.php';
        }
        
        // Inizializza i servizi se le classi esistono
        if (class_exists('BackupManager\Services\BackupService')) {
            $this->backupService = new \BackupManager\Services\BackupService();
        }
        if (class_exists('BackupManager\Services\BackupController')) {
            $this->backupController = new \BackupManager\Services\BackupController();
        }
        if (class_exists('BackupManager\Services\BackupScheduler')) {
            $this->backupScheduler = new \BackupManager\Services\BackupScheduler();
        }
    }

    /**
     * Main backup manager dashboard
     */
    public function indexAction()
    {
        try {
            // Get backup statistics
            $backups = $this->backupService->getBackupList(10);
            $schedules = $this->backupScheduler->getActiveSchedules();
            
            // Get statistics
            $db = \App\Core\Database\Database::getInstance();
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total_backups,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_backups,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_backups,
                    SUM(file_size) as total_size
                FROM backup_jobs
            ");
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get system information
            $systemInfo = [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'zip_available' => class_exists('ZipArchive'),
                'backup_dir_writable' => is_writable(ROOT_PATH . '/backups')
            ];
            
            $this->render('backup-manager/index', [
                'page_title' => 'Backup Manager',
                'backups' => $backups,
                'schedules' => $schedules,
                'stats' => $stats,
                'system_info' => $systemInfo
            ]);
            
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Error loading backup manager: ' . $e->getMessage());
            
            // Get system information even on error
            $systemInfo = [
                'php_version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'zip_available' => class_exists('ZipArchive'),
                'backup_dir_writable' => is_writable(ROOT_PATH . '/backups')
            ];
            
            $this->render('backup-manager/index', [
                'page_title' => 'Backup Manager',
                'backups' => [],
                'schedules' => [],
                'stats' => [],
                'system_info' => $systemInfo
            ]);
        }
    }
    
    /**
     * Handle AJAX backup creation
     */
    public function createAction()
    {
        $this->backupController->create();
    }
    
    /**
     * Handle backup download
     */
    public function downloadAction()
    {
        $this->backupController->download();
    }
    
    /**
     * Handle backup deletion
     */
    public function deleteAction()
    {
        $this->backupController->delete();
    }
    
    /**
     * Handle backup restore
     */
    public function restoreAction()
    {
        $this->backupController->restore();
    }
    
    /**
     * Handle schedule management
     */
    public function scheduleAction()
    {
        $this->backupController->schedule();
    }
    
    /**
     * Get backup list (AJAX)
     */
    public function listAction()
    {
        $this->backupController->list();
    }
    
    /**
     * Get backup statistics (AJAX)
     */
    public function statsAction()
    {
        $this->backupController->stats();
    }
    
    /**
     * Clean old backups
     */
    public function cleanupAction()
    {
        $this->backupController->cleanup();
    }
    
    /**
     * Clean temporary files
     */
    public function cleanupTempAction()
    {
        try {
            $cleaned = $this->backupService->cleanupTemporaryFiles();
            
            $this->setFlashMessage('success', "Cleaned up $cleaned temporary files");
            \App\Helpers\RedirectHelper::redirect('/admin/backup-manager');
            
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Cleanup failed: ' . $e->getMessage());
            \App\Helpers\RedirectHelper::redirect('/admin/backup-manager');
        }
    }
    
    /**
     * Backup settings page
     */
    public function settingsAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf('/admin/backup-manager/settings', 'backup settings save');
            try {
                $settings = [
                    'enabled' => isset($_POST['enabled']),
                    'show_dashboard_widget' => isset($_POST['show_dashboard_widget']),
                    'auto_cleanup' => isset($_POST['auto_cleanup']),
                    'retention_days' => (int)($_POST['retention_days'] ?? 30),
                    'max_backup_size' => $_POST['max_backup_size'] ?? '500MB',
                    'backup_timeout' => (int)($_POST['backup_timeout'] ?? 300),
                    'compression_level' => (int)($_POST['compression_level'] ?? 6),
                    'email_notifications' => isset($_POST['email_notifications']),
                    'notification_email' => $_POST['notification_email'] ?? '',
                    'include_uploads' => isset($_POST['include_uploads']),
                    'include_themes' => isset($_POST['include_themes']),
                    'include_plugins' => isset($_POST['include_plugins']),
                    'exclude_patterns' => array_filter(array_map('trim', explode("\n", $_POST['exclude_patterns'] ?? '')))
                ];
                
                \App\Helpers\SystemSettingsHelper::set('PLUGIN_BACKUP_MANAGER_SETTINGS', json_encode($settings));
                
                $this->setFlashMessage('success', 'Backup settings saved successfully');
                RedirectHelper::redirect('/admin/backup-manager/settings');
                return;
                
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Error saving settings: ' . $e->getMessage());
            }
        }
        
        // Get current settings
        $settingsJson = \App\Helpers\SystemSettingsHelper::get('PLUGIN_BACKUP_MANAGER_SETTINGS');
        $settings = $settingsJson ? json_decode($settingsJson, true) : $this->getDefaultSettings();
        
        // Get system information for template
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'zip_available' => class_exists('ZipArchive'),
            'backup_dir_writable' => is_writable(ROOT_PATH . '/backups')
        ];
        
        $this->render('backup-manager/settings', [
            'page_title' => 'Backup Settings',
            'settings' => $settings,
            'system_info' => $systemInfo
        ]);
    }
    
    /**
     * Schedules management page
     */
    public function schedulesAction()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireCsrf('/admin/backup-manager/schedules', 'backup schedule change');
            $action = $_POST['action'] ?? '';
            
            try {
                switch ($action) {
                    case 'create':
                        $this->createSchedule($_POST);
                        break;
                    case 'edit':
                        $this->editSchedule($_POST);
                        break;
                    case 'delete':
                        $this->deleteSchedule($_POST['schedule_id']);
                        break;
                    case 'toggle':
                        $this->toggleSchedule($_POST['schedule_id']);
                        break;
                    default:
                        throw new \Exception('Invalid action');
                }
                
                RedirectHelper::redirect('/admin/backup-manager/schedules');
                return;
                
            } catch (\Exception $e) {
                $this->setFlashMessage('error', 'Schedule operation failed: ' . $e->getMessage());
            }
        }
        
        // Get all schedules
        $db = \App\Core\Database\Database::getInstance();
        $stmt = $db->query("SELECT * FROM backup_schedules ORDER BY created_at DESC");
        $schedules = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $this->render('backup-manager/schedules', [
            'page_title' => 'Backup Schedules',
            'schedules' => $schedules
        ]);
    }
    
    /**
     * Create backup schedule
     */
    private function createSchedule($data)
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? 'full';
        $frequency = $data['frequency'] ?? 'daily';
        $time = $data['time'] ?? '00:00';
        
        if (empty($name)) {
            throw new \Exception('Schedule name is required');
        }
        
        // Calculate next run
        $nextRun = $this->calculateNextRun($frequency, $time);
        
        $stmt = $db->prepare("
            INSERT INTO backup_schedules (name, type, frequency, time, next_run, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name,
            $type,
            $frequency,
            $time,
            $nextRun,
            $_SESSION['user_id'] ?? null
        ]);
        
        $this->setFlashMessage('success', 'Backup schedule created successfully');
    }
    
    /**
     * Edit backup schedule
     */
    private function editSchedule($data)
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $scheduleId = $data['schedule_id'] ?? null;
        $name = trim($data['name'] ?? '');
        $type = $data['type'] ?? 'full';
        $frequency = $data['frequency'] ?? 'daily';
        $time = $data['time'] ?? '00:00';
        
        if (!$scheduleId) {
            throw new \Exception('Schedule ID is required');
        }
        
        if (empty($name)) {
            throw new \Exception('Schedule name is required');
        }
        
        // Calculate next run based on new frequency and time
        $nextRun = $this->calculateNextRun($frequency, $time);
        
        $stmt = $db->prepare("
            UPDATE backup_schedules 
            SET name = ?, type = ?, frequency = ?, time = ?, next_run = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name,
            $type,
            $frequency,
            $time,
            $nextRun,
            $scheduleId
        ]);
        
        $this->setFlashMessage('success', 'Backup schedule updated successfully');
    }
    
    /**
     * Delete backup schedule
     */
    private function deleteSchedule($scheduleId)
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $stmt = $db->prepare("DELETE FROM backup_schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        
        $this->setFlashMessage('success', 'Backup schedule deleted successfully');
    }
    
    /**
     * Toggle schedule active status
     */
    private function toggleSchedule($scheduleId)
    {
        $db = \App\Core\Database\Database::getInstance();
        
        $stmt = $db->prepare("UPDATE backup_schedules SET active = NOT active WHERE id = ?");
        $stmt->execute([$scheduleId]);
        
        $this->setFlashMessage('success', 'Schedule status updated successfully');
    }
    
    /**
     * Calculate next run time
     */
    private function calculateNextRun($frequency, $time)
    {
        $now = new \DateTime();
        $nextRun = new \DateTime();
        
        // Set the time
        list($hour, $minute) = explode(':', $time);
        $nextRun->setTime($hour, $minute, 0);
        
        // If the time has passed today, move to next occurrence
        if ($nextRun <= $now) {
            switch ($frequency) {
                case 'daily':
                    $nextRun->modify('+1 day');
                    break;
                case 'weekly':
                    $nextRun->modify('+1 week');
                    break;
                case 'monthly':
                    $nextRun->modify('+1 month');
                    break;
            }
        }
        
        return $nextRun->format('Y-m-d H:i:s');
    }
    
    /**
     * Get default settings
     */
    private function getDefaultSettings()
    {
        return [
            'enabled' => true,
            'show_dashboard_widget' => true,
            'auto_cleanup' => true,
            'retention_days' => 30,
            'max_backup_size' => '500MB',
            'backup_timeout' => 300,
            'compression_level' => 6,
            'email_notifications' => false,
            'notification_email' => '',
            'include_uploads' => true,
            'include_themes' => true,
            'include_plugins' => false,
            'exclude_patterns' => [
                'node_modules/*',
                '.git/*',
                'vendor/*',
                '*.log',
                'cache/*',
                'tmp/*',
                'backups/*'
            ]
        ];
    }
}
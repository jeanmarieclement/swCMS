<?php
/**
 * BackupScheduler - Handle scheduled backup operations
 */

namespace BackupManager\Services;

use App\Core\Database\Database;

class BackupScheduler {
    
    private $db;
    private $backupService;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->backupService = new BackupService();
    }
    
    /**
     * Run all due backup schedules
     */
    public function runDue() {
        try {
            $schedules = $this->getDueSchedules();
            
            foreach ($schedules as $schedule) {
                $this->runSchedule($schedule);
            }
            
            return count($schedules);
            
        } catch (\Exception $e) {
            error_log('Backup Scheduler Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get schedules that are due to run
     */
    private function getDueSchedules() {
        $stmt = $this->db->prepare("
            SELECT * FROM backup_schedules 
            WHERE active = 1 AND next_run <= NOW()
            ORDER BY next_run ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Run a specific schedule
     */
    private function runSchedule($schedule) {
        try {
            // Parse schedule settings
            $settings = json_decode($schedule['settings'], true) ?: [];
            
            // Create backup
            $result = $this->backupService->createBackup($schedule['type'], $settings);
            
            if ($result['success']) {
                // Update last run and calculate next run
                $this->updateScheduleRun($schedule['id'], true);
                
                // Send notification if enabled
                $this->sendNotification($schedule, $result, true);
                
                error_log("Scheduled backup completed: {$schedule['name']} ({$schedule['type']})");
            } else {
                // Update last run but don't advance next run on failure
                $this->updateScheduleRun($schedule['id'], false);
                
                // Send failure notification
                $this->sendNotification($schedule, $result, false);
                
                error_log("Scheduled backup failed: {$schedule['name']} - {$result['error']}");
            }
            
        } catch (\Exception $e) {
            $this->updateScheduleRun($schedule['id'], false);
            error_log("Scheduled backup exception: {$schedule['name']} - " . $e->getMessage());
        }
    }
    
    /**
     * Update schedule run information
     */
    private function updateScheduleRun($scheduleId, $success) {
        // Get schedule info
        $stmt = $this->db->prepare("SELECT frequency, time FROM backup_schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        $schedule = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($schedule) {
            $nextRun = $success ? $this->calculateNextRun($schedule['frequency'], $schedule['time']) : null;
            
            $sql = "UPDATE backup_schedules SET last_run = NOW()";
            $params = [];
            
            if ($nextRun) {
                $sql .= ", next_run = ?";
                $params[] = $nextRun;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $scheduleId;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
    }
    
    /**
     * Calculate next run time
     */
    private function calculateNextRun($frequency, $time) {
        $now = new \DateTime();
        $nextRun = new \DateTime();
        
        // Set the time
        list($hour, $minute) = explode(':', $time);
        $nextRun->setTime($hour, $minute, 0);
        
        // Calculate next occurrence
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
        
        return $nextRun->format('Y-m-d H:i:s');
    }
    
    /**
     * Send notification about backup result
     */
    private function sendNotification($schedule, $result, $success) {
        // Get plugin settings
        $settings = \App\Helpers\SystemSettingsHelper::get('PLUGIN_BACKUP_MANAGER_SETTINGS');
        $pluginSettings = $settings ? json_decode($settings, true) : [];
        
        if (!empty($pluginSettings['email_notifications']) && !empty($pluginSettings['notification_email'])) {
            $subject = $success ? 
                "Backup Completed: {$schedule['name']}" : 
                "Backup Failed: {$schedule['name']}";
            
            $message = $this->buildNotificationMessage($schedule, $result, $success);
            
            // Use system email function if available
            if (function_exists('mail')) {
                $this->sendEmail($pluginSettings['notification_email'], $subject, $message);
            }
        }
    }
    
    /**
     * Build notification email message
     */
    private function buildNotificationMessage($schedule, $result, $success) {
        $message = "Backup Schedule: {$schedule['name']}\n";
        $message .= "Type: {$schedule['type']}\n";
        $message .= "Frequency: {$schedule['frequency']}\n";
        $message .= "Time: " . date('Y-m-d H:i:s') . "\n\n";
        
        if ($success) {
            $message .= "Status: COMPLETED\n";
            $message .= "Filename: {$result['filename']}\n";
            $message .= "Size: " . $this->formatFileSize($result['file_size']) . "\n";
        } else {
            $message .= "Status: FAILED\n";
            $message .= "Error: {$result['error']}\n";
        }
        
        $message .= "\n--\n";
        $message .= "This is an automated message from swCMS Backup Manager.\n";
        
        return $message;
    }
    
    /**
     * Send email notification
     */
    private function sendEmail($to, $subject, $message) {
        try {
            $headers = [
                'From: noreply@' . $_SERVER['HTTP_HOST'],
                'Reply-To: noreply@' . $_SERVER['HTTP_HOST'],
                'Content-Type: text/plain; charset=UTF-8'
            ];
            
            mail($to, $subject, $message, implode("\r\n", $headers));
        } catch (\Exception $e) {
            error_log('Backup notification email failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Format file size
     */
    private function formatFileSize($size) {
        if ($size === null || $size === 0) {
            return '0 B';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        
        return number_format($size / pow(1024, $power), 1, '.', ',') . ' ' . $units[$power];
    }
    
    /**
     * Get all active schedules
     */
    public function getActiveSchedules() {
        $stmt = $this->db->prepare("
            SELECT * FROM backup_schedules 
            WHERE active = 1 
            ORDER BY next_run ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get schedule statistics
     */
    public function getScheduleStats() {
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_schedules,
                SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_schedules,
                MIN(next_run) as next_scheduled
            FROM backup_schedules
        ");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Check if any schedules are overdue
     */
    public function getOverdueSchedules() {
        $stmt = $this->db->prepare("
            SELECT * FROM backup_schedules 
            WHERE active = 1 AND next_run < DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY next_run ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
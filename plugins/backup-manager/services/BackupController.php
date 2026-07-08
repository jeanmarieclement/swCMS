<?php
/**
 * BackupController - Handle AJAX requests and admin operations for backup management
 */

namespace BackupManager\Services;

class BackupController {
    
    private $backupService;
    
    public function __construct() {
        $this->backupService = new BackupService();
    }
    
    /**
     * Handle backup creation request
     */
    public function create() {
        $this->verifyNonce();
        
        try {
            $input = $this->getJsonInput();
            $type = $input['type'] ?? 'full';
            $options = $input['options'] ?? [];
            
            // Validate backup type
            if (!in_array($type, ['database', 'files', 'full'])) {
                throw new \Exception('Invalid backup type');
            }
            
            // Create backup
            $result = $this->backupService->createBackup($type, $options);
            
            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Backup created successfully',
                    'backup' => [
                        'filename' => $result['filename'],
                        'size' => $this->formatFileSize($result['file_size']),
                        'type' => $type
                    ]
                ]);
            } else {
                throw new \Exception($result['error']);
            }
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle backup download request
     */
    public function download() {
        try {
            $backupId = \App\Helpers\RequestHelper::get('id', null, 'int');
            if (!$backupId) {
                throw new \Exception('Backup ID required');
            }
            
            $filepath = $this->backupService->getBackupFilePath($backupId);
            if (!$filepath) {
                throw new \Exception('Backup file not found');
            }
            
            // Send file download headers
            $filename = basename($filepath);
            $filesize = filesize($filepath);
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . $filesize);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Stream file content
            $handle = fopen($filepath, 'rb');
            while (!feof($handle)) {
                echo fread($handle, 8192);
                ob_flush();
                flush();
            }
            fclose($handle);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(404);
            echo 'Error: ' . $e->getMessage();
            exit;
        }
    }
    
    /**
     * Handle backup deletion request
     */
    public function delete() {
        $this->verifyNonce();
        
        // Start output buffering to prevent any unwanted output
        ob_start();
        
        try {
            $input = $this->getJsonInput();
            $backupId = $input['id'] ?? null;
            
            if (!$backupId) {
                throw new \Exception('Backup ID required');
            }
            
            $result = $this->backupService->deleteBackup($backupId);
            
            // Clean any captured output
            ob_end_clean();
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Backup deleted successfully'
                ]);
            } else {
                throw new \Exception('Failed to delete backup');
            }
            
        } catch (\Exception $e) {
            // Clean any captured output in case of error
            ob_end_clean();
            
            $this->jsonResponse([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle backup restore request
     */
    public function restore() {
        $this->verifyNonce();
        
        try {
            $input = $this->getJsonInput();
            $backupId = $input['id'] ?? null;
            
            if (!$backupId) {
                throw new \Exception('Backup ID required');
            }
            
            // Note: Restore functionality would be implemented here
            // For now, we'll return a placeholder response
            $this->jsonResponse([
                'success' => false,
                'message' => 'Restore functionality not yet implemented'
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle backup schedule management
     */
    public function schedule() {
        $this->verifyNonce();
        
        try {
            $input = $this->getJsonInput();
            $action = $input['action'] ?? 'create';
            
            switch ($action) {
                case 'create':
                    $this->createSchedule($input);
                    break;
                case 'update':
                    $this->updateSchedule($input);
                    break;
                case 'delete':
                    $this->deleteSchedule($input);
                    break;
                case 'toggle':
                    $this->toggleSchedule($input);
                    break;
                default:
                    throw new \Exception('Invalid schedule action');
            }
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Schedule operation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get backup list
     */
    public function list() {
        try {
            $page = max(1, (int) \App\Helpers\RequestHelper::get('page', 1, 'int'));
            $limit = max(1, min(100, (int) \App\Helpers\RequestHelper::get('limit', 20, 'int')));
            $offset = ($page - 1) * $limit;
            
            $backups = $this->backupService->getBackupList($limit, $offset);
            
            // Format backup data
            foreach ($backups as &$backup) {
                $backup['formatted_size'] = $this->formatFileSize($backup['file_size']);
                $backup['created_ago'] = $this->timeAgo($backup['created_at']);
                
                if ($backup['completed_at']) {
                    $backup['completed_ago'] = $this->timeAgo($backup['completed_at']);
                }
            }
            
            $this->jsonResponse([
                'success' => true,
                'backups' => $backups,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'has_more' => count($backups) === $limit
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get backup list: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get backup statistics
     */
    public function stats() {
        try {
            $db = \App\Core\Database\Database::getInstance();
            
            // Get backup statistics
            $stmt = $db->query("
                SELECT 
                    COUNT(*) as total_backups,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_backups,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_backups,
                    SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running_backups,
                    SUM(file_size) as total_size,
                    AVG(file_size) as avg_size
                FROM backup_jobs
            ");
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get latest backup
            $stmt = $db->query("
                SELECT * FROM backup_jobs 
                WHERE status = 'completed' 
                ORDER BY completed_at DESC 
                LIMIT 1
            ");
            $latestBackup = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get schedule info
            $stmt = $db->query("
                SELECT COUNT(*) as total_schedules,
                       SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active_schedules
                FROM backup_schedules
            ");
            $scheduleStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'stats' => [
                    'total_backups' => $stats['total_backups'],
                    'completed_backups' => $stats['completed_backups'],
                    'failed_backups' => $stats['failed_backups'],
                    'running_backups' => $stats['running_backups'],
                    'total_size' => $this->formatFileSize($stats['total_size']),
                    'avg_size' => $this->formatFileSize($stats['avg_size']),
                    'latest_backup' => $latestBackup ? [
                        'id' => $latestBackup['id'],
                        'type' => $latestBackup['type'],
                        'completed_at' => $latestBackup['completed_at'],
                        'completed_ago' => $this->timeAgo($latestBackup['completed_at']),
                        'size' => $this->formatFileSize($latestBackup['file_size'])
                    ] : null,
                    'schedules' => [
                        'total' => $scheduleStats['total_schedules'],
                        'active' => $scheduleStats['active_schedules']
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clean old backups
     */
    public function cleanup() {
        $this->verifyNonce();
        
        try {
            $input = $this->getJsonInput();
            $retentionDays = $input['retention_days'] ?? 30;
            
            $deleted = $this->backupService->cleanOldBackups($retentionDays);
            
            $this->jsonResponse([
                'success' => true,
                'message' => "Cleaned up $deleted old backup(s)",
                'deleted_count' => $deleted
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create backup schedule
     */
    private function createSchedule($input) {
        $db = \App\Core\Database\Database::getInstance();
        
        $name = $input['name'] ?? '';
        $type = $input['type'] ?? 'full';
        $frequency = $input['frequency'] ?? 'daily';
        $time = $input['time'] ?? '00:00';
        $settings = $input['settings'] ?? [];
        
        if (empty($name)) {
            throw new \Exception('Schedule name is required');
        }
        
        // Calculate next run time
        $nextRun = $this->calculateNextRun($frequency, $time);
        
        $stmt = $db->prepare("
            INSERT INTO backup_schedules (name, type, frequency, time, settings, next_run, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name,
            $type,
            $frequency,
            $time,
            json_encode($settings),
            $nextRun,
            $_SESSION['user_id'] ?? null
        ]);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Backup schedule created successfully',
            'schedule_id' => $db->lastInsertId()
        ]);
    }
    
    /**
     * Update backup schedule
     */
    private function updateSchedule($input) {
        $db = \App\Core\Database\Database::getInstance();
        
        $scheduleId = $input['id'] ?? null;
        if (!$scheduleId) {
            throw new \Exception('Schedule ID required');
        }
        
        $name = $input['name'] ?? '';
        $type = $input['type'] ?? 'full';
        $frequency = $input['frequency'] ?? 'daily';
        $time = $input['time'] ?? '00:00';
        $settings = $input['settings'] ?? [];
        
        // Calculate next run time
        $nextRun = $this->calculateNextRun($frequency, $time);
        
        $stmt = $db->prepare("
            UPDATE backup_schedules 
            SET name = ?, type = ?, frequency = ?, time = ?, settings = ?, next_run = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name,
            $type,
            $frequency,
            $time,
            json_encode($settings),
            $nextRun,
            $scheduleId
        ]);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Backup schedule updated successfully'
        ]);
    }
    
    /**
     * Delete backup schedule
     */
    private function deleteSchedule($input) {
        $db = \App\Core\Database\Database::getInstance();
        
        $scheduleId = $input['id'] ?? null;
        if (!$scheduleId) {
            throw new \Exception('Schedule ID required');
        }
        
        $stmt = $db->prepare("DELETE FROM backup_schedules WHERE id = ?");
        $stmt->execute([$scheduleId]);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Backup schedule deleted successfully'
        ]);
    }
    
    /**
     * Toggle backup schedule active status
     */
    private function toggleSchedule($input) {
        $db = \App\Core\Database\Database::getInstance();
        
        $scheduleId = $input['id'] ?? null;
        if (!$scheduleId) {
            throw new \Exception('Schedule ID required');
        }
        
        $stmt = $db->prepare("UPDATE backup_schedules SET active = NOT active WHERE id = ?");
        $stmt->execute([$scheduleId]);
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Backup schedule status updated'
        ]);
    }
    
    /**
     * Calculate next run time for schedule
     */
    private function calculateNextRun($frequency, $time) {
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
     * Get JSON input from request
     */
    private function getJsonInput() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON input');
        }
        
        return $data;
    }
    
    /**
     * Send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Verify nonce for security
     */
    private function verifyNonce() {
        // Basic security check - in a real implementation, you'd verify a proper nonce
        if (!isset($_SESSION['user_id'])) {
            throw new \Exception('Authentication required');
        }
        
        if (!backup_user_has_permission('manage_backups')) {
            throw new \Exception('Insufficient permissions');
        }
    }
    
    /**
     * Format file size in human readable format
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
     * Calculate time ago
     */
    private function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time / 60) . ' minutes ago';
        if ($time < 86400) return floor($time / 3600) . ' hours ago';
        if ($time < 2592000) return floor($time / 86400) . ' days ago';
        if ($time < 31536000) return floor($time / 2592000) . ' months ago';
        
        return floor($time / 31536000) . ' years ago';
    }
}
/**
 * Backup Manager JavaScript
 * Handles UI interactions and AJAX requests for backup management
 */

class BackupManager {
    constructor() {
        this.init();
        this.bindEvents();
        this.loadBackupList();
        this.startPolling();
    }
    
    init() {
        this.baseUrl = '/admin/backup-manager';
        this.pollingInterval = 5000; // 5 seconds
        this.pollingTimer = null;
        this.isPolling = false;
    }
    
    bindEvents() {
        // Backup creation buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-backup-type]')) {
                const type = e.target.dataset.backupType;
                this.createBackup(type);
            }
            
            if (e.target.matches('[data-backup-download]')) {
                const backupId = e.target.dataset.backupDownload;
                this.downloadBackup(backupId);
            }
            
            if (e.target.matches('[data-backup-delete]')) {
                const backupId = e.target.dataset.backupDelete;
                this.deleteBackup(backupId);
            }
            
            if (e.target.matches('[data-backup-restore]')) {
                const backupId = e.target.dataset.backupRestore;
                this.restoreBackup(backupId);
            }
            
            if (e.target.matches('[data-cleanup-backups]')) {
                this.cleanupBackups();
            }
            
            if (e.target.matches('[data-schedule-toggle]')) {
                const scheduleId = e.target.dataset.scheduleToggle;
                this.toggleSchedule(scheduleId);
            }
        });
        
        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.matches('#createScheduleForm')) {
                e.preventDefault();
                this.createSchedule(new FormData(e.target));
            }
        });
        
        // Real-time updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPolling();
            } else {
                this.startPolling();
            }
        });
    }
    
    /**
     * Create a new backup
     */
    async createBackup(type, options = {}) {
        try {
            this.showLoading(`Creating ${type} backup...`);
            
            const response = await this.request('POST', '/create', {
                type: type,
                options: options
            });
            
            if (response.success) {
                this.showToast('success', response.message);
                this.loadBackupList();
                this.updateStats();
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Download backup file
     */
    downloadBackup(backupId) {
        const url = `${this.baseUrl}/download?id=${backupId}`;
        const link = document.createElement('a');
        link.href = url;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    
    /**
     * Delete backup
     */
    async deleteBackup(backupId) {
        if (!confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
            return;
        }
        
        try {
            this.showLoading('Deleting backup...');
            
            const response = await this.request('POST', '/delete', {
                id: backupId
            });
            
            if (response.success) {
                this.showToast('success', response.message);
                this.loadBackupList();
                this.updateStats();
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Restore backup
     */
    async restoreBackup(backupId) {
        if (!confirm('Are you sure you want to restore this backup? This will overwrite your current data.')) {
            return;
        }
        
        try {
            this.showLoading('Restoring backup...');
            
            const response = await this.request('POST', '/restore', {
                id: backupId
            });
            
            if (response.success) {
                this.showToast('success', response.message);
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Cleanup old backups
     */
    async cleanupBackups() {
        if (!confirm('This will delete backups older than the retention period. Continue?')) {
            return;
        }
        
        try {
            this.showLoading('Cleaning up old backups...');
            
            const response = await this.request('POST', '/cleanup', {
                retention_days: 30 // Could be made configurable
            });
            
            if (response.success) {
                this.showToast('success', response.message);
                this.loadBackupList();
                this.updateStats();
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Create backup schedule
     */
    async createSchedule(formData) {
        try {
            this.showLoading('Creating schedule...');
            
            const data = {
                action: 'create',
                name: formData.get('name'),
                type: formData.get('type'),
                frequency: formData.get('frequency'),
                time: formData.get('time'),
                settings: {
                    compression: formData.get('compression') === 'on',
                    email_notification: formData.get('email_notification') === 'on'
                }
            };
            
            const response = await this.request('POST', '/schedule', data);
            
            if (response.success) {
                this.showToast('success', response.message);
                this.loadScheduleList();
                document.getElementById('createScheduleForm').reset();
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Toggle schedule active status
     */
    async toggleSchedule(scheduleId) {
        try {
            const response = await this.request('POST', '/schedule', {
                action: 'toggle',
                id: scheduleId
            });
            
            if (response.success) {
                this.showToast('success', response.message);
                this.loadScheduleList();
            } else {
                throw new Error(response.message);
            }
            
        } catch (error) {
            this.showToast('error', error.message);
        }
    }
    
    /**
     * Load backup list
     */
    async loadBackupList() {
        try {
            const response = await this.request('GET', '/list');
            
            if (response.success) {
                this.renderBackupList(response.backups);
            }
            
        } catch (error) {
            console.error('Failed to load backup list:', error);
        }
    }
    
    /**
     * Load schedule list
     */
    async loadScheduleList() {
        // This would be implemented if schedules are loaded via AJAX
        // For now, page refresh handles schedule updates
        location.reload();
    }
    
    /**
     * Update statistics
     */
    async updateStats() {
        try {
            const response = await this.request('GET', '/stats');
            
            if (response.success) {
                this.renderStats(response.stats);
            }
            
        } catch (error) {
            console.error('Failed to update stats:', error);
        }
    }
    
    /**
     * Render backup list
     */
    renderBackupList(backups) {
        const container = document.getElementById('backupList');
        if (!container) return;
        
        if (backups.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-download fa-3x text-muted mb-3"></i>
                    <h5>No Backups Found</h5>
                    <p class="text-muted">Create your first backup to get started.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = backups.map(backup => `
            <div class="backup-card backup-${backup.status} mb-3">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="backup-type-badge backup-type-${backup.type}">
                                ${backup.type.toUpperCase()}
                            </span>
                            <span class="status-badge status-${backup.status} ms-2">
                                ${backup.status.toUpperCase()}
                            </span>
                        </div>
                        <small class="text-muted">
                            ${backup.created_ago || backup.created_at}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-2">
                                <strong>File:</strong> ${backup.filename || 'N/A'}
                            </div>
                            <div class="mb-2">
                                <strong>Size:</strong> ${backup.formatted_size || '0 B'}
                            </div>
                            ${backup.completed_at ? `
                                <div class="mb-2">
                                    <strong>Completed:</strong> ${backup.completed_ago}
                                </div>
                            ` : ''}
                            ${backup.error_message ? `
                                <div class="text-danger">
                                    <strong>Error:</strong> ${backup.error_message}
                                </div>
                            ` : ''}
                        </div>
                        <div class="col-md-4">
                            <div class="backup-actions">
                                ${backup.status === 'completed' ? `
                                    <button class="btn btn-backup btn-sm" data-backup-download="${backup.id}">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <button class="btn btn-backup-secondary btn-sm" data-backup-restore="${backup.id}">
                                        <i class="fas fa-upload"></i> Restore
                                    </button>
                                ` : ''}
                                <button class="btn btn-backup-danger btn-sm" data-backup-delete="${backup.id}">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    ${backup.status === 'running' ? `
                        <div class="backup-progress indeterminate mt-3">
                            <div class="progress-bar"></div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Render statistics
     */
    renderStats(stats) {
        // Update dashboard widget stats
        const widgets = {
            'total-backups': stats.total_backups,
            'completed-backups': stats.completed_backups,
            'failed-backups': stats.failed_backups,
            'total-size': stats.total_size
        };
        
        Object.entries(widgets).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = value;
            }
        });
        
        // Update latest backup info
        if (stats.latest_backup) {
            const element = document.getElementById('latest-backup');
            if (element) {
                element.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <span>Latest Backup:</span>
                        <span class="fw-bold">${stats.latest_backup.completed_ago}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Type:</span>
                        <span class="badge bg-info">${stats.latest_backup.type}</span>
                    </div>
                `;
            }
        }
    }
    
    /**
     * Start polling for updates
     */
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.pollingTimer = setInterval(() => {
            this.loadBackupList();
            this.updateStats();
        }, this.pollingInterval);
    }
    
    /**
     * Stop polling
     */
    stopPolling() {
        this.isPolling = false;
        if (this.pollingTimer) {
            clearInterval(this.pollingTimer);
            this.pollingTimer = null;
        }
    }
    
    /**
     * Make AJAX request
     */
    async request(method, endpoint, data = null) {
        const url = this.baseUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }
        
        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    }
    
    /**
     * Show loading indicator
     */
    showLoading(message = 'Loading...') {
        // Create or update loading overlay
        let overlay = document.getElementById('backup-loading');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'backup-loading';
            overlay.className = 'backup-loading-overlay';
            document.body.appendChild(overlay);
        }
        
        overlay.innerHTML = `
            <div class="backup-loading-content">
                <div class="backup-spinner"></div>
                <div class="backup-loading-text">${message}</div>
            </div>
        `;
        overlay.style.display = 'flex';
    }
    
    /**
     * Hide loading indicator
     */
    hideLoading() {
        const overlay = document.getElementById('backup-loading');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    /**
     * Show toast notification
     */
    showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `backup-toast ${type}`;
        toast.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-sm" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 5000);
    }
    
    /**
     * Get icon for toast type
     */
    getToastIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// CSS for loading overlay and spinner (injected via JavaScript)
const style = document.createElement('style');
style.textContent = `
    .backup-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    
    .backup-loading-content {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
        min-width: 200px;
    }
    
    .backup-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: backup-spin 1s linear infinite;
        margin: 0 auto 1rem;
    }
    
    @keyframes backup-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .backup-loading-text {
        color: #495057;
        font-weight: 500;
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('/admin/backup-manager')) {
        window.backupManager = new BackupManager();
    }
});

// Quick backup function for dashboard widget
function createQuickBackup() {
    if (window.backupManager) {
        window.backupManager.createBackup('full');
    } else {
        // Fallback for dashboard widget
        if (confirm('Create a quick full backup now?')) {
            fetch('/admin/backup-manager/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: 'full', quick: true })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Backup started successfully!');
                    location.reload();
                } else {
                    alert('Backup failed: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    }
}
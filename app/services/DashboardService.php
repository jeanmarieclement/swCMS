<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Page;
use App\Models\User;
use App\Models\Comments;
use App\Models\Settings;
use App\Helpers\SystemSettingsHelper;

/**
 * Dashboard Service
 * Provides data for the admin dashboard
 */
class DashboardService
{
    private $postModel;
    private $pageModel;
    private $userModel;
    private $commentsModel;
    private $settingsModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->pageModel = new Page();
        $this->userModel = new User();
        $this->commentsModel = new Comments();
        $this->settingsModel = new Settings();
    }

    /**
     * Get dashboard statistics
     * 
     * @return array Statistics data
     */
    public function getStats()
    {
        return [
            'articles' => $this->postModel->countAll('published'),
            'pages' => $this->pageModel->countAll('published'),
            'users' => $this->userModel->countUsers('active'),
            'comments' => $this->countComments('approved')
        ];
    }

    /**
     * Get recent content (posts and pages)
     * 
     * @param int $limit Number of items to return
     * @return array Recent content
     */
    public function getRecentContent($limit = 10)
    {
        $recentContent = [];

        // Get recent posts
        $recentPosts = $this->postModel->getLatest($limit);
        foreach ($recentPosts as $post) {
            $recentContent[] = [
                'id' => $post['id'],
                'title' => $post['title'],
                'type' => 'article',
                'slug' => $post['slug'],
                'date' => date('M j, Y', strtotime($post['created_at'])),
                'created_at' => $post['created_at']
            ];
        }

        // Get recent pages
        $recentPages = $this->pageModel->getAllForAdmin('all', $limit, 0);
        foreach ($recentPages as $page) {
            $recentContent[] = [
                'id' => $page['id'],
                'title' => $page['title'],
                'type' => 'page',
                'slug' => $page['slug'],
                'date' => date('M j, Y', strtotime($page['created_at'])),
                'created_at' => $page['created_at']
            ];
        }

        // Sort by creation date (newest first)
        usort($recentContent, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Return only the requested number of items
        return array_slice($recentContent, 0, $limit);
    }

    /**
     * Get recent activity
     * 
     * @param int $limit Number of activities to return
     * @return array Recent activities
     */
    public function getRecentActivity($limit = 5)
    {
        $activities = [];

        // Get recent published posts
        $recentPosts = $this->postModel->getLatest(3);
        foreach ($recentPosts as $post) {
            $activities[] = [
                'title' => 'New Article Published',
                'description' => '"' . $post['title'] . '" was published',
                'user' => $post['author'] ?? $post['username'] ?? 'Unknown',
                'time' => $post['created_at'],
                'time_ago' => $this->timeAgo($post['created_at'])
            ];
        }

        // Get recent users
        $recentUsers = $this->userModel->getAllUsers('created_at', 'DESC');
        foreach (array_slice($recentUsers, 0, 2) as $user) {
            $activities[] = [
                'title' => 'New User Registered',
                'description' => $user['display_name'] . ' joined the site',
                'user' => 'System',
                'time' => $user['created_at'],
                'time_ago' => $this->timeAgo($user['created_at'])
            ];
        }

        // Get recent comments
        $recentComments = $this->commentsModel->getAll('approved');
        foreach (array_slice($recentComments, 0, 2) as $comment) {
            $activities[] = [
                'title' => 'New Comment',
                'description' => 'Comment on "' . ($comment['post_title'] ?? 'Unknown Post') . '"',
                'user' => $comment['author_name'] ?? 'Anonymous',
                'time' => $comment['created_at'],
                'time_ago' => $this->timeAgo($comment['created_at'])
            ];
        }

        // Sort by time (newest first)
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        // Return only the requested number of activities
        return array_slice($activities, 0, $limit);
    }

    /**
     * Get system information
     * 
     * @return array System info
     */
    public function getSystemInfo()
    {
        return [
            'php_version' => phpversion(),
            'db_type' => $this->getDatabaseType(),
            'db_version' => $this->getDatabaseVersion(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'cms_version' => $this->getCMSVersion(),
            'theme' => SystemSettingsHelper::get('THEME_ACTIVE', 'default'),
            'active_plugins' => '0', // Placeholder - implement when plugin system is ready
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'upload_max_size' => ini_get('upload_max_filesize'),
            'session_timeout' => ini_get('session.gc_maxlifetime') . 's'
        ];
    }

    /**
     * Count comments by status
     * 
     * @param string $status Comment status
     * @return int Comment count
     */
    private function countComments($status = null)
    {
        try {
            $comments = $this->commentsModel->getAll($status);
            return count($comments);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get database type from configuration
     * 
     * @return string Database type
     */
    private function getDatabaseType()
    {
        try {
            // Try to detect database type by querying the connection
            $db = $this->postModel->getDb();
            $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            switch ($driver) {
                case 'sqlite':
                    return 'SQLite';
                case 'mysql':
                    return 'MySQL';
                case 'pgsql':
                    return 'PostgreSQL';
                default:
                    return ucfirst($driver);
            }
        } catch (\Exception $e) {
            // Fallback: check if SQLite constant is defined
            if (defined('DB_DRIVER') && DB_DRIVER === 'sqlite') {
                return 'SQLite';
            }
            return 'Unknown';
        }
    }

    /**
     * Get database version
     * 
     * @return string Database version
     */
    private function getDatabaseVersion()
    {
        try {
            $db = $this->postModel->getDb();
            $driver = $db->getAttribute(\PDO::ATTR_DRIVER_NAME);
            
            if ($driver === 'sqlite') {
                // SQLite version query
                $version = $db->query('SELECT sqlite_version() as version')->fetch();
                return $version ? $version['version'] : 'Unknown';
            } else if ($driver === 'mysql') {
                // MySQL version query
                $version = $db->query('SELECT VERSION() as version')->fetch();
                return $version ? $version['version'] : 'Unknown';
            } else {
                // Try generic version query
                $version = $db->getAttribute(\PDO::ATTR_SERVER_VERSION);
                return $version ? $version : 'Unknown';
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get CMS version
     * 
     * @return string CMS version
     */
    private function getCMSVersion()
    {
        // You can define this as a constant or setting
        return '1.0.0';
    }

    /**
     * Format bytes to human readable format
     * 
     * @param int $size Size in bytes
     * @param int $precision Decimal precision
     * @return string Formatted size
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Calculate time ago from timestamp
     * 
     * @param string $datetime DateTime string
     * @return string Time ago string
     */
    private function timeAgo($datetime)
    {
        $time = time() - strtotime($datetime);
        
        if ($time < 1) {
            return 'just now';
        }
        
        $condition = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60      => 'month',
            24 * 60 * 60           => 'day',
            60 * 60                => 'hour',
            60                     => 'minute',
            1                      => 'second'
        ];
        
        foreach ($condition as $secs => $str) {
            $d = $time / $secs;
            
            if ($d >= 1) {
                $t = round($d);
                return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
            }
        }
        
        return 'just now';
    }
}
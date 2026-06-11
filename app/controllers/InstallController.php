<?php

namespace App\Controllers;

use App\Helpers\RequestHelper;
use App\Helpers\CSRFHelper;
use App\Helpers\SessionHelper;

/**
 * InstallController handles the installation wizard for first-time setup
 */
class InstallController
{
    private $step;
    private $errors = [];
    private $config = [];

    public function __construct()
    {
        // Check if installation is already complete
        $installFlagFile = ROOT_PATH . '/data/.installed';
        if (file_exists($installFlagFile)) {
            $this->redirectToMainSite();
        }

        $this->step = RequestHelper::get('step', 1, 'int') ?: 1;
        $this->config = $this->loadExistingConfig();
    }

    /**
     * Main run method for installation process
     */
    public function run()
    {
        // Handle form submissions
        if (RequestHelper::isPost()) {
            $this->handlePost();
        }

        // Display current step
        $this->displayStep();
    }

    /**
     * Handle POST requests for each step
     */
    private function handlePost()
    {
        switch ($this->step) {
            case 1:
                $this->handleWelcome();
                break;
            case 2:
                $this->handleSystemCheck();
                break;
            case 3:
                $this->handleDatabaseSetup();
                break;
            case 4:
                $this->handleSiteConfiguration();
                break;
            case 5:
                $this->handleAdminAccount();
                break;
            case 6:
                $this->completeInstallation();
                break;
        }
    }

    /**
     * Display the current installation step
     */
    private function displayStep()
    {
        $viewData = [
            'step' => $this->step,
            'errors' => $this->errors,
            'config' => $this->config
        ];

        switch ($this->step) {
            case 1:
                $this->renderView('welcome', $viewData);
                break;
            case 2:
                $this->renderView('system_check', array_merge($viewData, ['checks' => $this->runSystemChecks()]));
                break;
            case 3:
                $this->renderView('database', $viewData);
                break;
            case 4:
                $this->renderView('site_config', $viewData);
                break;
            case 5:
                $this->renderView('admin_account', $viewData);
                break;
            case 6:
                $this->renderView('complete', $viewData);
                break;
        }
    }

    /**
     * Handle welcome step
     */
    private function handleWelcome()
    {
        if (RequestHelper::post('continue') !== null) {
            $this->redirectToStep(2);
        }
    }

    /**
     * Handle system check step
     */
    private function handleSystemCheck()
    {
        if (RequestHelper::post('continue') !== null) {
            $checks = $this->runSystemChecks();
            $canContinue = true;

            foreach ($checks as $check) {
                if ($check['required'] && !$check['passed']) {
                    $canContinue = false;
                    break;
                }
            }

            if ($canContinue) {
                $this->redirectToStep(3);
            } else {
                $this->errors[] = 'Please fix the required system requirements before continuing.';
            }
        }
    }

    /**
     * Handle database setup
     */
    private function handleDatabaseSetup()
    {
        if (RequestHelper::post('test_connection') !== null) {
            $this->testDatabaseConnection();
        } elseif (RequestHelper::post('continue') !== null) {
            if ($this->validateDatabaseConfig()) {
                $this->saveDatabaseConfig();
                $this->redirectToStep(4);
            }
        }
    }

    /**
     * Handle site configuration
     */
    private function handleSiteConfiguration()
    {
        if (RequestHelper::post('continue') !== null) {
            if ($this->validateSiteConfig()) {
                $this->saveSiteConfig();
                $this->redirectToStep(5);
            }
        }
    }

    /**
     * Handle admin account creation
     */
    private function handleAdminAccount()
    {
        if (RequestHelper::post('continue') !== null) {
            if ($this->validateAdminAccount()) {
                $this->saveAdminConfig();
                $this->redirectToStep(6);
            }
        }
    }

    /**
     * Complete installation
     */
    private function completeInstallation()
    {
        try {
            // Create .env file
            $this->createEnvFile();

            // Initialize database
            $this->initializeDatabase();

            // Create admin user
            $this->createAdminUser();

            // Create installation flag
            $this->createInstallationFlag();

            // Clear session data
            SessionHelper::unsetValue('install_config');

            $this->renderView('complete', ['success' => true, 'config' => $this->config]);
        } catch (Exception $e) {
            $this->errors[] = 'Installation failed: ' . $e->getMessage();
            $this->renderView('complete', ['success' => false, 'errors' => $this->errors]);
        }
    }

    /**
     * Run system requirements check
     */
    private function runSystemChecks()
    {
        $checks = [
            [
                'name' => 'PHP Version',
                'description' => 'PHP 7.4 or higher required',
                'required' => true,
                'passed' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'value' => PHP_VERSION
            ],
            [
                'name' => 'PDO Extension',
                'description' => 'PDO extension for database connectivity',
                'required' => true,
                'passed' => extension_loaded('pdo'),
                'value' => extension_loaded('pdo') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'JSON Extension',
                'description' => 'JSON extension for data handling',
                'required' => true,
                'passed' => extension_loaded('json'),
                'value' => extension_loaded('json') ? 'Available' : 'Not Available'
            ],
            [
                'name' => 'Data Directory Writable',
                'description' => 'Data directory must be writable',
                'required' => true,
                'passed' => is_writable(DATA_PATH),
                'value' => is_writable(DATA_PATH) ? 'Writable' : 'Not Writable'
            ],
            [
                'name' => 'Logs Directory Writable',
                'description' => 'Logs directory must be writable',
                'required' => true,
                'passed' => is_writable(LOGS_PATH),
                'value' => is_writable(LOGS_PATH) ? 'Writable' : 'Not Writable'
            ]
        ];

        // Add dependency checks
        $dependencyChecks = $this->checkDependencies();
        $checks = array_merge($checks, $dependencyChecks);

        // Add hosting type detection
        $hostingInfo = $this->detectHostingEnvironment();
        $checks[] = [
            'name' => 'Hosting Environment',
            'description' => 'Detected hosting type and capabilities',
            'required' => false,
            'passed' => true,
            'value' => $hostingInfo['type'] . ' (' . $hostingInfo['details'] . ')'
        ];

        return $checks;
    }

    /**
     * Check dependencies (Composer, Smarty, etc.)
     */
    private function checkDependencies()
    {
        $checks = [];

        // Check Composer
        $hasComposer = file_exists(ROOT_PATH . '/vendor/autoload.php');
        $checks[] = [
            'name' => 'Composer Dependencies',
            'description' => 'Composer autoloader and dependencies',
            'required' => false,
            'passed' => $hasComposer,
            'value' => $hasComposer ? 'Available' : 'Not Available (Manual installation will be used)',
            'info' => $hasComposer ? '' : 'swCMS can run without Composer on shared hosting'
        ];

        // Check Smarty
        $smartyAvailable = false;
        $smartyMethod = 'Not Available';

        if ($hasComposer && class_exists('Smarty\\Smarty')) {
            $smartyAvailable = true;
            $smartyMethod = 'Composer';
        } elseif (file_exists(ROOT_PATH . '/vendor/smarty/smarty/src/Smarty.php')) {
            $smartyAvailable = true;
            $smartyMethod = 'Manual Vendor';
        } elseif (file_exists(ROOT_PATH . '/App/vendor/smarty/src/Smarty.php')) {
            $smartyAvailable = true;
            $smartyMethod = 'Bundled';
        }

        $checks[] = [
            'name' => 'Smarty Template Engine',
            'description' => 'Required for template rendering',
            'required' => true,
            'passed' => $smartyAvailable,
            'value' => $smartyMethod,
            'info' => !$smartyAvailable ? 'Download from https://github.com/smarty-php/smarty/releases' : ''
        ];

        return $checks;
    }

    /**
     * Detect hosting environment type
     */
    private function detectHostingEnvironment()
    {
        $indicators = [];

        // Check for common shared hosting indicators
        if (function_exists('apache_get_modules') && in_array('mod_security', apache_get_modules())) {
            $indicators[] = 'mod_security';
        }

        $serverSoftware = RequestHelper::server('SERVER_SOFTWARE', '');
        if (!empty($serverSoftware)) {
            $serverSoftware = strtolower($serverSoftware);
            if (strpos($serverSoftware, 'cpanel') !== false) {
                $indicators[] = 'cPanel';
            }
            if (strpos($serverSoftware, 'litespeed') !== false) {
                $indicators[] = 'LiteSpeed';
            }
        }

        // Check directory permissions and restrictions
        $hasComposer = file_exists(ROOT_PATH . '/vendor/autoload.php');
        $canExecPhp = function_exists('exec');

        if (!$hasComposer && !$canExecPhp) {
            $type = 'Shared Hosting (Restricted)';
            $details = implode(', ', array_merge($indicators, ['No Composer', 'No exec()']));
        } elseif (!$hasComposer) {
            $type = 'Shared Hosting';
            $details = implode(', ', array_merge($indicators, ['No Composer']));
        } elseif (!$canExecPhp) {
            $type = 'Managed Hosting';
            $details = implode(', ', array_merge($indicators, ['Composer Available', 'No exec()']));
        } else {
            $type = 'VPS/Dedicated Server';
            $details = implode(', ', array_merge($indicators, ['Full Access']));
        }

        return [
            'type' => $type,
            'details' => $details ?: 'Standard configuration',
            'has_composer' => $hasComposer,
            'can_exec' => $canExecPhp,
            'indicators' => $indicators
        ];
    }

    /**
     * Test database connection
     */
    private function testDatabaseConnection()
    {
        try {
            $driver = RequestHelper::post('db_driver', 'mysql');

            if ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;charset=utf8mb4',
                    RequestHelper::post('db_host', ''),
                    RequestHelper::post('db_port', 3306, 'int') ?: 3306
                );
                $pdo = new \PDO($dsn, RequestHelper::post('db_user', ''), RequestHelper::post('db_pass', '', 'raw'));

                // Test if database exists, create if not (db_name already validated in validateDatabaseConfig)
                $dbName = $this->config['database']['name'] ?? '';
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}`");
                $pdo->exec("USE `{$dbName}`");
            } elseif ($driver === 'sqlite') {
                $sqlitePath = RequestHelper::post('db_sqlite_path', DATA_PATH . '/database.sqlite');
                $dsn = 'sqlite:' . $sqlitePath;
                $pdo = new \PDO($dsn);
            }

            $this->config['db_connection_success'] = true;
            $this->saveInstallConfig();
        } catch (\PDOException $e) {
            $this->errors[] = 'Database connection failed: ' . $e->getMessage();
            $this->config['db_connection_success'] = false;
        }
    }

    /**
     * Validate database configuration
     */
    private function validateDatabaseConfig()
    {
        $driver = RequestHelper::post('db_driver', '');

        if (empty($driver) || !in_array($driver, ['mysql', 'sqlite'])) {
            $this->errors[] = 'Please select a valid database driver.';
            return false;
        }

        if ($driver === 'mysql') {
            if (empty(RequestHelper::post('db_host')) || empty(RequestHelper::post('db_name')) || empty(RequestHelper::post('db_user'))) {
                $this->errors[] = 'Please fill in all required database fields.';
                return false;
            }
            if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', RequestHelper::post('db_name', '', 'raw'))) {
                $this->errors[] = 'Database name may only contain letters, numbers, and underscores (max 64 chars).';
                return false;
            }
        } elseif ($driver === 'sqlite') {
            $sqlitePath = RequestHelper::post('db_sqlite_path', DATA_PATH . '/database.sqlite');
            $sqliteDir = dirname($sqlitePath);
            if (!is_writable($sqliteDir)) {
                $this->errors[] = 'SQLite database directory is not writable.';
                return false;
            }
        }

        return true;
    }

    /**
     * Save database configuration to session
     */
    private function saveDatabaseConfig()
    {
        $this->config['database'] = [
            'driver' => RequestHelper::post('db_driver', ''),
            'host' => RequestHelper::post('db_host', ''),
            'port' => RequestHelper::post('db_port', 3306, 'int') ?: 3306,
            'name' => RequestHelper::post('db_name', ''),
            'user' => RequestHelper::post('db_user', ''),
            'pass' => RequestHelper::post('db_pass', '', 'raw'),
            'sqlite_path' => RequestHelper::post('db_sqlite_path', DATA_PATH . '/database.sqlite')
        ];
        $this->saveInstallConfig();
    }

    /**
     * Validate site configuration
     */
    private function validateSiteConfig()
    {
        if (empty(RequestHelper::post('site_name')) || empty(RequestHelper::post('site_url'))) {
            $this->errors[] = 'Please fill in all required site configuration fields.';
            return false;
        }

        return true;
    }

    /**
     * Save site configuration
     */
    private function saveSiteConfig()
    {
        $this->config['site'] = [
            'name' => RequestHelper::post('site_name', ''),
            'url' => rtrim(RequestHelper::post('site_url', ''), '/'),
            'description' => RequestHelper::post('site_description', '')
        ];
        $this->saveInstallConfig();
    }

    /**
     * Validate admin account
     */
    private function validateAdminAccount()
    {
        if (empty(RequestHelper::post('admin_username')) || empty(RequestHelper::post('admin_email')) || empty(RequestHelper::post('admin_password'))) {
            $this->errors[] = 'Please fill in all admin account fields.';
            return false;
        }

        // Validate email using RequestHelper's email filter
        $email = RequestHelper::post('admin_email', '', 'email');
        if ($email === null || empty($email)) {
            $this->errors[] = 'Please enter a valid email address.';
            return false;
        }

        // Get password with raw filter to preserve special characters
        $password = RequestHelper::post('admin_password', '', 'raw');

        // Validate password strength using User model validation
        // We need to instantiate User model to use its validation method
        try {
            require_once ROOT_PATH . '/app/models/User.php';
            $userModel = new \App\Models\User();
            $passwordValidation = $userModel->validatePasswordStrength($password);

            if (!$passwordValidation['valid']) {
                foreach ($passwordValidation['errors'] as $error) {
                    $this->errors[] = $error;
                }
                return false;
            }
        } catch (\Throwable $e) {
            // If User model is not available during install, use basic validation
            if (strlen($password) < 8) {
                $this->errors[] = 'Admin password must be at least 8 characters long.';
                return false;
            }
        }

        return true;
    }

    /**
     * Save admin account configuration
     */
    private function saveAdminConfig()
    {
        $this->config['admin'] = [
            'username' => RequestHelper::post('admin_username', ''),
            'email' => RequestHelper::post('admin_email', '', 'email'),
            'password' => password_hash(RequestHelper::post('admin_password', '', 'raw'), PASSWORD_DEFAULT)
        ];
        $this->saveInstallConfig();
    }

    /**
     * Create .env file from configuration
     */
    private function createEnvFile()
    {
        $envContent = $this->generateEnvContent();
        $envPath = ROOT_PATH . '/.env';

        $fh = fopen($envPath, 'w');
        if (!$fh) {
            throw new Exception('Could not create .env file');
        }
        chmod($envPath, 0600);
        fwrite($fh, $envContent);
        fclose($fh);
    }

    /**
     * Generate .env file content
     */
    private function generateEnvContent()
    {
        $db = $this->config['database'];
        $site = $this->config['site'];

        $content = "# swCMS Environment Configuration\n";
        $content .= "# Generated by installation wizard on " . date('Y-m-d H:i:s') . "\n\n";

        $content .= "# Database Configuration\n";
        $content .= "DB_DRIVER={$db['driver']}\n";

        if ($db['driver'] === 'mysql') {
            $content .= "DB_HOST={$db['host']}\n";
            $content .= "DB_PORT={$db['port']}\n";
            $content .= "DB_NAME={$db['name']}\n";
            $content .= "DB_USER={$db['user']}\n";
            $content .= "DB_PASS={$db['pass']}\n";
            $content .= "DB_CHARSET=utf8mb4\n";
        } else {
            $content .= "DB_SQLITE_PATH={$db['sqlite_path']}\n";
        }

        $content .= "\n# Site Configuration\n";
        $content .= "SITE_NAME=\"{$site['name']}\"\n";
        $content .= "SITE_URL={$site['url']}\n";
        $content .= "SITE_DESCRIPTION=\"{$site['description']}\"\n";

        $content .= "\n# Application Settings\n";
        $content .= "DEBUG_MODE=false\n";
        $content .= "LOG_LEVEL=warning\n";

        return $content;
    }

    /**
     * Initialize database with all tables and settings
     */
    private function initializeDatabase()
    {
        try {
            $db = $this->connectToDatabase();

            // Run all migrations to create complete database schema
            $migrationResults = $this->runAllMigrations($db);

            if (!$migrationResults['success']) {
                throw new Exception('Migration failed: ' . ($migrationResults['error'] ?? 'Unknown error'));
            }

            // Insert default system settings (after settings table is created)
            $this->insertDefaultSettings($db);

            // Store migration results for display
            $this->config['migration_results'] = $migrationResults;

            return true;
        } catch (Exception $e) {
            throw new Exception('Database initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Run all database migrations
     */
    private function runAllMigrations($db)
    {
        // Load MigrationRunner
        require_once APP_PATH . '/core/MigrationRunner.php';

        $runner = new \App\Core\MigrationRunner($db);
        return $runner->runInstallationMigrations();
    }

    /**
     * Create admin user (users table created by migrations)
     */
    private function createAdminUser()
    {
        try {
            $db = $this->connectToDatabase();

            // Insert admin user (table already created by migrations)
            $adminData = $this->config['admin'];
            $this->insertAdminUser($db, $adminData);

            // Store admin info in settings for reference
            $this->insertAdminSettings($db, $adminData);
        } catch (Exception $e) {
            throw new Exception('Admin user creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Connect to database using saved configuration
     */
    private function connectToDatabase()
    {
        $db = $this->config['database'];

        if ($db['driver'] === 'mysql') {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );
            return new PDO($dsn, $db['user'], $db['pass']);
        } else {
            $dsn = 'sqlite:' . $db['sqlite_path'];
            return new PDO($dsn);
        }
    }


    /**
     * Insert default system settings
     */
    private function insertDefaultSettings($db)
    {
        $site = $this->config['site'];

        $settings = [
            'SITE_NAME' => [$site['name'], 'Site name displayed in title and headers'],
            'SITE_URL' => [$site['url'], 'Base URL of the website'],
            'SITE_DESCRIPTION' => [$site['description'], 'Site description for SEO and about pages'],
            'THEME_ACTIVE' => ['default', 'Currently active theme'],
            'ALLOW_REGISTRATION' => ['1', 'Allow new user registration'],
            'DEBUG_MODE' => ['0', 'Enable debug mode (production should be 0)'],
            'SESSION_TIMEOUT' => ['3600', 'Session timeout in seconds'],
            'INSTALLATION_DATE' => [date('Y-m-d H:i:s'), 'Date when CMS was installed'],
            'CMS_VERSION' => ['1.0.0', 'Current CMS version'],
        ];

        if ($this->config['database']['driver'] === 'mysql') {
            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `description` = VALUES(`description`)");
        } else {
            $stmt = $db->prepare("INSERT OR REPLACE INTO settings (`key`, `value`, `description`) VALUES (?, ?, ?)");
        }

        foreach ($settings as $key => $data) {
            $stmt->execute([$key, $data[0], $data[1]]);
        }
    }

    /**
     * Insert admin user
     */
    private function insertAdminUser($db, $adminData)
    {
        if ($this->config['database']['driver'] === 'mysql') {
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active') ON DUPLICATE KEY UPDATE email = VALUES(email), password = VALUES(password)");
        } else {
            $stmt = $db->prepare("INSERT OR REPLACE INTO users (username, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')");
        }

        $stmt->execute([
            $adminData['username'],
            $adminData['email'],
            $adminData['password']
        ]);
    }

    /**
     * Store admin settings
     */
    private function insertAdminSettings($db, $adminData)
    {
        if ($this->config['database']['driver'] === 'mysql') {
            $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, `description`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        } else {
            $stmt = $db->prepare("INSERT OR REPLACE INTO settings (`key`, `value`, `description`) VALUES (?, ?, ?)");
        }

        $stmt->execute(['ADMIN_USERNAME', $adminData['username'], 'Primary admin username']);
        $stmt->execute(['ADMIN_EMAIL', $adminData['email'], 'Primary admin email address']);
    }

    /**
     * Create installation completion flag
     */
    private function createInstallationFlag()
    {
        $flagPath = ROOT_PATH . '/data/.installed';
        $flagContent = json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'installer_ip' => RequestHelper::server('REMOTE_ADDR', 'unknown')
        ]);

        if (!file_put_contents($flagPath, $flagContent)) {
            throw new Exception('Could not create installation flag');
        }
    }

    /**
     * Load existing configuration from session
     */
    private function loadExistingConfig()
    {
        return SessionHelper::getValue('install_config', []);
    }

    /**
     * Save configuration to session
     */
    private function saveInstallConfig()
    {
        SessionHelper::setValue('install_config', $this->config);
    }

    /**
     * Redirect to installation step
     */
    private function redirectToStep($step)
    {
        header("Location: ?step=$step");
        exit;
    }

    /**
     * Redirect to main site (installation already complete)
     */
    private function redirectToMainSite()
    {
        // Try to determine the base URL
        $https = RequestHelper::server('HTTPS', '');
        $protocol = (!empty($https) && $https === 'on') ? 'https' : 'http';
        $host = RequestHelper::server('HTTP_HOST', 'localhost');
        $baseUrl = $protocol . '://' . $host;

        // Remove any installer path from current URL
        $currentPath = RequestHelper::server('REQUEST_URI', '/');
        $basePath = dirname(RequestHelper::server('SCRIPT_NAME', '/'));
        if ($basePath !== '/') {
            $baseUrl .= $basePath;
        }

        // Show a simple message instead of redirect to avoid redirect loops
        $this->showAlreadyInstalledMessage($baseUrl);
        exit;
    }

    /**
     * Show message that installation is already complete
     */
    private function showAlreadyInstalledMessage($siteUrl)
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Installation Already Complete - swCMS</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
                    padding: 40px;
                    text-align: center;
                    max-width: 500px;
                    width: 100%;
                }
                .icon {
                    font-size: 48px;
                    color: #10b981;
                    margin-bottom: 20px;
                }
                h1 {
                    color: #1f2937;
                    margin-bottom: 15px;
                    font-size: 28px;
                }
                p {
                    color: #6b7280;
                    line-height: 1.6;
                    margin-bottom: 30px;
                }
                .btn {
                    display: inline-block;
                    background: #4f46e5;
                    color: white;
                    text-decoration: none;
                    padding: 14px 28px;
                    border-radius: 8px;
                    font-weight: 600;
                    margin: 0 10px;
                    transition: background 0.2s ease;
                }
                .btn:hover {
                    background: #4338ca;
                }
                .btn-secondary {
                    background: #6b7280;
                }
                .btn-secondary:hover {
                    background: #4b5563;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="icon">✅</div>
                <h1>Installation Already Complete</h1>
                <p>
                    swCMS has already been installed and configured on this server. 
                    The installation wizard cannot run again for security reasons.
                </p>
                <p>
                    <a href="<?php echo htmlspecialchars($siteUrl); ?>" class="btn">Visit Your Site</a>
                    <a href="<?php echo htmlspecialchars($siteUrl . '/admin'); ?>" class="btn btn-secondary">Admin Panel</a>
                </p>
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #e5e7eb;">
                <p style="font-size: 14px; color: #9ca3af;">
                    If you need to re-run the installation, please remove the installation flag:
                    <br><code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                        php scripts/remove_install_flag.php
                    </code>
                </p>
            </div>
        </body>
        </html>
        <?php
    }

    /**
     * Render installation view
     */
    private function renderView($view, $data = [])
    {
        extract($data);

        // Include basic HTML template with the specific step content
        include APP_PATH . '/views/install/layout.php';
    }
}
<?php
/**
 * Prepare swCMS for Shared Hosting Deployment
 * This script creates a deployment package without Composer dependencies
 */

echo "=== swCMS Shared Hosting Preparation ===\n\n";

// Define paths
$rootPath = dirname(__DIR__);
$deployPath = $rootPath . '/deploy_shared_hosting';

// Create deployment directory
if (!is_dir($deployPath)) {
    mkdir($deployPath, 0755, true);
    echo "✓ Created deployment directory: $deployPath\n";
}

// Copy essential files and directories
$filesToCopy = [
    'app' => 'Core application files',
    'public' => 'Web accessible files',
    'plugins' => 'Plugin directory',
    'data' => 'Data directory (empty)',
    'logs' => 'Logs directory (empty)',
    'scripts' => 'Utility scripts',
    'database' => 'Database migrations and schema',
    '.env.example' => 'Environment configuration template',
    '.gitignore' => 'Git ignore rules',
    'README.md' => 'Documentation',
    'LICENSE' => 'License file'
];

foreach ($filesToCopy as $source => $description) {
    $sourcePath = $rootPath . '/' . $source;
    $destPath = $deployPath . '/' . $source;
    
    if (is_dir($sourcePath)) {
        copyDirectory($sourcePath, $destPath);
        echo "✓ Copied directory: $source ($description)\n";
    } elseif (is_file($sourcePath)) {
        copy($sourcePath, $destPath);
        echo "✓ Copied file: $source ($description)\n";
    } else {
        echo "⚠ Source not found: $source\n";
    }
}

// Create empty vendor directory structure for manual dependencies
$vendorPath = $deployPath . '/App/vendor';
mkdir($vendorPath, 0755, true);
mkdir($vendorPath . '/smarty', 0755, true);

// Create installation instructions
$instructionsFile = $deployPath . '/SHARED_HOSTING_INSTALL.md';
$instructions = generateInstallInstructions();
file_put_contents($instructionsFile, $instructions);
echo "✓ Created installation instructions: SHARED_HOSTING_INSTALL.md\n";

// Create download script for dependencies
$downloadScript = $deployPath . '/download_dependencies.php';
$downloadScriptContent = generateDownloadScript();
file_put_contents($downloadScript, $downloadScriptContent);
echo "✓ Created dependency download script: download_dependencies.php\n";

// Create .htaccess for security if needed
$htaccessContent = generateHtaccess();
file_put_contents($deployPath . '/public/.htaccess', $htaccessContent);
echo "✓ Created .htaccess file for web security\n";

echo "\n=== Deployment Package Ready! ===\n";
echo "Location: $deployPath\n";
echo "Size: " . formatBytes(getDirectorySize($deployPath)) . "\n";
echo "\nNext steps:\n";
echo "1. Upload the contents of '$deployPath' to your shared hosting\n";
echo "2. Point your domain to the 'public' directory\n";
echo "3. Run: php download_dependencies.php (if needed)\n";
echo "4. Set permissions: chmod 755 data logs public/uploads\n";
echo "5. Visit your domain to start the installation wizard\n";

/**
 * Copy directory recursively
 */
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    while (false !== ($file = readdir($dir))) {
        if ($file != '.' && $file != '..') {
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                // Skip certain directories
                if (in_array($file, ['vendor', 'node_modules', '.git', 'tests', 'coverage'])) {
                    continue;
                }
                copyDirectory($srcFile, $dstFile);
            } else {
                // Skip certain files
                if (in_array($file, ['composer.lock', 'package-lock.json'])) {
                    continue;
                }
                copy($srcFile, $dstFile);
            }
        }
    }
    closedir($dir);
}

/**
 * Generate installation instructions for shared hosting
 */
function generateInstallInstructions() {
    return <<<'EOD'
# swCMS Shared Hosting Installation Guide

This package contains swCMS prepared for shared hosting environments without Composer.

## 📋 Requirements

- PHP 7.4 or higher
- PDO extension (MySQL/SQLite)
- JSON extension
- Write permissions on directories

## 🚀 Installation Steps

### 1. Upload Files
Upload all files to your shared hosting account:
- Upload via FTP, File Manager, or hosting control panel
- Point your domain's document root to the `public` directory

### 2. Set Permissions
Set the following directory permissions:
```bash
chmod 755 data
chmod 755 logs  
chmod 755 public/uploads
```

### 3. Download Dependencies (if needed)
If Smarty template engine is not available, run:
```bash
php download_dependencies.php
```

Or manually download and extract:
- Download Smarty from: https://github.com/smarty-php/smarty/releases
- Extract to: `App/vendor/smarty/`

### 4. Run Installation Wizard
- Visit your domain in a browser
- The installation wizard will run automatically
- Complete all 6 steps of the setup process

### 5. Post-Installation
- Admin panel: `https://yourdomain.com/admin`
- Remove or restrict access to `download_dependencies.php` for security

## 🔧 Troubleshooting

### Installation wizard doesn't appear
```bash
# Check if installation flag exists
ls -la data/.installed

# Remove flag to re-run installer  
php scripts/remove_install_flag.php
```

### Dependencies not found
- Ensure Smarty is extracted to `App/vendor/smarty/`
- Check that `App/vendor/smarty/src/Smarty.php` exists
- Verify directory permissions

### Database connection issues
- Verify database credentials during installation
- Ensure database exists or user has CREATE privileges
- For SQLite, ensure data directory is writable

### Permission errors
```bash
# Set correct permissions
chmod -R 755 data logs public/uploads

# If using Apache, ensure .htaccess is allowed
# Check hosting control panel for permission settings
```

## 📞 Support

- Documentation: https://github.com/jeanmarieclement/swCMS/wiki
- Issues: https://github.com/jeanmarieclement/swCMS/issues
- Run diagnostic: `php scripts/test_install.php`

---
Generated by swCMS shared hosting preparation script
EOD;
}

/**
 * Generate download script for dependencies
 */
function generateDownloadScript() {
    return <<<'EOD'
<?php
/**
 * Download Dependencies for swCMS Shared Hosting
 * Run this script if dependencies are not available
 */

echo "=== swCMS Dependency Downloader ===\n\n";

$dependencies = [
    'smarty' => [
        'name' => 'Smarty Template Engine',
        'url' => 'https://github.com/smarty-php/smarty/releases/download/v5.5.0/smarty-5.5.0.tar.gz',
        'extract_to' => 'App/vendor/smarty',
        'required_file' => 'src/Smarty.php'
    ]
];

foreach ($dependencies as $key => $dep) {
    echo "Downloading {$dep['name']}...\n";
    
    $targetDir = __DIR__ . '/' . $dep['extract_to'];
    $tempFile = sys_get_temp_dir() . '/' . $key . '.tar.gz';
    
    // Create target directory
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Check if already exists
    if (file_exists($targetDir . '/' . $dep['required_file'])) {
        echo "  ✓ Already exists: {$dep['name']}\n";
        continue;
    }
    
    // Download
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dep['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $data = file_get_contents($dep['url']);
    }
    
    if ($data === false) {
        echo "  ✗ Failed to download {$dep['name']}\n";
        echo "  Manual download required from: {$dep['url']}\n";
        continue;
    }
    
    // Save and extract
    file_put_contents($tempFile, $data);
    
    // Extract using PHP (basic tar.gz support)
    $phar = new PharData($tempFile);
    $phar->extractTo($targetDir, null, true);
    
    // Clean up
    unlink($tempFile);
    
    // Verify
    if (file_exists($targetDir . '/' . $dep['required_file'])) {
        echo "  ✓ Successfully installed: {$dep['name']}\n";
    } else {
        echo "  ⚠ Download completed but file structure may be different\n";
        echo "  Please verify: {$targetDir}/{$dep['required_file']}\n";
    }
}

echo "\n=== Download Complete ===\n";
echo "You can now run the installation wizard by visiting your domain.\n";
EOD;
}

/**
 * Generate .htaccess for security
 */
function generateHtaccess() {
    return <<<'EOD'
# swCMS Security Rules
RewriteEngine On

# Redirect all requests to index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Deny access to sensitive files
<FilesMatch "^(\.env|\.htaccess|composer\.(json|lock)|package(-lock)?\.json)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Deny access to app directory from web
RewriteRule ^App/ - [F,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
EOD;
}

/**
 * Get directory size recursively
 */
function getDirectorySize($dir) {
    $size = 0;
    if (is_dir($dir)) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    }
    return $size;
}

/**
 * Format bytes to human readable
 */
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
EOD;
# swCMS - Modular Content Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

A modern, modular Content Management System built with PHP and MVC architecture. Inspired by WordPress, swCMS offers a clean, extensible platform for managing content, users, and plugins with both frontend and administrative interfaces.

## ✨ Features

- **MVC Architecture** - Clean separation of concerns with modular design
- **User Management** - Role-based permissions (Admin, Editor, Author, Reader)
- **Content Management** - Articles, pages, categories, and tags
- **Plugin System** - Extensible architecture with hook system
- **Theme Support** - Multiple theme support with automatic fallback
- **Media Library** - File upload and management system
- **Comment System** - Built-in commenting with moderation
- **Admin Dashboard** - Comprehensive backend interface
- **Database Flexibility** - MySQL and SQLite support
- **Auto-Installation** - One-click setup wizard with system checks
- **Docker Ready** - Complete development environment

## 🚀 Quick Start

### Automatic Installation (Recommended)

swCMS includes a built-in **Installation Wizard** that automatically configures your CMS on first access.

1. **Clone the repository**
   ```bash
   git clone https://github.com/jeanmarieclement/swCMS.git
   cd swCMS
   ```

2. **Set basic permissions**
   ```bash
   chmod 755 data
   chmod 755 logs
   chmod 755 public/uploads
   ```

3. **Access your site**
   ```
   http://your-domain.com
   ```

4. **Complete the Installation Wizard**
   - ✅ **Step 1**: Welcome screen
   - ✅ **Step 2**: System requirements check
   - ✅ **Step 3**: Database configuration (MySQL/SQLite)
   - ✅ **Step 4**: Site settings (name, URL, description)
   - ✅ **Step 5**: Admin account creation
   - ✅ **Step 6**: Installation complete!

5. **Start using your CMS**
   - Frontend: `http://your-domain.com`
   - Admin Panel: `http://your-domain.com/admin`

### Using Docker (Development)

1. **Start the environment**
   ```bash
   docker-compose up -d
   ```

2. **Access your CMS**
   - Frontend: http://localhost
   - Admin Panel: http://localhost/admin
   - phpMyAdmin: http://localhost:8081

3. **Complete Installation Wizard**
   - Use database settings: host=`db`, user=`swcms_user`, password=`swcms_password`, database=`swcms`

### Manual Installation

1. **Requirements**
   - PHP 8.0 or higher
   - MySQL 5.7+ or SQLite 3
   - Apache/Nginx web server
   - PDO extension (MySQL/SQLite)
   - JSON extension

2. **Install Dependencies (Optional)**
   ```bash
   composer install
   ```

3. **Set Permissions**
   ```bash
   chmod 755 data logs public/uploads
   ```

4. **Access Installation Wizard**
   Visit your domain - the installer runs automatically on first access

5. **Manual Configuration (Advanced)**
   ```bash
   # Copy environment template
   cp .env.example .env
   
   # Edit configuration
   nano .env
   
   # Skip installer (creates installation flag)
   php scripts/create_install_flag.php
   ```

## 📁 Project Structure

```
swCMS/
├── 📂 App/                    # Core application
│   ├── 📂 controllers/        # MVC Controllers
│   │   ├── 📂 admin/          # Admin panel controllers
│   │   └── 📂 frontend/       # Public site controllers
│   ├── 📂 models/             # Database models
│   ├── 📂 views/              # Smarty templates
│   │   ├── 📂 admin/          # Admin templates
│   │   └── 📂 frontend/       # Public templates
│   ├── 📂 core/               # Core framework classes
│   ├── 📂 helpers/            # Utility functions
│   ├── 📂 services/           # Business logic
│   └── 📂 middlewares/        # Request middlewares
├── 📂 public/                 # Web root
│   ├── 📂 themes/             # Theme files
│   ├── 📂 uploads/            # User uploads
│   └── index.php              # Entry point
├── 📂 plugins/                # CMS plugins
├── 📂 database/               # Migrations & schema
├── 📂 tests/                  # Test suite
└── 📂 docker/                 # Docker configuration
```

## 🛠️ Development

### Running Tests
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Unit

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage
```

### Database Migrations
```bash
# Apply all pending migrations
php database/migrate.php up

# Revert last migration
php database/migrate.php down

# Apply specific migration
php database/migrate.php up 2025_06_19_000010_create_users_table.php
```

### Plugin Development

swCMS features a powerful plugin system with hooks and filters:

```php
// Register a hook
HookHelper::add_action('init', 'my_plugin_init');

// Add a filter
HookHelper::add_filter('content', 'my_content_filter');

// Plugin structure
plugins/my-plugin/
├── my-plugin.php          # Main plugin file
├── controllers/           # Plugin controllers
├── models/                # Plugin models  
├── views/                 # Plugin templates
└── assets/                # Plugin assets
```

## 🎨 Theming

Create custom themes in `public/themes/`:

```
public/themes/my-theme/
├── index.tpl              # Homepage template
├── article.tpl            # Article template
├── page.tpl               # Page template
├── css/                   # Theme styles
├── js/                    # Theme scripts
└── README.md              # Theme documentation
```

Set your theme in Admin → Settings → Appearance.

## 🔧 Configuration

### Automatic Configuration

swCMS automatically creates configuration during installation:

- **Environment File** - Creates `.env` with database and site settings
- **Database Settings** - Stores configuration in `settings` table  
- **Admin Account** - Creates first admin user automatically
- **System Settings** - Configures themes, debugging, and security

### Environment Variables

The installation wizard creates `.env` automatically, but you can customize:

```env
# Database Configuration
DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_NAME=swcms
DB_USER=your_user
DB_PASS=your_password
DB_CHARSET=utf8mb4

# Site Configuration  
SITE_NAME="Your CMS Site"
SITE_URL=https://yoursite.com
SITE_DESCRIPTION="Your site description"

# Application Settings
DEBUG_MODE=false
LOG_LEVEL=warning
SESSION_LIFETIME=7200
```

### Manual Configuration Tools

```bash
# Test installation system
php scripts/test_install.php

# Re-run installer (removes installation flag)
php scripts/remove_install_flag.php

# Skip installer (creates installation flag)
php scripts/create_install_flag.php
```

### Security Settings

Important security considerations:

- Installation wizard sets secure defaults automatically
- Set `DEBUG_MODE = false` in production (done automatically)
- Use strong database passwords during installation
- Enable HTTPS enforcement in web server configuration
- Review file permissions after installation

## 🧪 Testing

swCMS includes a comprehensive test suite:

- **Unit Tests** - Test individual components
- **Integration Tests** - Test component interactions  
- **Functional Tests** - Test complete workflows

```bash
# Install development dependencies
composer install --dev

# Run tests with coverage
vendor/bin/phpunit --coverage-html coverage
```

## 🔌 Plugin System

The plugin architecture supports:

- **Hooks & Filters** - Modify behavior without core changes
- **Custom Post Types** - Extend content types
- **Admin Pages** - Add custom admin interfaces
- **Database Tables** - Plugin-specific data storage
- **REST API** - Custom endpoints

Example plugin structure in `plugins/example-plugin/`:

```php
<?php
/**
 * Plugin Name: Example Plugin
 * Description: Example plugin for swCMS
 * Version: 1.0.0
 */

// Activation hook
HookHelper::register_activation_hook(__FILE__, 'example_plugin_activate');

// Initialize plugin
HookHelper::add_action('init', 'example_plugin_init');

function example_plugin_init() {
    // Plugin initialization code
}
```

## 🌐 Multilingual Support

swCMS supports multiple languages through:

- Template-based translations
- Plugin translation hooks
- Admin interface localization
- Content language management

## 📊 Performance

Optimization features:

- **Template Caching** - Smarty template compilation
- **Database Query Optimization** - Efficient queries with prepared statements
- **Asset Minification** - CSS/JS optimization (theme-dependent)
- **Image Optimization** - Media processing hooks

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes and add tests
4. Ensure tests pass: `vendor/bin/phpunit`
5. Commit changes: `git commit -am 'Add new feature'`
6. Push to branch: `git push origin feature/my-feature`
7. Submit a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🛠️ Installation Troubleshooting

### Common Installation Issues

**Installation wizard doesn't appear**
```bash
# Check if installation flag exists
ls -la data/.installed

# Remove flag to re-run installer
php scripts/remove_install_flag.php
```

**Database connection fails**
- Verify database credentials
- Ensure database server is running
- Check database permissions
- For MySQL: ensure database exists or user has CREATE privileges

**Permission errors**
```bash
# Set correct permissions
chmod -R 755 data logs public/uploads
chown -R www-data:www-data data logs public/uploads
```

**System requirements not met**
- Upgrade PHP to 8.0+ 
- Install required extensions: `php-pdo`, `php-json`
- Check with: `php scripts/test_install.php`

**Blank page after installation**
- Check error logs in `logs/` directory
- Enable debug mode temporarily in `.env`: `DEBUG_MODE=true`
- Verify web server document root points to `public/` directory

## ⚠️ Known Limitations

- **Password reset**: Email delivery is not implemented out of the box. You must configure a mailer (e.g. PHPMailer with SMTP) to complete this feature.
- **Content Security Policy**: `unsafe-inline` and `unsafe-eval` are required for Smarty template engine and TinyMCE editor compatibility. Hardening the CSP requires replacing both.
- **session.cookie_secure**: Set to `0` by default for compatibility with non-HTTPS local setups. Set to `1` in `app/Config/config.php` when deploying on HTTPS.

## 🆘 Support

- **Documentation**: [Wiki](../../wiki)  
- **Issues**: [GitHub Issues](../../issues)
- **Discussions**: [GitHub Discussions](../../discussions)
- **Installation Help**: Run `php scripts/test_install.php` for diagnostics

## 🔗 Links

- [Demo Site](https://demo.swcms.org) (coming soon)
- [Documentation](https://docs.swcms.org) (coming soon)
- [Plugin Directory](https://plugins.swcms.org) (coming soon)

---


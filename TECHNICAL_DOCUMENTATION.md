# swCMS - Technical Documentation

## Table of Contents
- [Overview](#overview)
- [Architecture](#architecture)
- [Core Components](#core-components)
- [Database Structure](#database-structure)
- [Plugin System](#plugin-system)
- [Theming System](#theming-system)
- [Security Features](#security-features)
- [Installation System](#installation-system)
- [Testing Framework](#testing-framework)
- [Development Guidelines](#development-guidelines)

## Overview

swCMS is a modern, modular Content Management System built with PHP and MVC architecture. Inspired by WordPress, it offers a clean, extensible platform for managing content, users, and plugins with both frontend and administrative interfaces.

### Key Features
- **MVC Architecture**: Clean separation of concerns with modular design
- **Role-based Access Control**: Granular permission system
- **Plugin System**: Extensible hook-based architecture
- **Theme Support**: Multiple theme support with Smarty templates
- **Auto-Installation**: One-click setup wizard
- **Security First**: Built-in security measures and rate limiting
- **Database Flexibility**: MySQL and SQLite support

### Technology Stack
- **Backend**: PHP 7.4+
- **Template Engine**: Smarty 5.5
- **Database**: MySQL 5.7+ / SQLite 3
- **Frontend**: HTML5, CSS3, JavaScript
- **Build Tools**: Composer for dependency management

## Architecture

### Project Structure
```
swCMS/
├── App/                          # Core application
│   ├── Config/                   # Configuration files
│   │   ├── config.php           # Main configuration
│   │   └── install_config.php   # Installation configuration
│   ├── Controllers/              # MVC Controllers
│   │   ├── admin/               # Admin panel controllers
│   │   └── frontend/            # Public site controllers
│   ├── Core/                    # Core framework classes
│   │   ├── Autoloader.php       # Custom class autoloader
│   │   ├── Controller.php       # Base controller class
│   │   ├── Database/            # Database abstraction
│   │   ├── Model.php            # Base model class
│   │   ├── Router.php           # URL routing system
│   │   └── View.php             # Smarty template engine wrapper
│   ├── Helpers/                 # Utility functions
│   ├── Models/                  # Database models
│   ├── Services/                # Business logic layer
│   ├── Views/                   # Smarty templates
│   │   ├── admin/               # Admin interface templates
│   │   ├── frontend/            # Public site templates
│   │   ├── compiled/            # Smarty compiled templates
│   │   └── plugins/             # Smarty custom plugins
│   └── Middlewares/             # Request middlewares
├── public/                       # Web root
│   ├── themes/                  # Theme files
│   ├── uploads/                 # User uploads
│   └── index.php                # Application entry point
├── plugins/                      # CMS plugins
├── database/                     # Migrations & schema
├── tests/                        # Test suite
└── vendor/                       # Composer dependencies
```

## Core Components

### 1. Application Bootstrap (public/index.php)

The main entry point handles:
- **Installation Check**: Redirects to installer if not installed
- **Configuration Loading**: Loads environment and system settings
- **Error Handling**: Configures error reporting based on debug mode
- **Session Management**: Initializes secure sessions with CSRF protection
- **Plugin Loading**: Loads and initializes active plugins
- **Request Routing**: Dispatches requests to appropriate controllers

```php
// Installation check
$installFlagFile = ROOT_PATH . '/data/.installed';
if (!file_exists($installFlagFile)) {
    // Run installation wizard
    $installer = new InstallController();
    $installer->run();
    exit;
}

// Initialize Hook System and load plugins
$hookSystem = HookSystem::getInstance();
$pluginService = new PluginService();
$pluginService->loadActivePlugins();

// Route the request
$router = new Router();
$router->dispatch();
```

### 2. Autoloader System (App/Core/Autoloader.php)

Custom PSR-4 compliant autoloader that handles:
- **Namespace Resolution**: Converts namespaces to file paths
- **Class Type Detection**: Identifies controllers, models, helpers, services
- **Legacy Support**: Maintains backward compatibility
- **Performance**: Efficient class loading with caching

**Loading Strategy**:
1. Core classes (Router, Controller, Model, View, Database)
2. Models (with and without "Model" suffix)
3. Controllers (admin, frontend, base)
4. Helpers and Services
5. PSR-4 namespace resolution

### 3. Router System (App/Core/Router.php)

**URL Routing Features**:
- **Pattern Matching**: Regex-based route matching
- **Parameter Extraction**: Named and numeric parameter capture
- **Plugin Routes**: Dynamic plugin route loading
- **Controller Resolution**: Automatic controller and action detection
- **Error Handling**: 404/500 error page handling

**Route Types**:
```php
// Admin routes with parameters
$this->addRoute('admin/users/edit/([0-9]+)', ['controller' => 'User', 'action' => 'edit']);

// Frontend content routes with slugs
$this->addRoute('article/([a-zA-Z0-9\-]+)', ['controller' => 'Article', 'action' => 'show']);

// Authentication routes
$this->addRoute('auth/login', ['controller' => 'Auth', 'action' => 'login']);
```

**Controller Resolution Logic**:
1. Check plugin controllers first
2. Admin routes: admin directory, then frontend fallback
3. Frontend routes: frontend directory, then admin fallback
4. Convert kebab-case to StudlyCaps for class names

### 4. MVC Architecture

#### Base Controller (App/Core/Controller.php)

**Features**:
- **Template Rendering**: Integrated with View system
- **Flash Messages**: Session-based user feedback
- **Parameter Handling**: Sanitized request parameter access
- **Authentication Data**: Automatic user data injection
- **Hook Integration**: Before/after action filters

**Core Methods**:
```php
protected function render($template, $data = []); // Render templates with data
protected function redirect($url);                // HTTP redirects
protected function setFlashMessage($type, $message); // User feedback
protected function getParam($key, $default, $method = 'GET'); // Safe parameter access
```

#### Base Model (App/Core/Model.php)

**Database Operations**:
- **CRUD Operations**: Create, Read, Update, Delete with validation
- **Query Builder**: Fluent query interface with prepared statements
- **Hook System**: Before/after save hooks for plugins
- **Bulk Operations**: Efficient batch processing
- **Type Safety**: Automatic parameter type binding

**Hook Integration**:
```php
// Model hooks for plugins
$this->fireModelHook('before_save', $data, $id);
$this->fireModelHook('after_insert', $insertedRecord, $id);

// Filter hooks for data modification
$data = $this->hookSystem->applyFilters('model_insert_data', $data, $this->table);
```

#### View System (App/Core/View.php)

**Template Engine Features**:
- **Smarty Integration**: Full Smarty 5.5 template engine
- **Theme Support**: Multi-theme architecture with fallbacks
- **Plugin Templates**: Plugin-specific template directories
- **Security**: Path traversal protection and input validation
- **Caching**: Environment-based template caching
- **Hook Integration**: Template modification hooks

**Template Resolution**:
1. **Admin Templates**: `App/Views/admin/`
2. **Frontend Templates**: `public/themes/{active_theme}/templates/`
3. **Plugin Templates**: `plugins/{plugin_name}/views/`
4. **Fallback**: Default theme templates

## Database Structure

### Migration System

**Database Migration Features**:
- **Version Control**: Timestamped migration files
- **Up/Down Migrations**: Forward and rollback support
- **Cross-Database**: MySQL and SQLite compatibility
- **Dependency Tracking**: Migration state management

**Migration Structure**:
```php
<?php
// database/migrations/2025_06_19_000010_create_users_table.php
class CreateUsersTable {
    public function up($db) {
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','editor','author','subscriber') DEFAULT 'subscriber',
            status ENUM('active','inactive','banned') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($sql);
    }

    public function down($db) {
        $db->exec("DROP TABLE users");
    }
}
```

### Core Database Tables

#### Users Table
- **Authentication**: Secure password hashing with bcrypt
- **Role-based Access**: Admin, Editor, Author, Subscriber roles
- **Security Features**: Rate limiting, login attempt tracking
- **Audit Trail**: Creation, update, and last login timestamps

#### Posts Table
- **Content Management**: Title, content, excerpt, featured image
- **Publishing**: Draft, published, scheduled, trash status
- **SEO**: Slug generation and management
- **Relationships**: Author, categories, tags associations

#### Categories & Tags
- **Hierarchical Categories**: Parent-child relationships
- **Tag System**: Many-to-many relationships with posts
- **SEO-Friendly**: Automatic slug generation

#### Media Library
- **File Management**: Upload, storage, and metadata
- **Image Processing**: Thumbnail generation and optimization
- **Security**: File type validation and sanitization

### Example Models

#### User Model (App/Models/User.php)
```php
class User extends Model {
    protected $table = 'users';

    public function authenticate($email, $password) {
        // Rate limiting check
        if ($this->isRateLimited($email)) {
            return false;
        }

        $user = $this->getUserByEmail($email);

        // Timing attack prevention
        $passwordValid = password_verify($password, $user['password'] ?? '');

        if (!$user || !$passwordValid) {
            $this->recordFailedAttempt($email);
            return false;
        }

        $this->clearFailedAttempts($email);
        $this->updateLastLogin($user['id']);

        unset($user['password']); // Security: never return password
        return $user;
    }
}
```

#### Post Model (App/Models/Post.php)
```php
class Post extends Model {
    protected $table = 'posts';

    public function create($data) {
        // Auto-generate unique slug
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }

        // Set publication timestamp
        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        return $this->insert($data);
    }
}
```

## Plugin System

### Hook System Architecture

The plugin system is built on a WordPress-inspired hook architecture that allows plugins to modify behavior without changing core code.

#### Hook System (App/Core/HookSystem.php)

**Hook Types**:
- **Actions**: Execute code at specific points
- **Filters**: Modify data as it passes through the system

**Usage Examples**:
```php
// Register an action hook
HookHelper::add_action('init', 'my_plugin_init');

// Register a filter hook
HookHelper::add_filter('content', 'my_content_filter');

// Fire an action
$hookSystem->doAction('template_render', $template, $data);

// Apply a filter
$content = $hookSystem->applyFilters('the_content', $content);
```

#### Plugin Structure

**Standard Plugin Directory**:
```
plugins/my-plugin/
├── my-plugin.php              # Main plugin file
├── controllers/               # Plugin controllers
├── models/                    # Plugin models
├── views/                     # Plugin templates
├── assets/                    # CSS, JS, images
└── README.md                  # Plugin documentation
```

**Plugin Main File**:
```php
<?php
/**
 * Plugin Name: My Plugin
 * Description: Example plugin for swCMS
 * Version: 1.0.0
 * Author: Plugin Author
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation hook
HookHelper::register_activation_hook(__FILE__, 'my_plugin_activate');

// Initialize plugin
HookHelper::add_action('init', 'my_plugin_init');

function my_plugin_init() {
    // Plugin initialization code
    HookHelper::add_filter('the_content', 'my_plugin_filter_content');
    HookHelper::add_action('cms_head', 'my_plugin_add_head_content');
}

function my_plugin_filter_content($content) {
    return $content . '<p>Added by My Plugin</p>';
}
```

### Plugin Services

#### PluginService (App/Services/PluginService.php)
- **Plugin Discovery**: Scans plugin directory
- **Activation/Deactivation**: Manages plugin state
- **Dependency Checking**: Validates plugin requirements
- **Route Generation**: Creates plugin-specific routes

#### AdminMenuService (App/Services/AdminMenuService.php)
- **Dynamic Menus**: Role-based admin menu generation
- **Plugin Integration**: Allows plugins to add menu items
- **Permission Checking**: Validates user access to menu items

## Theming System

### Theme Architecture

**Theme Structure**:
```
public/themes/my-theme/
├── templates/                 # Smarty template files
│   ├── index.tpl             # Homepage template
│   ├── article.tpl           # Article template
│   ├── page.tpl              # Page template
│   └── layout.tpl            # Base layout
├── css/                       # Stylesheets
├── js/                        # JavaScript files
├── images/                    # Theme images
└── theme.json                 # Theme metadata
```

### Template System

**Template Hierarchy**:
1. Active theme templates
2. Default theme fallback
3. Core admin templates

**Template Variables**:
```smarty
{* Available in all templates *}
{$site_name}
{$site_url}
{$admin_url}
{$is_logged_in}
{$user}
{$settings}

{* Content-specific variables *}
{$post}
{$page}
{$categories}
{$tags}
```

**Smarty Custom Plugins**:
```php
// App/Views/plugins/function.hook_action.php
function smarty_function_hook_action($params, $smarty) {
    $hook = $params['hook'] ?? '';
    $hookSystem = HookSystem::getInstance();
    ob_start();
    $hookSystem->doAction($hook, $params);
    return ob_get_clean();
}
```

## Security Features

### Authentication & Authorization

**Multi-layer Security**:
1. **Password Security**: bcrypt hashing with cost factor 12
2. **Rate Limiting**: Failed login attempt tracking
3. **Session Security**: Secure session configuration with CSRF tokens
4. **Role-based Access**: Granular permission system

**CSRF Protection**:
```php
// Session initialization in index.php
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Template usage
<input type="hidden" name="csrf_token" value="{$smarty.session.csrf_token}">
```

**Authentication Middleware**:
```php
class AuthMiddleware {
    public static function requireAdmin() {
        if (!SessionHelper::hasValue('user_id') ||
            SessionHelper::getValue('user_role') !== 'admin') {
            RedirectHelper::redirect('/auth/login');
        }
    }
}
```

### Input Validation & Sanitization

**Parameter Sanitization**:
```php
protected function getParam($key, $default = null, $method = 'GET') {
    $value = ($method === 'POST') ? $_POST[$key] ?? $default : $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $value;
}
```

**Template Security**:
- Path traversal prevention
- Template name validation
- Directory restriction enforcement

### Database Security

**Prepared Statements**:
```php
public function query($sql, $params = []) {
    $stmt = $this->db->prepare($sql);
    foreach ($params as $key => $value) {
        $this->bindValueByType($stmt, $key, $value);
    }
    $stmt->execute();
    return $stmt;
}
```

## Installation System

### Auto-Installation Wizard

**Installation Flow**:
1. **Welcome Screen**: Introduction and requirements
2. **System Check**: PHP version, extensions, permissions
3. **Database Configuration**: MySQL/SQLite setup
4. **Site Settings**: Name, URL, description
5. **Admin Account**: First administrator creation
6. **Completion**: Installation flag creation

**InstallController (App/Controllers/InstallController.php)**:
```php
public function run() {
    $step = $_GET['step'] ?? 'welcome';

    switch ($step) {
        case 'welcome':
            $this->showWelcome();
            break;
        case 'system_check':
            $this->checkSystem();
            break;
        case 'database':
            $this->configureDatabase();
            break;
        // ... additional steps
    }
}
```

### Configuration Management

**Environment Configuration**:
```php
// .env file creation during installation
DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=swcms
DB_USER=username
DB_PASS=password

SITE_NAME="My CMS Site"
SITE_URL=https://example.com
DEBUG_MODE=false
```

**Settings Storage**:
- Environment variables in `.env` file
- Dynamic settings in database `settings` table
- Installation flag in `data/.installed`

## Testing Framework

### Test Structure
```
tests/
├── Unit/                      # Unit tests
│   ├── AutoloaderTest.php
│   ├── ControllerTest.php
│   ├── ModelTest.php
│   ├── RouterTest.php
│   └── ViewTest.php
├── Integration/               # Integration tests
│   ├── ModelDatabaseTest.php
│   └── RouterControllerTest.php
└── bootstrap.php              # Test configuration
```

### PHPUnit Configuration

**Test Categories**:
- **Unit Tests**: Individual component testing
- **Integration Tests**: Component interaction testing
- **Database Tests**: Database operation testing

**Running Tests**:
```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite Unit

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage
```

## Development Guidelines

### Coding Standards

**PSR Standards**:
- PSR-4: Autoloading standard
- PSR-12: Extended coding style guide
- PSR-1: Basic coding standard

**Code Organization**:
- One class per file
- Meaningful method and variable names
- Comprehensive documentation blocks
- Error handling and logging

### Database Best Practices

**Model Guidelines**:
1. Extend the base Model class
2. Define table property in constructor
3. Use prepared statements for all queries
4. Implement proper error handling
5. Fire appropriate hooks for plugins

**Migration Guidelines**:
1. Include up() and down() methods
2. Use descriptive migration names
3. Handle database-specific differences
4. Test migrations thoroughly

### Plugin Development

**Plugin Guidelines**:
1. Follow WordPress-style plugin structure
2. Use proper plugin headers
3. Implement activation/deactivation hooks
4. Prefix all functions and classes
5. Validate user input and sanitize output

**Hook Usage**:
```php
// Good: Specific, descriptive hook names
HookHelper::add_action('user_login_success', 'my_plugin_log_login');
HookHelper::add_filter('post_content_display', 'my_plugin_add_signature');

// Bad: Generic, unclear hook names
HookHelper::add_action('init', 'do_everything');
```

### Security Best Practices

**Input Validation**:
1. Validate all user input
2. Sanitize data before database storage
3. Escape output in templates
4. Use CSRF tokens for forms
5. Implement rate limiting

**Database Security**:
1. Use prepared statements
2. Validate data types
3. Implement proper error handling
4. Log security events
5. Regular security audits

This technical documentation provides a comprehensive overview of the swCMS architecture, components, and development practices. It serves as a reference for developers working with or extending the CMS system.
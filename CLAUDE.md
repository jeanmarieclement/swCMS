# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Development environment
docker-compose up -d          # Start app (port 80), MySQL (3306), phpMyAdmin (8081)

# Tests
vendor/bin/phpunit             # All tests
vendor/bin/phpunit --testsuite Unit        # Unit only
vendor/bin/phpunit --testsuite Integration # Integration only
vendor/bin/phpunit tests/Unit/RouterTest.php  # Single file

# Code style (PSR-12)
composer cs-check              # phpcs --standard=PSR12 App/
composer cs-fix                # phpcbf --standard=PSR12 App/

# Database migrations
php database/migrate.php up
php database/migrate.php down
php database/migrate.php up 2025_06_19_000010_create_users_table.php

# Installation management
php scripts/create_install_flag.php   # Skip installer (mark as installed)
php scripts/remove_install_flag.php   # Force re-run installer
php scripts/test_install.php          # Diagnostics
```

## Architecture

**Entry point**: `public/index.php` → checks `data/.installed` flag → if missing, runs `InstallController`; otherwise loads `app/Config/config.php` and boots the router.

**Config**: `app/Config/config.php` parses `.env` via `parse_ini_file()` and defines PHP constants (`DB_HOST`, `DB_NAME`, `SITE_URL`, etc.). All code accesses config through these constants or `SystemSettingsHelper::get()` (DB-stored settings with env fallbacks).

**Autoloader**: `app/core/Autoloader.php` — PSR-4, namespace `App\` → `app/`. Called before any class instantiation.

### Request lifecycle

```
public/index.php
  → App\Core\Router        (route pattern matching, regex params)
  → Controller::__call()   (magic dispatch: calls before() → {action}Action() → after())
  → App\Core\View          (Smarty render)
```

### Controllers

Two namespaces with distinct base classes:
- `App\Controllers\Frontend\*` — extends `BaseController` which injects header/footer menu into every render call
- `App\Controllers\Admin\*` — extends `App\Core\Controller` directly
- `App\Controllers\InstallController` — standalone, runs before full boot

All controllers extend `App\Core\Controller` which provides `$this->view`, `$this->roleService`, `$this->settings`, and `$this->commonData`.

### Models

All models extend `App\Core\Model` which injects `$this->db` (PDO singleton) and `$this->hookSystem`. Models live in `app/models/` and map 1:1 to DB tables.

### Database

`App\Core\Database\Database` extends PDO as a singleton (`Database::getInstance()`). Supports MySQL and SQLite; driver selected by `DB_DRIVER` constant. All queries use prepared statements.

### Middlewares

- `AuthMiddleware` — session check + role-based access + timeout enforcement
- `CSRFMiddleware` — validates token on POST/PUT/DELETE; `api/*` routes are exempt

### Helpers (stateless utility classes in `app/helpers/`)

| Helper | Purpose |
|--------|---------|
| `RequestHelper` | `::get()`, `::post()` — **always use for user input**, auto-sanitizes |
| `ValidationHelper` | Validation rules (required, minLength, email, etc.) |
| `SecurityHelper` | CSRF token generation/verification, HTML sanitization |
| `CSRFHelper` | Low-level CSRF token management |
| `SessionHelper` | Session read/write abstraction |
| `AuthHelper` | Auth state checks |
| `SystemSettingsHelper` | DB-stored settings with `::get(key)` / `::all()` |
| `LogHelper` | Structured logging |
| `RedirectHelper` | HTTP redirects |

### Services (`app/services/`)

Business logic layer between controllers and models:
- `PluginService` — plugin discovery, activation, deactivation
- `PluginMenuManager` / `PluginRoutesManager` — dynamic plugin routing
- `RoleService` — RBAC operations
- `ThemeService` — theme management
- `DashboardService` / `AdminMenuService` — admin UI data

### Hook system

WordPress-like singleton (`App\Core\HookSystem`). Accessed via `App\Helpers\HookHelper`:
```php
HookHelper::add_action('init', 'my_callback');
HookHelper::add_filter('content', 'my_filter');
HookHelper::do_action('init');
HookHelper::apply_filters('content', $value);
```

### Plugin system

Plugins live in `plugins/{plugin-name}/{plugin-name}.php`. `PluginService` scans the directory, reads plugin metadata from the file header comment, and handles activation/deactivation. Active plugins are stored in the DB. Plugin routes are dynamically loaded via `app/core/plugin_routes.php`.

### Views / Templates

Smarty 5.5. Template directories:
- **Frontend (themed)**: `public/themes/{active_theme}/templates/` — fallback to `public/themes/default/templates/`
- **Admin**: `app/views/admin/`
- **Auth / Install / Errors**: `app/views/{auth,install,errors}/`

Compiled templates: `app/views/compiled/`. The active theme is stored in `SystemSettingsHelper::get('THEME_ACTIVE')`.

## Key conventions

- **User input**: always via `RequestHelper::get()` / `RequestHelper::post()`, never `$_GET`/`$_POST` directly
- **Output escaping**: `SecurityHelper::sanitize($value)` or Smarty's `{$var|escape}`
- **DB queries**: always prepared statements on `$this->db` (PDO singleton)
- **Roles**: Admin, Editor, Author, Reader — enforced through `RoleService` + `AuthMiddleware`
- **Installation flag**: `data/.installed` — its absence triggers the installer on every request

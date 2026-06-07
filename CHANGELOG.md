# Changelog

All notable changes to swCMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.1] - 2026-06-07

### Security
- Fix SQL injection in InstallController: validate `db_name` against `/^[A-Za-z0-9_]{1,64}$/` before SQL interpolation
- Fix insecure `.env` file permissions: use `fopen`/`chmod(0600)`/`fwrite` instead of `file_put_contents`

### Changed
- Add `namespace App\Helpers` to `PaginationHelper`; update imports in ArticleController and PageController
- Unify flash message API in `MenuController`: replace `setValue('flash_success/error')` with `setFlashMessage()`
- Consolidate `generateSlug()` into base `Model` class; remove duplicate implementations from `Post` and `Page` models
- Replace obsolete Router TODO with accurate comment about `PluginRoutesManager`

### Removed
- Internal development audit reports and planning documents from repository
- Dead code: `AdminController::editContentAction()` (unrouted test method)

## [1.0.0] - 2026-06-07

### Added
- **Core Features**
  - MVC architecture with clean separation of concerns
  - User management with role-based permissions (Admin, Editor, Author, Reader)
  - Content management (Articles, Pages, Categories, Tags)
  - Plugin system with hooks and filters
  - Theme support with automatic fallback
  - Media library with file upload management
  - Comment system with moderation capabilities
  - Admin dashboard with comprehensive backend interface

- **Technical Features**
  - Database flexibility (MySQL and SQLite support)
  - Docker development environment
  - Migration system for database schema management
  - Template caching with Smarty engine
  - Security features (CSRF protection, input sanitization)
  - RESTful routing system
  - Comprehensive test suite (Unit, Integration, Functional)

- **Plugin System**
  - Hook and filter system for extensibility
  - Plugin activation/deactivation management
  - Plugin route management
  - Example plugin with full functionality
  - Backup Manager plugin

- **Admin Interface**
  - Dynamic sidebar menu system
  - Rich text editing with TinyMCE
  - User and role management
  - Content CRUD operations
  - Media management interface
  - System settings configuration
  - Theme management

- **Frontend Features**
  - Responsive theme system
  - Article and page display
  - Category and tag organization
  - Comment functionality
  - Search capabilities
  - SEO-friendly URLs

### Technical Specifications
- **PHP Version**: 7.4+
- **Database**: MySQL 5.7+ / SQLite 3
- **Template Engine**: Smarty 5.x
- **Editor**: TinyMCE
- **Frontend**: Bootstrap-based admin interface
- **Testing**: PHPUnit
- **Containerization**: Docker with docker-compose

### Architecture
- **MVC Pattern**: Strict Model-View-Controller separation
- **PSR-4 Autoloading**: Modern PHP autoloading standards
- **Service Layer**: Business logic separation
- **Middleware System**: Request processing pipeline
- **Helper Classes**: Utility functions organization

---

## Release Notes

### Version 1.0.0 Release Notes

swCMS 1.0.0 marks the first stable release of our modular Content Management System. Built with modern PHP practices and inspired by WordPress, swCMS provides a solid foundation for web development projects.

**Key Highlights:**
- 🏗️ **Modular Architecture** - Clean MVC design for maintainability
- 🔐 **Security First** - Built-in security features and best practices
- 🔌 **Extensible** - Powerful plugin and theme system
- 🐳 **Development Ready** - Complete Docker environment
- 📱 **Modern UI** - Responsive admin interface
- 🧪 **Well Tested** - Comprehensive test coverage

**Getting Started:**
1. Download or clone the repository
2. Run `docker-compose up -d` for instant setup
3. Access your CMS at `http://localhost`
4. Login with admin/password to start building

**For Developers:**
- Full plugin development documentation
- Theme creation guidelines
- Comprehensive API documentation
- Testing framework included

**What's Next:**
- Enhanced plugin directory
- Additional themes
- Advanced caching mechanisms
- Multi-site support
- REST API expansion

---

**Migration Notes:** This is the initial release, no migration required.

**Compatibility:** Requires PHP 7.4+ and MySQL 5.7+ or SQLite 3.
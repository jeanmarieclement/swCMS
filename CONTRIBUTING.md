# Contributing to swCMS

Thank you for your interest in contributing to swCMS! We welcome contributions from developers of all skill levels.

## 🚀 Getting Started

### Development Environment

1. **Fork and Clone**
   ```bash
   git clone https://github.com/jeanmarieclement/swCMS.git
   cd swCMS
   ```

2. **Set up Docker Environment**
   ```bash
   docker-compose up -d
   ```

3. **Install Development Dependencies**
   ```bash
   composer install --dev
   ```

4. **Run Tests**
   ```bash
   vendor/bin/phpunit
   ```

## 📋 Development Guidelines

### Code Standards

- **PSR-12** - Follow PHP coding standards
- **Documentation** - Document all public methods and classes
- **Type Hints** - Use type hints for method parameters and return values
- **Error Handling** - Implement proper error handling and logging

### Architecture Principles

- **MVC Pattern** - Maintain strict separation of concerns
- **DRY** - Don't repeat yourself
- **SOLID** - Follow SOLID principles
- **Security** - Always sanitize inputs and use prepared statements

### File Naming Conventions

- **Controllers** - PascalCase with "Controller" suffix (e.g., `UserController.php`)
- **Models** - PascalCase (e.g., `User.php`)
- **Helpers** - PascalCase with "Helper" suffix (e.g., `StringHelper.php`)
- **Templates** - snake_case with .tpl extension (e.g., `user_list.tpl`)

## 🔧 Making Changes

### Branch Naming

- **Feature**: `feature/short-description`
- **Bug Fix**: `fix/short-description`
- **Documentation**: `docs/short-description`
- **Refactor**: `refactor/short-description`

### Commit Messages

Follow conventional commits format:

```
type(scope): description

[optional body]

[optional footer]
```

Types:
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation changes
- `style` - Code style changes
- `refactor` - Code refactoring
- `test` - Test additions/modifications
- `chore` - Build process or auxiliary tool changes

Examples:
```
feat(auth): add two-factor authentication
fix(admin): resolve pagination issue in user list
docs(api): update plugin development guide
```

## 🧪 Testing

### Running Tests

```bash
# All tests
vendor/bin/phpunit

# Specific test suite
vendor/bin/phpunit --testsuite Unit

# Test with coverage
vendor/bin/phpunit --coverage-html coverage
```

### Writing Tests

- Place unit tests in `tests/Unit/`
- Place integration tests in `tests/Integration/`
- Place functional tests in `tests/Functional/`
- Test file names should match class names with "Test" suffix

Example test:
```php
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Helpers\StringHelper;

class StringHelperTest extends TestCase
{
    public function testSlugGeneration()
    {
        $result = StringHelper::generateSlug('Hello World');
        $this->assertEquals('hello-world', $result);
    }
}
```

## 🔌 Plugin Development

### Creating a Plugin

1. Create plugin directory in `plugins/`
2. Create main plugin file with header:
   ```php
   <?php
   /**
    * Plugin Name: Your Plugin Name
    * Plugin URI: https://yoursite.com
    * Description: Plugin description
    * Version: 1.0.0
    * Author: Your Name
    * License: MIT
    */
   ```

3. Use hooks and filters:
   ```php
   HookHelper::add_action('init', 'your_plugin_init');
   HookHelper::add_filter('content', 'your_content_filter');
   ```

### Plugin Structure
```
plugins/your-plugin/
├── your-plugin.php        # Main plugin file
├── README.md              # Plugin documentation
├── controllers/           # Plugin controllers
├── models/                # Plugin models
├── views/                 # Plugin templates
├── assets/                # CSS/JS/images
└── languages/             # Translation files
```

## 🎨 Theme Development

### Creating a Theme

1. Create theme directory in `public/themes/`
2. Include required templates:
   - `index.tpl` - Homepage
   - `article.tpl` - Article view
   - `page.tpl` - Page view
   - `404.tpl` - Error page

### Theme Structure
```
public/themes/your-theme/
├── index.tpl              # Homepage template
├── article.tpl            # Article template
├── page.tpl               # Page template
├── 404.tpl                # Error page
├── css/                   # Stylesheets
├── js/                    # JavaScript
├── images/                # Theme images
└── README.md              # Theme documentation
```

## 📚 Documentation

### Code Documentation

Use PHPDoc format for all classes and methods:

```php
/**
 * Generate a URL slug from text
 *
 * @param string $text The text to convert
 * @param int $maxLength Maximum slug length
 * @return string The generated slug
 * @throws InvalidArgumentException If text is empty
 */
public function generateSlug(string $text, int $maxLength = 100): string
{
    // Implementation
}
```

### API Documentation

Document all public APIs and plugin hooks in the wiki.

## 🐛 Bug Reports

### Before Submitting

1. Check existing issues
2. Test with latest version
3. Reproduce in clean environment

### Bug Report Template

```markdown
**Description**
Clear description of the bug

**Steps to Reproduce**
1. Go to...
2. Click on...
3. See error

**Expected Behavior**
What should happen

**Screenshots**
If applicable

**Environment**
- swCMS Version:
- PHP Version:
- Database:
- Browser:
```

## 💡 Feature Requests

### Feature Request Template

```markdown
**Feature Description**
Clear description of the feature

**Use Case**
Why is this feature needed?

**Proposed Solution**
How should it work?

**Alternatives**
Other solutions considered
```

## 🔍 Code Review Process

### Pull Request Guidelines

1. **Clear Description** - Explain what and why
2. **Small Changes** - Keep PRs focused and small
3. **Tests** - Include tests for new features
4. **Documentation** - Update docs if needed
5. **Backwards Compatibility** - Maintain API compatibility

### Review Checklist

- [ ] Code follows style guidelines
- [ ] Tests pass
- [ ] Documentation updated
- [ ] No breaking changes
- [ ] Security considerations addressed

## 🏷️ Release Process

### Versioning

We follow [Semantic Versioning](https://semver.org/):
- **MAJOR** - Breaking changes
- **MINOR** - New features (backwards compatible)
- **PATCH** - Bug fixes (backwards compatible)

### Release Checklist

1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create release notes
4. Tag release
5. Update documentation

## 📞 Getting Help

- **Issues** - [GitHub Issues](../../issues)
- **Discussions** - [GitHub Discussions](../../discussions)
- **Email** - [maintainer@swcms.org](mailto:maintainer@swcms.org)

## 📜 Code of Conduct

Please note that this project is released with a [Contributor Code of Conduct](CODE_OF_CONDUCT.md). By participating in this project you agree to abide by its terms.

---

Thank you for contributing to swCMS! 🎉
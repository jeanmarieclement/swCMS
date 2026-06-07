# swCMS Plugin System

This directory contains plugins for the swCMS system. Plugins extend the functionality of the CMS using an extensible hook system.

## Plugin Structure

Each plugin should be in its own directory with the following structure:

```
plugins/
└── your-plugin/
    ├── your-plugin.php     # Main plugin file (required)
    ├── hooks.php           # Hook definitions (optional)
    ├── settings.php        # Custom settings interface (optional)
    ├── assets/             # CSS, JS, images (optional)
    │   ├── css/
    │   ├── js/
    │   └── img/
    └── README.md           # Plugin documentation (optional)
```

## Main Plugin File

The main plugin file should include a header comment with metadata:

```php
<?php
/**
 * Plugin Name: Your Plugin Name
 * Description: Brief description of what your plugin does
 * Version: 1.0.0
 * Author: Your Name
 * Requires: 1.0.0
 * Tested up to: 1.0.0
 */

// Prevent direct access
if (!defined('APP_PATH')) {
    exit('Direct access denied');
}

// Plugin activation hook
function your_plugin_activate() {
    // Activation logic here
}

// Plugin deactivation hook  
function your_plugin_deactivate() {
    // Deactivation logic here
}

// Main plugin initialization
function your_plugin_init() {
    // Plugin functionality here
}

// Initialize the plugin
your_plugin_init();
```

## Hook System

The hook system provides two types of hooks:

### Actions
Actions let you execute code at specific points:

```php
$hookSystem = \App\Core\HookSystem::getInstance();
$hookSystem->addAction('init', 'my_function');
$hookSystem->doAction('init'); // Triggers all functions hooked to 'init'
```

### Filters
Filters let you modify data:

```php
$hookSystem->addFilter('the_content', 'my_content_filter');
$content = $hookSystem->applyFilters('the_content', $content);
```

## Available Hooks

### Core Hooks
- `init` - After CMS initialization
- `plugins_loaded` - After all plugins are loaded
- `cms_head` - In the HTML head section
- `cms_footer` - Before closing body tag
- `admin_head` - In admin head section
- `admin_footer` - In admin footer
- `admin_menu` - Admin menu generation
- `admin_dashboard_widgets` - Dashboard widgets

### Content Hooks
- `the_content` - Filter page/post content
- `the_title` - Filter page/post titles
- `the_excerpt` - Filter excerpts

### Database Hooks
- `save_post` - Before/after saving posts
- `delete_post` - Before/after deleting posts
- `save_user` - Before/after saving users
- `delete_user` - Before/after deleting users

## Plugin Settings

Plugins can store settings in the database using SystemSettingsHelper:

```php
use App\Helpers\SystemSettingsHelper;

// Save setting
SystemSettingsHelper::set('PLUGIN_YOURPLUGIN_SETTING', 'value');

// Get setting
$value = SystemSettingsHelper::get('PLUGIN_YOURPLUGIN_SETTING');
```

For complex settings, use JSON:

```php
$settings = ['option1' => 'value1', 'option2' => 'value2'];
SystemSettingsHelper::set('PLUGIN_YOURPLUGIN_SETTINGS', json_encode($settings));

$settings = json_decode(SystemSettingsHelper::get('PLUGIN_YOURPLUGIN_SETTINGS'), true);
```

## Custom Settings Interface

Create a `settings.php` file for a custom settings interface:

```php
<?php
function your_plugin_render_settings($current_settings = []) {
    // Return HTML for settings form
    return '<div class="plugin-settings">...</div>';
}

function your_plugin_validate_settings($settings) {
    // Validate and sanitize settings
    return $validated_settings;
}
```

## Best Practices

1. **Naming**: Use unique function names prefixed with your plugin name
2. **Security**: Always sanitize input and escape output
3. **Performance**: Only load code when needed
4. **Compatibility**: Test with different themes and other plugins
5. **Documentation**: Include clear documentation and comments
6. **Error Handling**: Use try/catch blocks for critical operations
7. **Logging**: Use LogHelper for debugging and error logging

## Example Plugin

See the `example-plugin` directory for a complete example demonstrating:
- Plugin header and structure
- Activation/deactivation hooks
- Action and filter hooks
- Settings management
- Admin integration

## Plugin Management

Plugins are managed through the admin interface at `/admin/plugins`:
- View all installed plugins
- Activate/deactivate plugins
- Configure plugin settings
- View plugin details

Active plugins are stored in the `ACTIVE_PLUGINS` setting as a JSON array.
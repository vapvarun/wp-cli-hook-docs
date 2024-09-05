# WP-CLI Comprehensive Documentation Generator

## Description

The WP-CLI Comprehensive Documentation Generator is a powerful tool that allows developers to automatically generate detailed documentation for their WordPress plugins and themes. This plugin focuses on capturing key components such as hooks, filters, actions, shortcodes, custom post types, and more.

## Features

- **do_action Hooks:** Extract and document all `do_action` hooks.
- **apply_filters Hooks:** Extract and document all `apply_filters` hooks.
- **add_action Hooks:** Extract and document all `add_action` hooks.
- **Shortcodes:** Document shortcodes, including their callback functions and attributes.
- **Custom Post Types:** Document custom post types and associated meta fields.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-cli-comprehensive-doc-gen` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Ensure WP-CLI is installed and available on your server.

## Usage

Once the plugin is installed and activated, you can use the following WP-CLI commands to generate documentation:

### Command: `wp doc-gen generate <plugin_dir>`

This command generates comprehensive documentation for the specified plugin or theme directory.

### Example Usage

```bash
wp doc-gen generate my-plugin-directory
```

### Command Options

- `<plugin_dir>`: The directory of the plugin or theme you want to scan. This is a required parameter.

### Output

The generated documentation will be saved as a Markdown file in the `uploads/plugin-docs` directory of your WordPress installation. The filename will be based on the name of the plugin or theme directory.

### Example Documentation Output

The documentation will include sections like:

- **do_action Hooks**: Lists all `do_action` hooks with their descriptions, file paths, line numbers, and arguments.
- **apply_filters Hooks**: Lists all `apply_filters` hooks with their descriptions, file paths, line numbers, and arguments.
- **add_action Hooks**: Lists all `add_action` hooks with their descriptions, file paths, line numbers, callback functions, priorities, and accepted arguments.
- **Shortcodes**: Provides details on all shortcodes, including their callback functions, file paths, line numbers, and attributes.
- **Custom Post Types**: Documents custom post types, including associated meta fields, file paths, and line numbers.

### Example Markdown Format

````markdown
## do_action Hooks in `includes/class-myplugin.php`

### Hook: `myplugin_after_save`

- **Description:** Triggered after a custom post is saved.
- **File:** `includes/class-myplugin.php`
- **Line:** 145
- **Arguments:**
  1. `$post_id` - The ID of the post that was saved.
  2. `$post` - The post object.

**Code Example:**

```php
do_action( 'myplugin_after_save', $post_id, $post );
```
````

````

### Shortcode Example

```markdown
## Shortcodes in `includes/class-myplugin.php`

### Shortcode: `[myplugin_custom_form]`
- **Description:** Displays a custom form for users.
- **File:** `includes/class-myplugin.php`
- **Line:** 245
- **Callback Function:** `myplugin_render_custom_form`
- **Attributes:**
  - `title` - The title of the form.
  - `id` - The ID of the form.

**Code Example:**
```php
add_shortcode( 'myplugin_custom_form', 'myplugin_render_custom_form' );
````

**Usage Example:**

```php
[myplugin_custom_form title="Contact Us" id="123"]
```

```

## Changelog

### 1.2.0
- **Feature:** Modular structure to handle different datatypes (hooks, filters, actions, shortcodes, custom post types).
- **Improvement:** Enhanced documentation formatting to include detailed code examples.

### 1.1.0
- **Feature:** Added support for shortcode documentation with attributes and usage examples.
- **Feature:** Basic documentation for custom post types and meta fields.

### 1.0.0
- Initial release.

## Author

- **vapvarun**
- [Wbcom Designs](https://wbcomdesigns.com)

## License

This plugin is licensed under the GPLv2 or later.
```

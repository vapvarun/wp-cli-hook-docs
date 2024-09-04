# WP-CLI Hook Documentation Generator

This plugin provides a WP-CLI command to generate documentation for all `do_action` and `apply_filters` hooks used in a specified plugin directory.

## Installation

1. Download or clone the plugin into your WordPress installation's `wp-content/plugins` directory:

   ```bash
   git clone https://your-repository-url/wp-content/plugins/wp-cli-hook-docs.git wp-content/plugins/wp-cli-hook-docs
   ```

2. Activate the plugin via the WordPress Admin Dashboard:

   - Go to **Plugins > Installed Plugins**.
   - Activate **WP-CLI Hook Documentation Generator**.

## Usage

### Generate Hook Documentation

To generate documentation for a plugin, run the following WP-CLI command:

```bash
wp hook-docs generate <plugin-directory>
```

Replace `<plugin-directory>` with the directory name of the plugin you want to scan. For example, if your plugin is located in `wp-content/plugins/my-plugin`, you would run:

```bash
wp hook-docs generate my-plugin
```

### Output

The documentation will be generated in the `wp-content/uploads/hook-docs/<plugin-directory>` directory. The documentation is organized as separate Markdown files, each corresponding to a PHP file in the plugin that contains hooks.

- Each Markdown file includes:
  - The hook name.
  - The type of hook (`do_action` or `apply_filters`).
  - The file and line number where the hook is defined.
  - A brief description of what the hook does (if available in the code comments).

### Skipped Directories

The following directories are skipped during the scan to improve performance and avoid unnecessary files:

- `assets`
- `images`
- `css`
- `js`
- `vendor`
- `node_modules`

If your plugin contains additional directories that should be skipped, you can modify the `$skip_dirs` array in the plugin's code.

### Examples

#### Example 1: Generate Documentation for a Simple Plugin

```bash
wp hook-docs generate simple-plugin
```

#### Example 2: Generate Documentation for a Complex Plugin

```bash
wp hook-docs generate complex-plugin
```

### Notes

- Ensure that your hooks are well-commented in the code to get meaningful descriptions in the documentation.
- If the plugin directory does not exist, the command will return an error.

### Author

- **Author:** vapvarun
- **Website:** [https://wbcomdesigns.com](https://wbcomdesigns.com)

<?php
/**
 * Plugin Name: WP-CLI Hook Docs
 * Description: Generate documentation for WordPress hooks and shortcodes via WP-CLI.
 * Version: 1.0.0
 * Author: Your Name
 */

 // Autoload classes from the classes directory
spl_autoload_register(function ($class) {
    $prefix = 'WPCLIDocGen\\';
    $base_dir = __DIR__ . '/classes/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name,
    // append with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


if (defined('WP_CLI') && WP_CLI) {
    require_once __DIR__ . '/classes/Modular_Documentation_Command.php';
    
    WP_CLI::add_command('doc-gen', 'WPCLIDocGen\Modular_Documentation_Command');
}

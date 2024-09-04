<?php
/**
 * Plugin Name: WP-CLI Comprehensive Documentation Generator
 * Description: A WP-CLI command to generate comprehensive documentation for a plugin, including hooks, classes, methods, functions, shortcodes, etc.
 * Version: 1.0.0
 * Author: vapvarun
 * Author URI: https://wbcomdesigns.com
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class Comprehensive_Documentation_Command {

        /**
         * Generate comprehensive documentation for a plugin.
         *
         * ## OPTIONS
         *
         * <plugin_dir>
         * : The directory of the plugin you want to scan.
         *
         * ## EXAMPLES
         *
         *     wp doc-gen generate my-plugin-directory
         *
         * @when after_wp_load
         */
        public function generate( $args, $assoc_args ) {
            $plugin_dir = realpath(trailingslashit( WP_PLUGIN_DIR ) . $args[0]);
            
            if ( ! is_dir( $plugin_dir ) ) {
                WP_CLI::error( "Invalid plugin directory: $plugin_dir" );
                return;
            }

            $upload_dir = wp_upload_dir();
            $output_dir = trailingslashit( $upload_dir['basedir'] ) . 'plugin-docs';
            $output_file = $output_dir . '/' . basename( $plugin_dir ) . '-developer-guide.md';

            if ( ! file_exists( $output_dir ) ) {
                if ( ! mkdir( $output_dir, 0755, true ) ) {
                    WP_CLI::error( "Failed to create directory: $output_dir" );
                    return;
                }
            }

            $files = $this->scan_files( $plugin_dir );

            $toc = "# Plugin Developer Guide for " . basename( $plugin_dir ) . "\n\n";
            $toc .= "## Table of Contents\n\n";
            $content = "";

            foreach ( $files as $file ) {
                if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
                    $relative_path = str_replace( realpath($plugin_dir), '', realpath($file));
                    $file_content = file_get_contents($file);

                    $content .= $this->log_hooks($relative_path, $file_content);
                    $content .= $this->log_classes_and_methods($relative_path, $file_content);
                    $content .= $this->log_functions($relative_path, $file_content);
                    $content .= $this->log_shortcodes($relative_path, $file_content);
                    $content .= $this->log_template_overrides($relative_path, $file_content);

                    $toc .= "- [$relative_path](#$relative_path)\n";
                }
            }

            $doc = $toc . "\n" . $content;

            file_put_contents($output_file, $doc);

            if ( file_exists($output_file) ) {
                WP_CLI::success( "Documentation generated at: $output_file" );
            } else {
                WP_CLI::error( "Failed to create documentation file: $output_file" );
            }
        }

        private function scan_files($dir, &$results = array()) {
            $skip_dirs = array('assets', 'images', 'css', 'js', 'vendor', 'node_modules','codestar-framework','wbcom');

            $files = scandir($dir);

            foreach ($files as $key => $value) {
                $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

                if (!is_dir($path)) {
                    $results[] = $path;
                } else if ($value != "." && $value != ".." && !in_array(basename($path), $skip_dirs)) {
                    $this->scan_files($path, $results);
                }
            }

            return $results;
        }

        private function log_hooks($relative_path, $file_content) {
            $pattern = '/(do_action|apply_filters)\s*\(\s*([\'"][^\'"]+[\'"])/';
            preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches[2])) {
                $section = "## Hooks in $relative_path\n\n";

                foreach ($matches[2] as $key => $match) {
                    $hook_name = trim($match[0], "'\"");
                    $section .= "### Hook: `$hook_name`\n";
                    $section .= "- **Type:** {$matches[1][$key][0]}\n";
                    $section .= "- **File:** `$relative_path`\n";
                    $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $match[1]), "\n") + 1) . "\n\n";
                }

                return $section;
            }

            return '';
        }

        private function log_classes_and_methods($relative_path, $file_content) {
            $pattern = '/class\s+(\w+)|function\s+(\w+)/';
            preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

            $section = '';
            if (!empty($matches[1])) {
                $section .= "## Classes and Methods in $relative_path\n\n";

                foreach ($matches[1] as $class) {
                    if ($class[0]) {
                        $class_name = $class[0];
                        $section .= "### Class: `$class_name`\n\n";
                    }
                }

                foreach ($matches[2] as $method) {
                    if ($method[0]) {
                        $method_name = $method[0];
                        $section .= "#### Method: `$method_name`\n";
                        $section .= "- **File:** `$relative_path`\n";
                        $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $method[1]), "\n") + 1) . "\n\n";
                    }
                }
            }

            return $section;
        }

        private function log_functions($relative_path, $file_content) {
            $pattern = '/function\s+(\w+)/';
            preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches[1])) {
                $section = "## Functions in $relative_path\n\n";

                foreach ($matches[1] as $function) {
                    $function_name = $function[0];
                    $section .= "### Function: `$function_name`\n";
                    $section .= "- **File:** `$relative_path`\n";
                    $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $function[1]), "\n") + 1) . "\n\n";
                }

                return $section;
            }

            return '';
        }

        private function log_shortcodes($relative_path, $file_content) {
            $pattern = '/add_shortcode\s*\(\s*[\'"]([^\'"]+)[\'"]/';
            preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

            if (!empty($matches[1])) {
                $section = "## Shortcodes in $relative_path\n\n";

                foreach ($matches[1] as $shortcode) {
                    $shortcode_name = $shortcode[0];
                    $section .= "### Shortcode: `$shortcode_name`\n";
                    $section .= "- **File:** `$relative_path`\n";
                    $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $shortcode[1]), "\n") + 1) . "\n\n";
                }

                return $section;
            }

            return '';
        }

        private function log_template_overrides($relative_path, $file_content) {
            // You can add custom logic here to identify and log template overrides
            // For example, if the plugin uses template parts, document them here

            // Placeholder for template logging logic
            return '';
        }
    }

    WP_CLI::add_command('doc-gen', 'Comprehensive_Documentation_Command');
}

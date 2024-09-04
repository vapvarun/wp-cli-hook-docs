<?php
/**
 * Plugin Name: WP-CLI Hook Documentation Generator
 * Description: A WP-CLI command to generate documentation for do_action and apply_filters hooks in a plugin.
 * Version: 1.5.0
 * Author: vapvarun
 * Author URI: https://wbcomdesigns.com
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

    class Hook_Documentation_Command {

        /**
         * Generate documentation for hooks used in a plugin.
         *
         * ## OPTIONS
         *
         * <plugin_dir>
         * : The directory of the plugin you want to scan.
         *
         * ## EXAMPLES
         *
         *     wp hook-docs generate my-plugin-directory
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
            $output_dir = trailingslashit( $upload_dir['basedir'] ) . 'hook-docs';
            $output_file = $output_dir . '/' . basename( $plugin_dir ) . '-hooks-documentation.md';

            if ( ! file_exists( $output_dir ) ) {
                if ( ! mkdir( $output_dir, 0755, true ) ) {
                    WP_CLI::error( "Failed to create directory: $output_dir" );
                    return;
                }
            }

            $files = $this->scan_files( $plugin_dir );
            $all_hooks = [];

            $toc = "# Hooks Documentation for Plugin: " . basename( $plugin_dir ) . "\n\n";
            $toc .= "## Table of Contents\n\n";
            $summary = "## Summary of Hooks\n\n";
            $details = "";

            foreach ( $files as $file ) {
                if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
                    $hooks = $this->extract_hooks( $file );
                    if ( ! empty( $hooks ) ) {
                        $relative_path = str_replace( realpath($plugin_dir), '', realpath($file) );
                        $section_title = "### Hooks in $relative_path";
                        $toc .= "- [$section_title](#hooks-in-" . str_replace([' ', '/'], ['-', ''], strtolower($relative_path)) . ")\n";
                        $details .= $this->generate_docs_section( $hooks, $relative_path, $summary );
                    }
                }
            }

            $doc = $toc . "\n" . $summary . "\n" . $details;

            file_put_contents( $output_file, $doc );

            if ( file_exists( $output_file ) ) {
                WP_CLI::success( "Documentation generated at: $output_file" );
            } else {
                WP_CLI::error( "Failed to create documentation file: $output_file" );
            }
        }

        private function scan_files( $dir, &$results = array() ) {
            $skip_dirs = array('assets', 'images', 'css', 'js', 'vendor', 'node_modules');

            $files = scandir( $dir );

            foreach ( $files as $key => $value ) {
                $path = realpath( $dir . DIRECTORY_SEPARATOR . $value );

                if ( !is_dir( $path ) ) {
                    $results[] = $path;
                } else if ( $value != "." && $value != ".." && !in_array(basename($path), $skip_dirs) ) {
                    $this->scan_files( $path, $results );
                }
            }

            return $results;
        }

        private function extract_hooks( $file ) {
            $content = file_get_contents( $file );
            $pattern = '/(do_action|apply_filters)\s*\(\s*([\'"][^\'"]+[\'"])/';
            preg_match_all( $pattern, $content, $matches, PREG_OFFSET_CAPTURE );
            $hooks = [];

            foreach ( $matches[2] as $key => $match ) {
                $hook_name = trim( $match[0], "'\"" );
                $hooks[] = [
                    'type'      => $matches[1][$key][0],
                    'hook_name' => $hook_name,
                    'file'      => $file,
                    'line'      => substr_count( substr( $content, 0, $match[1] ), "\n" ) + 1,
                    'description' => $this->get_hook_description( $hook_name, $content, $match[1] ),
                ];
            }

            return $hooks;
        }

        private function get_hook_description( $hook_name, $content, $position ) {
            // Attempt to extract a comment above the hook
            $before_hook = substr( $content, 0, $position );
            $lines = array_reverse( explode( "\n", $before_hook ) );

            $description = '';
            foreach ( $lines as $line ) {
                $line = trim( $line );
                if ( strpos( $line, '*/' ) !== false ) {
                    continue;
                }
                if ( strpos( $line, '/*' ) !== false || strpos( $line, '//' ) === 0 ) {
                    $description = trim( preg_replace( '/^[\/*]+/', '', $line ) ) . "\n" . $description;
                } else {
                    break;
                }
            }

            return $description ?: 'No description available.';
        }

        private function generate_docs_section( $hooks, $relative_path, &$summary ) {
            if ( empty( $hooks ) ) {
                return '';
            }

            $doc = "### Hooks in $relative_path\n\n";

            foreach ( $hooks as $hook ) {
                $doc .= "#### `{$hook['hook_name']}`\n";
                $doc .= "- **Type:** {$hook['type']}\n";
                $doc .= "- **File:** `$relative_path`\n";
                $doc .= "- **Line:** {$hook['line']}\n";
                $doc .= "- **Description:**\n```\n{$hook['description']}\n```\n\n";

                $summary .= "- [{$hook['hook_name']}]({$hook['hook_name']}) in $relative_path at line {$hook['line']}\n";
            }

            return $doc;
        }
    }

    WP_CLI::add_command( 'hook-docs', 'Hook_Documentation_Command' );
}

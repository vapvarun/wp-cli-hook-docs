<?php
/**
 * Plugin Name: WP-CLI Hook Documentation Generator
 * Description: A WP-CLI command to generate documentation for do_action and apply_filters hooks in a plugin.
 * Version: 1.3.0
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
            $output_dir = trailingslashit( $upload_dir['basedir'] ) . 'hook-docs/' . basename( $plugin_dir );

            if ( ! file_exists( $output_dir ) ) {
                if ( ! mkdir( $output_dir, 0755, true ) ) {
                    WP_CLI::error( "Failed to create directory: $output_dir" );
                    return;
                }
            }

            $files = $this->scan_files( $plugin_dir );
            $all_hooks = [];

            foreach ( $files as $file ) {
                if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'php' ) {
                    $hooks = $this->extract_hooks( $file );
                    if ( ! empty( $hooks ) ) {
                        $this->generate_docs( $hooks, $plugin_dir, $file, $output_dir );
                    }
                }
            }

            WP_CLI::success( "Documentation generated in: $output_dir" );
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

        private function generate_docs( $hooks, $plugin_dir, $file, $output_dir ) {
            if ( empty( $hooks ) ) {
                return;
            }

            $relative_path = str_replace( realpath($plugin_dir), '', realpath($file) );
            $doc_filename = str_replace( array( '/', '\\', '.php' ), array( '-', '-', '' ), $relative_path ) . '-hooks.md';
            $doc_path = trailingslashit( $output_dir ) . $doc_filename;

            $doc = "# Hooks Documentation for $relative_path\n\n";

            foreach ( $hooks as $hook ) {
                $doc .= "## `{$hook['hook_name']}`\n";
                $doc .= "- **Type:** {$hook['type']}\n";
                $doc .= "- **File:** `{$hook['file']}`\n";
                $doc .= "- **Line:** {$hook['line']}\n";
                $doc .= "- **Description:**\n```\n{$hook['description']}\n```\n\n";
            }

            file_put_contents( $doc_path, $doc );

            // Verify that the file was created
            if ( ! file_exists( $doc_path ) ) {
                WP_CLI::error( "Failed to create documentation file: $doc_path" );
            }
        }
    }

    WP_CLI::add_command( 'hook-docs', 'Hook_Documentation_Command' );
}

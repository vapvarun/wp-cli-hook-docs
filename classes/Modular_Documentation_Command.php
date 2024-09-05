<?php

namespace WPCLIDocGen;

use WP_CLI;

class Modular_Documentation_Command {

    public function generate($args, $assoc_args) {
        $plugin_dir = realpath(trailingslashit(WP_PLUGIN_DIR) . $args[0]);

        if (!is_dir($plugin_dir)) {
            WP_CLI::error("Invalid plugin directory: $plugin_dir");
            return;
        }

        $upload_dir = wp_upload_dir();
        $output_dir = trailingslashit($upload_dir['basedir']) . 'plugin-docs';
        $output_file = $output_dir . '/' . basename($plugin_dir) . '-developer-guide.md';

        if (!file_exists($output_dir)) {
            if (!mkdir($output_dir, 0755, true)) {
                WP_CLI::error("Failed to create directory: $output_dir");
                return;
            }
        }

        $files = $this->scan_files($plugin_dir);

        $toc = "# Plugin Developer Guide for " . basename($plugin_dir) . "\n\n";
        $toc .= "## Table of Contents\n\n";
        $content = "";

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $relative_path = str_replace(realpath($plugin_dir), '', realpath($file));
                $file_content = file_get_contents($file);

                $do_action_parser = new Add_Actions_Parser();
                $apply_filters_parser = new Apply_Filters_Parser();
                $shortcodes_parser = new Shortcodes_Parser();
                $custom_post_types_parser = new Custom_Post_Types_Parser();

                $do_action_hooks = $do_action_parser->parse($relative_path, $file_content);
                $apply_filters_hooks = $apply_filters_parser->parse($relative_path, $file_content);
                $shortcodes = $shortcodes_parser->parse($relative_path, $file_content);
                $custom_posts = $custom_post_types_parser->parse($relative_path, $file_content);

                if ($do_action_hooks || $apply_filters_hooks || $shortcodes || $custom_posts) {
                    $toc .= "- [$relative_path](#$relative_path)\n";
                }

                if ($do_action_hooks) {
                    $content .= "## do_action Hooks in `$relative_path`\n\n" . $do_action_hooks;
                }

                if ($apply_filters_hooks) {
                    $content .= "## apply_filters Hooks in `$relative_path`\n\n" . $apply_filters_hooks;
                }

                if ($shortcodes) {
                    $content .= "## Shortcodes in `$relative_path`\n\n" . $shortcodes;
                }

                if ($custom_posts) {
                    $content .= "## Custom Post Types in `$relative_path`\n\n" . $custom_posts;
                }
            }
        }

        $doc = $toc . "\n" . $content;

        file_put_contents($output_file, $doc);

        if (file_exists($output_file)) {
            WP_CLI::success("Documentation generated at: $output_file");
        } else {
            WP_CLI::error("Failed to create documentation file: $output_file");
        }
    }

    private function scan_files($dir, &$results = array()) {
        $skip_dirs = array('assets', 'images', 'css', 'js', 'vendor', 'node_modules', 'codestar-framework', 'wbcom', 'plugin-update-checker');

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
}

WP_CLI::add_command('doc-gen', 'WPCLIDocGen\Modular_Documentation_Command');

<?php

namespace WPCLIDocGen;

class Custom_Post_Types_Parser extends Base_Parser {

    public function parse($relative_path, $file_content) {
        $pattern = '/register_post_type\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(\[|\()/';
        preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[1])) {
            $section = "## Custom Post Types in `$relative_path`\n\n";

            foreach ($matches[1] as $key => $post_type) {
                $post_type_name = $post_type[0];
                $section .= "### Custom Post Type: `$post_type_name`\n";
                $section .= "- **File:** `\"{$relative_path}\"`\n";
                $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $post_type[1]), "\n") + 1) . "\n\n";

                // Extract and log custom meta fields for this post type if they are defined.
                $meta_pattern = '/add_post_meta\s*\(\s*\$post_id\s*,\s*[\'"]([^\'"]+)[\'"]\s*,/';
                preg_match_all($meta_pattern, $file_content, $meta_matches, PREG_OFFSET_CAPTURE);

                if (!empty($meta_matches[1])) {
                    $section .= "#### Meta Fields:\n";
                    foreach ($meta_matches[1] as $meta_key) {
                        $meta_name = $meta_key[0];
                        $section .= "- `$meta_name`\n";
                    }
                    $section .= "\n";
                }
            }

            return $section;
        }

        return '';
    }
}

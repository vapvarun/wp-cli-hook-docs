<?php

namespace WPCLIDocGen;

class Apply_Filters_Parser extends Base_Parser {

    public function parse($relative_path, $file_content) {
        $pattern = '/apply_filters\s*\(\s*([\'"][^\'"]+[\'"]),\s*(.*?)[\n;]/';
        preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[0])) {
            $section = "## apply_filters Hooks in `$relative_path`\n\n";

            foreach ($matches[0] as $key => $full_match) {
                $hook_name = trim($matches[1][$key][0], "'\"");
                $arguments = $matches[2][$key][0];
                $line_number = substr_count(substr($file_content, 0, $full_match[1]), "\n") + 1;

                $section .= "### Hook: `$hook_name`\n";
                $section .= "- **Type:** apply_filters\n";
                $section .= "- **File:** `\"{$relative_path}\"`\n";
                $section .= "- **Line:** $line_number\n";
                $section .= "- **Arguments:**\n";

                // Splitting arguments by comma for better readability
                $args = array_map('trim', explode(',', $arguments));
                foreach ($args as $index => $arg) {
                    $section .= "  " . ($index + 1) . ". `$arg` - Description of the argument.\n";
                }

                $section .= "\n";
            }

            return $section;
        }

        return '';
    }
}

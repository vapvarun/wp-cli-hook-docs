<?php

namespace WPCLIDocGen;

class Hooks_Parser extends Base_Parser {

    public function parse($relative_path, $file_content) {
        $pattern = '/(do_action|apply_filters)\s*\(\s*([\'"][^\'"]+[\'"].*?);/s';
        preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[2])) {
            $section = "## Hooks in `$relative_path`\n\n";

            foreach ($matches[0] as $key => $full_match) {
                $hook_name = trim($matches[2][$key][0], "'\"");
                $hook_type = $matches[1][$key][0];
                $line_number = substr_count(substr($file_content, 0, $full_match[1]), "\n") + 1;

                $section .= "### Hook: `$hook_name`\n";
                $section .= "- **Type:** $hook_type\n";
                $section .= "- **File:** `\"{$relative_path}\"`\n";
                $section .= "- **Line:** $line_number\n";

                // Capture the entire line for code example
                $code_example = trim($full_match[0]);
                $section .= "**Code Example:**\n```php\n";
                $section .= $code_example;
                $section .= "\n```\n\n";
            }

            return $section;
        }

        return '';
    }
}



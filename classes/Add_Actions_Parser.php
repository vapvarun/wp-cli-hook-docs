<?php

namespace WPCLIDocGen;

class Add_Actions_Parser extends Base_Parser {

    public function parse($relative_path, $file_content) {
        $pattern = '/add_action\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([\'"]([^\'"]+)[\'"]|\s*([^\s]+))/';
        preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[1])) {
            $section = "## add_action Hooks in `$relative_path`\n\n";

            foreach ($matches[1] as $key => $hook) {
                $hook_name = $hook[0];
                $callback_function = $matches[2][$key][0];

                $section .= "### Hook: `$hook_name`\n";
                $section .= "- **Callback Function:** `$callback_function`\n";
                $section .= "- **File:** `\"{$relative_path}\"`\n";
                $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $hook[1]), "\n") + 1) . "\n\n";
            }

            return $section;
        }

        return '';
    }
}

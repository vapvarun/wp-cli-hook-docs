<?php

namespace WPCLIDocGen;

class Shortcodes_Parser extends Base_Parser {

    public function parse($relative_path, $file_content) {
        $pattern = '/add_shortcode\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*([\'"]([^\'"]+)[\'"]|\s*([^\s]+))/';
        preg_match_all($pattern, $file_content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[1])) {
            $section = "## Shortcodes in `$relative_path`\n\n";

            foreach ($matches[1] as $key => $shortcode) {
                $shortcode_name = $shortcode[0];
                $callback_function = $matches[2][$key][0];
                $args = $this->extract_shortcode_arguments($callback_function, $file_content);

                $section .= "### Shortcode: `$shortcode_name`\n";
                $section .= "- **Callback Function:** `$callback_function`\n";
                $section .= "- **File:** `\"{$relative_path}\"`\n";
                $section .= "- **Line:** " . (substr_count(substr($file_content, 0, $shortcode[1]), "\n") + 1) . "\n";
                if ($args) {
                    $section .= "- **Arguments:**\n" . $args . "\n";
                }
                $section .= "**Code Example:**\n```php\n";
                $section .= "[" . $shortcode_name . "]\n```\n\n";
            }

            return $section;
        }

        return '';
    }

    private function extract_shortcode_arguments($callback_function, $file_content) {
        $pattern = '/function\s+' . preg_quote($callback_function) . '\s*\((.*?)\)/';
        preg_match($pattern, $file_content, $matches);

        if (!empty($matches[1])) {
            $args = explode(',', $matches[1]);
            $parsed_args = "";

            foreach ($args as $index => $arg) {
                $arg_name = trim($arg);
                if ($arg_name) {
                    $parsed_args .= "  " . ($index + 1) . ". `$arg_name` - Description of the argument.\n";
                }
            }

            return $parsed_args;
        }

        return '';
    }
}

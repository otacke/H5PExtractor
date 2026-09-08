<?php
/**
 * Proof of concept code for extracting and displaying H5P content server-side.
 *
 * PHP version 8
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */

namespace H5PExtractor;

/**
 * Class for handling text.
 *
 * @category File
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class TextUtils
{
    /**
     * Convert the given HTML string to plain text.
     *
     * @param string $string The HTML string to convert.
     *
     * @return string The plain text.
     */
    public static function htmlToText($string)
    {
        return self::htmlToTextInternal($string, true);
    }

    /**
     * Convert the given HTML string to plain text but keep it on one line.
     *
     * @param string $string The HTML string to convert.
     *
     * @return string The plain text on one line.
     */
    public static function htmlToTextInOneLine($string)
    {
        return self::htmlToTextInternal($string, false);
    }

    /**
     * Shared HTML to text conversion used by htmlToText and htmlToTextInOneLine.
     *
     * The only differences between the two modes are the glue inserted between
     * blocks/list items (a line break vs. a space) and the final post-processing.
     *
     * @param string $string The HTML string to convert.
     * @param bool   $multiLine Whether to keep blocks on separate lines.
     *
     * @return string The converted text.
     */
    private static function htmlToTextInternal($string, $multiLine)
    {
        // Glue separating blocks and list items.
        $glue = $multiLine ? "\n" : ' ';
        // Multi-line mode needs patterns that span newlines; one-line mode does not.
        $pattern = $multiLine ? 's' : 'is';
        // In one-line mode a line break is never kept, so a block separator is
        // always a space; in multi-line mode </p> becomes a line break.
        $paragraphSeparator = $multiLine ? "\n" : ' ';

        $string = preg_replace('/\r?\n/', '', $string);

        // Remove opening <p> tags
        $string = preg_replace('/<p[^>]*>/', '', $string);

        // Replace closing </p> tags with the paragraph separator
        $string = preg_replace('/<\/p\s*>/i', $paragraphSeparator, $string);

        // Replace <br>, <br/>, <br /> and </br> with a space
        $string = preg_replace('/<br\s*\/?>|<\/br\s*>/i', ' ', $string);

        // Decode HTML entities
        $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Replace <a> tags with Markdown link syntax
        $string = preg_replace('/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a\s*>/' . $pattern, '[$2]($1)', $string);

        // Replace <strong> tags with Markdown bold syntax
        $string = preg_replace('/<strong>(.*?)<\/strong\s*>/' . $pattern, '**$1**', $string);

        // Replace <em> tags with Markdown italic syntax
        $string = preg_replace('/<em>(.*?)<\/em\s*>/' . $pattern, '*$1*', $string);

        // Remove spaces between <ul>/<ol> and <li>
        $string = preg_replace('/<ul>\s*<li>/', '<ul><li>', $string);
        $string = preg_replace('/<ol>\s*<li>/', '<ol><li>', $string);

        // Trim content between <li> and </li>
        $string = preg_replace('/<li>\s*(.*?)\s*<\/li>/' . $pattern, '<li>$1</li>', $string);

        // Convert unordered lists
        $string = preg_replace_callback(
            '/<ul>(.*?)<\/ul>/' . $pattern,
            function ($matches) use ($glue) {
                return preg_replace('/<li>(.*?)<\/li>/' . ($glue === "\n" ? 's' : 'is'), '- $1' . $glue, $matches[1]);
            },
            $string
        );

        // Convert ordered lists
        $string = preg_replace_callback(
            '/<ol>(.*?)<\/ol>/' . $pattern,
            function ($matches) use ($glue) {
                $counter = 1;
                return preg_replace_callback(
                    '/<li>(.*?)<\/li>/' . ($glue === "\n" ? 's' : 'is'),
                    function ($matches) use (&$counter, $glue) {
                        return $counter++ . '. ' . $matches[1] . $glue;
                    },
                    $matches[1]
                );
            },
            $string
        );

        // Place a line break after </h1> to </h6>
        if ($multiLine) {
            $string = preg_replace('/<\/h([1-6])>/', "\n</h$1>", $string);
        }

        // Remove all remaining HTML tags
        $string = preg_replace('/<[^>]*>/', '', $string);

        if ($multiLine) {
            // Limit consecutive line breaks to 2
            $string = preg_replace('/(\n{3,})/', "\n\n", $string);
            // Remove all &nbsp replacement chars that are trailing or right in front of the final line break
            $string = preg_replace('/\x{00A0}+(?=\n|$)/u', '', $string);
        } else {
            // Remove trailing non-breaking spaces
            $string = preg_replace('/\x{00A0}+(?=\s|$)/u', '', $string);
            // Collapse consecutive whitespace into one space
            $string = preg_replace('/\s+/u', ' ', $string);
            return trim($string);
        }

        return $string;
    }

    /**
     * Get the closing tag for the given container.
     *
     * @param string $container The container to get the closing tag for.
     *
     * @return string The closing tag.
     */
    public static function getClosingTag($container)
    {
        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $container, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        if ($tag_name) {
            return '</' . $tag_name . '>';
        } else {
            return '';
        }
    }
}

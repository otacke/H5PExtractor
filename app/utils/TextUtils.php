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
        $string = preg_replace('/\r?\n/', '', $string);

        // Remove <p> tags
        $string = preg_replace('/<p[^>]*>/', "", $string);

        // Replace </p> tags with a newline character
        $string = preg_replace('/<\/p[^>]*>/', "\n", $string);

        // Replace &nbsp; and other HTML encoded characters
        $string = html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Replace <a> tags with Markdown link syntax
        $string = preg_replace(
            '/<a[^>]*href="([^"]*)"[^>]*>(.*?)<\/a[^>]*>/',
            '[$2]($1)',
            $string
        );

        // Replace <strong> tags with Markdown bold syntax
        $string = preg_replace('/<strong>(.*?)<\/strong>/', '**$1**', $string);

        // Replace <em> tags with Markdown italic syntax
        $string = preg_replace('/<em>(.*?)<\/em>/', '*$1*', $string);

        // Replace <ul><li> tags with Markdown unordered list syntax
        $string = preg_replace_callback('/<ul>(.*?)<\/ul>/', function($matches) {
            $listItems = preg_replace('/<li>(.*?)<\/li>/', "- $1\n", $matches[1]);
            return $listItems;
        }, $string);

        // Replace <ol><li> tags with Markdown ordered list syntax
        $string = preg_replace_callback('/<ol>(.*?)<\/ol>/', function($matches) {
            $listItems = preg_replace_callback('/<li>(.*?)<\/li>/', function($matches) {
                static $counter = 1;
                return "{$counter}. {$matches[1]}\n";
            }, $matches[1]);
            return $listItems;
        }, $string);

        // Remove all other HTML tags
        $string = preg_replace('/<[^>]*>/', '', $string);

        // Limit consecutive line breaks to 2
        $string = preg_replace('/(\n{3,})/', "\n\n", $string);

        error_log($string);

        return $string;
    }
}

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

        // Remove all other HTML tags
        $string = preg_replace('/<[^>]*>/', '', $string);

        // Limit consecutive line breaks to 2
        $string = preg_replace('/(\n{3,})/', "\n\n", $string);
        return $string;
    }
}

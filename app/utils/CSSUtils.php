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
 * @link     https://todo
 */

namespace H5PExtractor;

require_once __DIR__ . '/' . 'FileUtils.php';

/**
 * Class for handling CSS.
 *
 * @category Utility
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class CSSUtils
{
    /**
     * Replace URLs in the given CSS with base64 encoded strings.
     *
     * @param string $css      The CSS to replace URLs in.
     * @param string $basePath The base path to the CSS file.
     *
     * @return string The CSS with URLs replaced with base64 encoded strings.
     */
    public static function replaceUrlsWithBase64($css, $basePath)
    {
        $pattern = '/url\s*\(\s*[\'"]?\K[^\'")]+/';

        // Replace URLs with base64 encoded strings
        $css = preg_replace_callback(
            $pattern, function ($matches) use ($basePath) {
                return FileUtils::fileToBase64(
                    $basePath . '/' . trim($matches[0], '\'"')
                );
            }, $css
        );

        return $css;
    }

    /**
     * Remove client handling CSS from the given CSS.
     *
     * @param string $css The CSS to remove client handling from.
     *
     * @return string The CSS with client handling removed.
     */
    public static function removeClientHandlingCSS($css)
    {
        // Define the properties and pseudo-elements to remove
        $unwanted_properties = array(
            '/\bcursor\s*:\s*[^;]+;/',
            // Remove :active
        );

        // Remove unwanted properties and pseudo-elements
        $css = preg_replace($unwanted_properties, '', $css);

        // Define the properties and pseudo-elements to remove
        $unwanted_pseudo_elements = array(
            // Remove :hover
            '/\b:hover\b[^{]*\{[^}]*\}|:hover[^{]*\{[^}]*\}/',
            // Remove :active
            '/\b:active\b[^{]*\{[^}]*\}|:active[^{]*\{[^}]*\}/',
            // Remove :focus
            '/\b:focus\b[^{]*\{[^}]*\}|:focus[^{]*\{[^}]*\}/',
            // Remove :visited
            '/\b:visited\b[^{]*\{[^}]*\}|\b:visited\b[^{]*\{[^}]*\}/',
            // Remove :focus-visible
            '/\b:focus-visible\b[^{]*\{[^}]*\}|\b:focus-visible\b[^{]*\{[^}]*\}/'
        );

        // Remove unwanted pseudo-elements
        $css = preg_replace($unwanted_pseudo_elements, '{}', $css);

        // Remove cursor and interactivity from common interaction elements
        $elements = ['input', 'button', 'a', 'p', 'span'];
        foreach ($elements as $element) {
            $css .= '.h5p-content ' . $element .
                '{cursor:default;pointer-events: none;}';
        }

        return $css;
    }
}

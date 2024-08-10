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
 * Class for handling CSS.
 *
 * @category Utility
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class CSSUtils
{
    /**
     * Prettify the given CSS.
     *
     * @param string $css The CSS to prettify.
     *
     * @return string The prettified CSS.
     */
    public static function prettify($css, $compact = false)
    {
        $vendorPath = FileUtils::getVendorPath(__DIR__);
        $autoload = $vendorPath . DIRECTORY_SEPARATOR . 'autoload.php';
        require_once $autoload;

        $parser = new \Sabberworm\CSS\Parser($css);
        $cssDocument = $parser->parse();

        if ($compact) {
            $formatter = \Sabberworm\CSS\OutputFormat::createCompact();
        } else {
            $formatter = \Sabberworm\CSS\OutputFormat::createPretty();
        }
        return $cssDocument->render($formatter);
    }

    /**
     * Simplify the given CSS by removing redundant properties.
     *
     * @param string $css The CSS to simplify.
     * @param array  $properties The font format priorities.
     *
     * @return string The simplified CSS.
     */
    public static function simplifyFonts($css, $priority = null)
    {
        // Define font format priorities
        if (!isset($priority)) {
            $priority = [
                'woff2' => 1,
                'woff' => 2,
                'otf' => 3,
                'ttf' => 4,
                'svg' => 5,
                'eot' => 6
            ];
        }

        // Regular expression to match @font-face blocks
        $pattern = '/@font-face\s*{([^}]+)}/i';

        // Callback function to process each @font-face block
        $callback = function ($matches) use ($priority) {
            $declaration = $matches[1];

            // Extract all parts of the @font-face block
            preg_match_all('/([a-z\-]+)\s*:\s*([^;]+);/i', $declaration, $attrMatches, PREG_SET_ORDER);

            // Capture attributes
            $attributes = [];
            foreach ($attrMatches as $attrMatch) {
                $property = strtolower(trim($attrMatch[1]));
                $value = trim($attrMatch[2]);
                $attributes[$property] = $value;
            }

            // Handle src declarations separately
            preg_match_all(
                '/src\s*:\s*url\(([^)]+)\)\s*(?:format\(["\']([^"\']+)["\']\))?|src\s*:\s*data:[^;]+;/i',
                $declaration,
                $srcMatches,
                PREG_SET_ORDER
            );

            // Initialize variables to keep track of highest priority src
            $highestPrioritySrc = '';
            $highestPriorityFormat = '';
            $highestPriorityValue = PHP_INT_MAX;
            $dataUri = '';

            foreach ($srcMatches as $srcMatch) {
                $url = $srcMatch[1] ?? '';
                $format = isset($srcMatch[2]) ? strtolower($srcMatch[2]) : '';

                if ($url) {
                    $priorityValue = isset($priority[$format]) ? $priority[$format] : PHP_INT_MAX;
                    if ($priorityValue < $highestPriorityValue) {
                        $highestPrioritySrc = $url;
                        $highestPriorityFormat = $format;
                        $highestPriorityValue = $priorityValue;
                    }
                } else {
                    // Preserve data URIs
                    $dataUri = $srcMatch[0];
                }
            }

            // Build the new src declaration with the highest priority format
            $newSrcDeclaration = $dataUri ?: '';
            if ($highestPrioritySrc) {
                $newSrcDeclaration .= ($newSrcDeclaration ? ', ' : '') . "url($highestPrioritySrc)";
                if ($highestPriorityFormat) {
                    $newSrcDeclaration .= " format('$highestPriorityFormat')";
                }
            }

            // Remove existing src declarations and replace with new one
            $declaration = preg_replace(
                '/src\s*:\s*url\([^\)]+\)\s*(?:format\(["\'][^"\']+["\']\))?|src\s*:\s*data:[^;]+;/i',
                '',
                $declaration
            );
            $declaration = trim($declaration);

            // Add the new src declaration and rebuild the @font-face block
            if ($newSrcDeclaration) {
                $attributes['src'] = $newSrcDeclaration;
            }
            $attributeString = '';
            foreach ($attributes as $property => $value) {
                $attributeString .= "$property: $value; ";
            }

            return "@font-face { " . trim($attributeString) . "}";
        };

        // Apply the callback to all @font-face blocks
        return preg_replace_callback($pattern, $callback, $css);
    }

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
            $pattern,
            function ($matches) use ($basePath) {
                $trimmed = trim($matches[0], '\'"');
                if (strpos($trimmed, 'data:') === 0) {
                    return $trimmed; // Already base64 encoded
                }

                return FileUtils::fileToBase64(
                    $basePath . DIRECTORY_SEPARATOR . $trimmed
                );
            },
            $css
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
            // cursor, but not part of a larger word, e.g. in class name
            '/(?<![-\w])\bcursor\s*:\s*[^;}]+;?/',
        );

        // Remove unwanted properties
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
        $elements = ['input', 'button', 'a', 'p', 'span', 'textarea', 'select'];
        foreach ($elements as $element) {
            $css .= '.h5p-content ' . $element .
                '{cursor:default;pointer-events: none;}';
        }

        return $css;
    }
}

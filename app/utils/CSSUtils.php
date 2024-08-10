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
    public static function simplifyFonts($css, $formatPriorities = null)
    {
        // Find all @font-face blocks
        $fontFaceBlocks = [];
        preg_match_all('/@font-face\s*{[^}]*}/', $css, $fontFaceBlocks);

        // Loop over all @font-face blocks
        foreach ($fontFaceBlocks[0] as $fontFaceBlock) {
            // Find all complete src values, not only containing the URL
            $srcValues = [];
            preg_match_all('/src\s*:\s*[^;]+;/', $fontFaceBlock, $srcValues);

            $allSources = [];

            foreach ($srcValues[0] as $srcValue) {
                $urls = [];
                preg_match_all('/url\s*\([^)]+\)/', $srcValue, $urls);

                if (count($urls[0]) === 1) {
                    $allSources[] = $srcValue;
                } else {
                    $parts = explode(',', $srcValue);
                    $allSources[] = $parts[0];
                    for ($i = 1; $i < count($parts); $i++) {
                        $allSources[] = 'src:' . $parts[$i];
                    }
                }
            }

            $bestFormatIndex = 0;
            if (count($allSources) > 1) {
                $bestFormatPriority = 0;

                if ($formatPriorities === null) {
                    $formatPriorities = [
                        'truetype' => 5,
                        'woff2' => 4,
                        'woff' => 3,
                        'embedded-opentype' => 2,
                        'svg' => 1
                    ];
                }

                foreach ($allSources as $index => $source) {
                    $format = null;
                    $formatIndex = null;
                    $formatPriority = 0;

                    foreach ($formatPriorities as $key => $priority) {
                        if (strpos($source, $key) !== false) {
                            $format = $key;
                            $formatIndex = $index;
                            $formatPriority = $priority;
                            break;
                        }
                    }

                    if ($format !== null && $formatPriority > $bestFormatPriority) {
                        $bestFormatIndex = $formatIndex;
                        $bestFormatPriority = $formatPriority;
                    }
                }
            }

            $newSrc = $allSources[$bestFormatIndex];
            if (substr($newSrc, -1) !== ';') {
                $newSrc .= ';';
            }

            // Replace the src value in the @font-face block with $newSrc
            $css = str_replace($srcValues[0][0], $newSrc, $css);

            for ($i = 1; $i < count($srcValues[0]); $i++) {
                $css = str_replace($srcValues[0][$i], '', $css);
            }
        }

        return $css;
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

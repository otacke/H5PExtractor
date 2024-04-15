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

require_once __DIR__ . '/../HtmlGeneratorInterface.php';

/**
 * Class for generating HTML for H5P.AdvancedText-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class HtmlGeneratorAdvancedText_1_1 implements HtmlGeneratorInterface
{
    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array             $params Parameters.
     * @param HtmlGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
    {
        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $html = str_replace('h5pClassName', 'h5p-advanced-text', $html);

        if (isset($contentParams['text'])) {
            $html .= $contentParams['text'];
        }

        $html .= $htmlClosing;

        return $html;
    }
}

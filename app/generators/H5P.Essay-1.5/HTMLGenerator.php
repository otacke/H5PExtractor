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
 * Class for generating HTML for H5P.Essay-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorEssayMajor1Minor5 implements HtmlGeneratorInterface
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
        $html = str_replace('h5pClassName', 'h5p-essay', $html);

        if (isset($contentParams['media']['type'])) {
            $html .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $numberLines = (isset($contentParams['behaviour']['inputFieldSize'])) ?
            $contentParams['behaviour']['inputFieldSize'] :
            10;

        $html .= '<div class="h5p-question-introduction">';
        $html .= '<div>' . ($contentParams['taskDescription'] ?? ''). '</div>';
        $html .= '</div>';

        $html .= '<div class="h5p-question-content">';
        $html .= '<div>';
        $html .= '<textarea disabled' .
            ' class="h5p-essay-input-field-textfield"' .
            ' rows="' . $numberLines . '" ' .
            ' placeholder="' . $contentParams['placeholderText'] . '"' .
            '>';
        $html .= '</textarea>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

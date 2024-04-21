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
 * Class for generating HTML for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorBlanksMajor1Minor14 implements HtmlGeneratorInterface
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
        $html = str_replace('h5pClassName', 'h5p-blanks', $html);

        if (isset($contentParams['media']['type'])) {
            $html .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $html .= '<div class="h5p-question-introduction">';
        $html .= '<div>' . $contentParams['text'] . '</div>';
        $html .= '</div>';

        if ($contentParams['behaviour']['separateLines']) {
            $html .= '<div class="h5p-question-content h5p-separate-lines">';
        } else {
            $html .= '<div class="h5p-question-content">';
        }

        // loop through $contentParams['questions']
        $questionCount = count($contentParams['questions']);
        for ($index = 0; $index < $questionCount; $index++) {
            $questionData = $contentParams['questions'][$index];
            $blankWidth = $contentParams['behaviour']['separateLines'] ?
                '100%' : '56px';

            $questionData = preg_replace(
                '/\*([^*]+)\*/',
                '<span class="h5p-input-wrapper">' .
                    '<input class="h5p-text-input" style="width: ' .
                        $blankWidth . ';"/>' .
                    '</span>',
                $questionData
            );

            $html .= '<div role="group">';
            $html .= $questionData;
            $html .= '</div>';
        }

        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

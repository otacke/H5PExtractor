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
 * Class for generating HTML for H5P.MultipleChoice-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorMultiChoiceMajor1Minor16 implements HtmlGeneratorInterface
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
        $html = str_replace('h5pClassName', 'h5p-multichoice', $html);

        if ($contentParams['behaviour']['randomAnswers']) {
            shuffle($contentParams['answers']);
        }

        if (isset($contentParams['media']['type'])) {
            $html .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $html .= '<div class="h5p-question-introduction">';
        $html .= '<div>' . ($contentParams['question'] ?? ''). '</div>';
        $html .= '</div>';

        $numCorrect = count(
            array_filter(
                $contentParams['answers'],
                function ($answer) {
                    return $answer['correct'];
                }
            )
        );

        $mode = ($numCorrect === 1) ? 'h5p-radio' : 'h5p-check';
        if ($contentParams['behaviour']['type'] === 'single') {
            $mode = 'h5p-radio';
        } elseif ($contentParams['behaviour']['type'] === 'multi') {
            $mode = 'h5p-check';
        }

        $html .= '<div class="h5p-question-content ' . $mode . '">';

        $role = $mode === 'h5p-radio' ? 'radiogroup' : 'group';
        $html .= '<ul class="h5p-answers" role="' . $role . '">';

        $role = $mode === 'h5p-radio' ? 'radio' : 'checkbox';
        $answerCount = count($contentParams['answers']);
        for ($answerIndex = 0; $answerIndex < $answerCount; $answerIndex++) {
            $html .= '<li class="h5p-answer" role="' . $role . '">';
            $html .= '<div class="h5p-alternative-container">';
            $html .= '<span class="h5p-alternative-inner">';
            $answerData = $contentParams['answers'][$answerIndex];
            $html .= ($answerData['text'] ?? '');
            $html .= '</span>';
            // TODO: Tips
            $html .= '</div>';
            $html .= '</li>';
        }

        $html .= '<ul>';
        $html .= '</div>';

        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

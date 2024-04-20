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

require_once __DIR__ . '/../HtmlGeneratorInterface.php';
require_once __DIR__ . '/Utils.php';

/**
 * Class for generating HTML for H5P.DragText-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorDragText_1_10 implements HtmlGeneratorInterface
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
        $html = str_replace('h5pClassName', 'h5p-drag-text', $html);

        if (isset($contentParams['media']['type'])) {
            $html .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $draggables = [];

        $textParts = [];
        $textFieldHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $contentParams['textField'] ?? ''
        );
        $segments = UtilsDragText_1_10::parseText($textFieldHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                $textParts[] = '<span>' . $segment . '</span>';
                continue;
            }

            $lexed = UtilsDragText_1_10::lex($segment);
            $draggables[] = '<div ' .
                'role="button" aria-grabbed="false" ' .
                'class="ui-draggable ui-draggable-handle" ' .
                'style="position: relative; left: 0; top: 0;"' .
                '>' .
                '<span>' . $lexed['text'] . '</span>' .
                '</div>';

            $dropzone = '<div class="h5p-drag-dropzone-container">' .
                '<div ' .
                'aria-dropeffect="none" class="ui-droppable" style="width: 100px">' .
                '</div>' .
                '</div>';

            $textParts[] = $dropzone;
        }

        $distractorsHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $contentParams['distractors'] ?? ''
        );
        $segments = UtilsDragText_1_10::parseText($distractorsHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                continue;
            }

            $lexed = UtilsDragText_1_10::lex($segment);
            $draggables[] = '<div ' .
              'role="button" aria-grabbed="false" ' .
              'class="ui-draggable ui-draggable-handle" ' .
              'style="position: relative; left: 0; top: 0;"' .
              '>' .
              '<span>' . $lexed['text'] . '</span>' .
              '</div>';
        }

        $html .= '<div class="h5p-question-introduction">';
        $html .= $contentParams['taskDescription'] ?? '';
        $html .= '</div>';

        $html .= '<div class="h5p-question-content">';
        $html .= '<div class="h5p-drag-inner">';
        $html .= '<div class="h5p-drag-task">';

        $html .= '<div class="h5p-drag-droppable-words" style="margin-right: 0;">';
        $html .= implode('', $textParts);
        $html .= '</div>';

        $html .= '<div class="h5p-drag-draggables-container">';
        shuffle($draggables);
        $html .= implode('', $draggables);
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

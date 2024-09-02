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
 * Class for generating HTML for H5P.DragText-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorDragTextMajor1Minor10 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params     Parameters.
     * @param int   $contentId  Content ID.
     * @param array $extras     Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-question h5p-drag-text', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $draggables = [];

        $textParts = [];
        $textFieldHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $this->params['textField'] ?? ''
        );
        $segments = UtilsDragTextMajor1Minor10::parseText($textFieldHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                // Span in original, but may conflict with Pressbooks. Does not hurt though.
                $textParts[] = '<div style="display:inline;">' . $segment . '</div>';
                continue;
            }

            $lexed = UtilsDragTextMajor1Minor10::lex($segment);
            $draggables[] = '<div ' .
                'role="button" aria-grabbed="false" ' .
                'class="ui-draggable ui-draggable-handle" ' .
                'style="position: relative; left: 0; top: 0;"' .
                '>' .
                // Span in original, but may conflict with Pressbooks. Does not hurt though.
                '<div style="display: inline;">' . $lexed['text'] . '</div>' .
                '</div>';

            $dropzone = '<div class="h5p-drag-dropzone-container">' .
                '<div ' .
                    'aria-dropeffect="none" class="ui-droppable" ' .
                    'style="width: 100px">' .
                '</div>' .
                '</div>';

            $textParts[] = $dropzone;
        }

        $distractorsHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $this->params['distractors'] ?? ''
        );
        $segments = UtilsDragTextMajor1Minor10::parseText($distractorsHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                continue;
            }

            $lexed = UtilsDragTextMajor1Minor10::lex($segment);
            $draggables[] = '<div ' .
              'role="button" aria-grabbed="false" ' .
              'class="ui-draggable ui-draggable-handle" ' .
              'style="position: relative; left: 0; top: 0;"' .
              '>' .
              // Span in original, but may conflict with Pressbooks. Does not hurt though.
              '<div style="display:inline;">' . $lexed['text'] . '</div>' .
              // TODO: Tips
              '</div>';
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= $this->params['taskDescription'] ?? '';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-drag-inner">';
        $container .= '<div class="h5p-drag-task">';

        $container .= '<div class="h5p-drag-droppable-words" style="margin-right: 0;">';
        $container .= implode('', $textParts);
        $container .= '</div>';

        $container .= '<div class="h5p-drag-draggables-container">';
        shuffle($draggables);
        $container .= implode('', $draggables);
        $container .= '</div>';

        $container .= '</div>';
        $container .= '</div>';
        $container .= '</div>';

        $container .= $htmlClosing;
    }
}

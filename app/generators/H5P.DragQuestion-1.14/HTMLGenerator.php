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
 * Class for generating HTML for H5P.DragQuestion-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorDragQuestionMajor1Minor14 extends Generator implements GeneratorInterface
{

    /**
     * Constructor.
     *
     * @param HTMLGeneratorMain $main The main HTML generator.
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
        $task = $this->params['question']['task'] ?? [];

        $htmlClosing = TextUtils::getClosingTag($container);

        /*
         * In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace(
            'h5pClassName',
            'h5p-question h5p-dragquestion',
            $container
        );

        if ($this->params['behaviour']['showTitle'] ?? false) {
            $container .= '<div class="h5p-question-introduction">';
            $container .= '<p class="h5p-dragquestion-introduction">' .
                ($this->extras['metadata']['title'] ?? 'Drag and Drop') .
                '</p>';
            $container .= '</div>';
        }

        $container .= '<div class="h5p-question-content">';

        $fontSize = 16 * (
            $this->main->renderWidth /
            $this->params['question']['settings']['size']['width']
        );

        $styleProps = [
            'width: 100%',
            'font-size: ' . $fontSize . 'px'
        ];

        if (isset($this->params['question']['settings']['background']['path'])) {
            $imagePath = $this->main->h5pFileHandler->getBaseDirectory() . DIRECTORY_SEPARATOR .
                $this->main->h5pFileHandler->getFilesDirectory() . DIRECTORY_SEPARATOR .
                'content' . DIRECTORY_SEPARATOR .
                $this->params['question']['settings']['background']['path'];

            $styleProps[] = 'background-image: url(' .
                FileUtils::fileToBase64($imagePath) .
                ')';

            list($width, $height) = getimagesize($imagePath);
            $styleProps[] = 'aspect-ratio: ' . $width . '/' . $height;
        } else {
            $styleProps[] = 'aspect-ratio: 2';
        }

        $container .=
            '<div class="h5p-inner" style="' . implode('; ', $styleProps)  . '">';

        // Draggables
        foreach ($task['elements'] ?? [] as $draggable) {
            $innerContainer = '<div';

            $innerContainerStyleProps = [
                'left: ' . ($draggable['x'] ?? 0) . '%',
                'top: ' . ($draggable['y'] ?? 0) . '%',
                'width: ' . ($draggable['width'] ?? 10) . 'em',
                'height: ' . ($draggable['height'] ?? 10) . 'em',
                'background-image: none;'
            ];

            if (count($draggable['dropZones'] ?? []) === 0) {
                $innerContainer .= ' class="h5p-static h5pClassName"';
                $innerContainerStyleProps[] =
                    'background-color: rgb(255, 255, 255)';
            } else {
                $innerContainer .=
                    ' class="h5p-draggable ui-draggable' .
                    ' ui-draggable-handle h5pClassName"';
                $innerContainerStyleProps[] =
                    'border-color: rgba(198, 198, 198, ' . $draggable['backgroundOpacity'] . ')';
                $innerContainerStyleProps[] =
                    'box-shadow: rgb(0 0 0 / ' . 20 * $draggable['backgroundOpacity'] . '%) 0px 0px 2.9729px 0px';
                $innerContainerStyleProps[] =
                    'background-color: rgba(221, 221, 221, ' . $draggable['backgroundOpacity'] . ')';
            }

            $innerContainer .=
                ' style="' . implode('; ', $innerContainerStyleProps) . '">';

            $this->main->newRunnable(
                $draggable['type'] ?? [],
                1,
                $innerContainer
            );

            $container .= $innerContainer;
        }

        // Dropzones
        foreach ($task['dropZones'] ?? [] as $dropzone) {
            if ($dropzone['showLabel']) {
                $labelDivStyleProps = [
                    'background-color: rgba(221, 221, 221, ' .
                    $dropzone['backgroundOpacity'] .
                    ')',
                    'background-image: none'
                ];

                $labelDiv = '<div' .
                    ' class="h5p-label"' .
                    ' style="' . implode('; ', $labelDivStyleProps) . '"' .
                    '>' . $dropzone['label'] . '</div>';
            } else {
                $labelDiv = null;
            }

            $h5pInnerDivStyleProps = [
                'background-color: rgba(245, 245, 245, ' .
                    $dropzone['backgroundOpacity'] .
                    ')',
                'background-image: none'
            ];

            $h5pInnerDiv = '<div' .
                ' class="h5p-inner ui-droppable"' .
                ' style="' . implode('; ', $h5pInnerDivStyleProps) . '"' .
                '></div>';

            $dropZoneDivStyleProps = [
                'left: ' . ($dropzone['x'] ?? 0) . '%',
                'top: ' . ($dropzone['y'] ?? 0) . '%',
                'width: ' . ($dropzone['width'] ?? 10) . 'em',
                'height: ' . ($dropzone['height'] ?? 10) . 'em'
            ];

            $dropZoneDiv = '<div' .
                ' class="h5p-dropzone"' .
                ' role="button"' .
                ' style="' . implode('; ', $dropZoneDivStyleProps) . '"' .
                '>' . ($labelDiv ?? '') . $h5pInnerDiv . '</div>';

            $container .= $dropZoneDiv;
        }

        $container .= '</div>'; // h5p-inner

        $container .= '</div>'; // h5p-question-content

        $container .= $htmlClosing;
    }
}

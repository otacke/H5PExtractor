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

        $renderWidth = $this->getRenderWidth();

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

        $container = str_replace(
            'style=""',
            'style="width: ' . $renderWidth . 'px"',
            $container
        );

        if ($this->params['behaviour']['showTitle'] ?? false) {
            $container .= '<div class="h5p-question-introduction">';
            $container .= '<p class="h5p-dragquestion-introduction">' .
                ($this->extras['metadata']['title'] ?? 'Drag and Drop') .
                '</p>';
            $container .= '</div>';
        }

        // `position: relative` for older render engines that will not position draggables correctly otherwise
        $container .= '<div class="h5p-question-content" style="position: relative;">';

        $fontSize = 16 * (
            $renderWidth /
            $this->params['question']['settings']['size']['width']
        );

        $styleProps = [
            'font-size: ' . $fontSize . 'px',
            'width: ' . $renderWidth . 'px',
        ];

        $imageNaturalWidth = 0;
        $imageNaturalHeight = 0;
        $imageRenderHeight = 0;
        if (isset($this->params['question']['settings']['background']['path'])) {
            $imagePath = $this->params['question']['settings']['background']['path'];

            $styleProps[] = 'background-image: url(' . $this->buildFileSource($imagePath) . ')';
            $styleProps[] = 'background-size: contain';
            $styleProps[] = 'background-position: left top';

            $fullImagePath = $this->main->h5pFileHandler->getBaseDirectory() . DIRECTORY_SEPARATOR .
                $this->main->h5pFileHandler->getFilesDirectory() . DIRECTORY_SEPARATOR .
                'content' . DIRECTORY_SEPARATOR . $imagePath;

            list($imageNaturalWidth, $imageNaturalHeight) = getimagesize($fullImagePath);
        }
        if (($imageNaturalWidth ?? 0) === 0 || ($imageNaturalHeight ?? 0) === 0) {
            $imageNaturalWidth = 2;
            $imageNaturalHeight = 1;
        }

        $imageRenderHeight = $renderWidth * $imageNaturalHeight / $imageNaturalWidth;
        $styleProps[] = 'aspect-ratio: ' . $imageNaturalWidth . '/' . $imageNaturalHeight;
        $styleProps[] = 'height: ' . $imageRenderHeight . 'px';

        $container .=
            '<div class="h5p-inner" style="' . implode('; ', $styleProps)  . '">';

        // Draggables
        foreach ($task['elements'] ?? [] as $draggable) {
            $innerContainer = '<div';

            $innerContainerStyleProps = [
                'left: ' . ($draggable['x'] ?? 0) / 100 * $renderWidth . 'px',
                'top: ' . ($draggable['y'] ?? 0) / 100 * $imageRenderHeight . 'px',
                'width: ' . ($draggable['width'] ?? 10) * $fontSize . 'px',
                'height: ' . ($draggable['height'] ?? 10) * $fontSize . 'px',
                'background-image: none',
                'display: flex',
                'flex-direction: column',
                'align-items: center',
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
                'background-color: rgb(245, 245, 245)', // Fallback for older render engines without opacity support
                'background-color: rgb(245, 245, 245, ' .
                    $dropzone['backgroundOpacity'] .
                ')',
                'background-image: none',
                'height: 100%'
            ];

            $h5pInnerDiv = '<div' .
                ' class="h5p-inner ui-droppable"' .
                ' style="' . implode('; ', $h5pInnerDivStyleProps) . '"' .
                '></div>';

            $dropZoneDivStyleProps = [
                'left: ' . ($dropzone['x'] ?? 0) / 100 * $renderWidth . 'px',
                'top: ' . ($dropzone['y'] ?? 0) / 100 * $imageRenderHeight . 'px',
                'width: ' . ($dropzone['width'] ?? 10) * $fontSize . 'px',
                'height: ' . ($dropzone['height'] ?? 10) * $fontSize . 'px'
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

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
 * Class for generating HTML for H5P.StructureStrip-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorStructureStripMajor1Minor0 extends Generator implements GeneratorInterface
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
     * @param array             $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-question h5p-structure-strip', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .=
            '<div class="h5p-structure-strip-task-description">' .
                $this->params['taskDescription'] .
            '</div>';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-structure-strip-text-strips-container">';

        for ($i = 0; $i < count($this->params['sections']); $i++) {
            $container .= '<div class="h5p-structure-strip-text-strip">';

            $container .=
                '<div ' .
                    'class="h5p-structure-strip-text-strip-description-container" ' .
                    'style="' .
                        'background-color: ' . $this->params['sections'][$i]['colorBackground'] . '; ' .
                        'color: ' . $this->params['sections'][$i]['colorText'] . ';' .
                        'flex-basis: 100%;' . // Enforce column layout
                    '"' .
                '>';

            $container .= '<div class="h5p-structure-strip-text-strip-description-wrapper">';
            $container .= '<div class="h5p-structure-strip-text-strip-description-title">';
            $container .=
                '<span class="h5p-structure-strip-text-strip-description-title-text">' .
                    $this->params['sections'][$i]['title'] .
                '</span>';

            if (isset($this->params['sections'][$i]['description'])) {
                $container .=
                '<div style="font-weight: normal; font-size: 1em;">' .
                    $this->params['sections'][$i]['description'] .
                '</div>';
            }

            $container .= '</div>'; // Closing h5p-structure-strip-text-strip-description-title
            $container .= '</div>'; // Closing h5p-structure-strip-text-strip-description-wrapper

            $container .= '</div>'; // Closing h5p-structure-strip-text-strip-description-container

            $container .= '<div class="h5p-structure-strip-text-strip-input-container">';
            $container .= '<textarea class="h5p-structure-strip-text-strip-input-field" rows="10">';
            $container .= '</textarea>';
            $container .= '</div>'; // Closing h5p-structure-strip-text-strip-input-container

            $container .= '</div>'; // Closing h5p-structure-strip-text-strip
        }
        $container .= '</div>'; // Closing h5p-structure-strip-text-strips-container
        $container .= '</div>'; // Closing h5p-question-content

        $container .= $htmlClosing;
    }
}

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
 * Class for generating HTML for H5P.SortParagraphs-0.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorSortParagraphsMajor0Minor11 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-question h5p-sort-paragraphs', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . $this->params['taskDescription'] . '</div>';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-sort-paragraphs-content">';
        $container .= '<div class="h5p-sort-paragraphs-list">';

        shuffle($this->params['paragraphs']);

        $numberOfParagraphs = count($this->params['paragraphs']);

        for ($i = 0; $i < $numberOfParagraphs; $i++) {
            $container .= '<div class="h5p-sort-paragraphs-paragraph">';
            $container .= '<div class="h5p-sort-paragraphs-paragraph-container">';
            $container .= $this->params['paragraphs'][$i];
            $container .= '</div>';
            $container .= '</div>';

            if ($i < $numberOfParagraphs - 1) {
                $container .= '<div class="h5p-sort-paragraphs-separator"></div>';
            }
        }

        $container .= '</div>'; // Closing h5p-sort-paragraphs-list
        $container .= '</div>'; // Closing h5p-sort-paragraphs-content
        $container .= '</div>'; // Closing h5p-question-content

        $container .= $htmlClosing;
    }
}

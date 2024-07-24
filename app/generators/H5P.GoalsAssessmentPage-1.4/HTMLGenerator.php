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
 * Class for generating HTML for H5P.GoalsAssessmentPage-1.4.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorGoalsAssessmentPageMajor1Minor4 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-goals-page', $container);

        $container .= '<div class="page-header">';
        $container .=
            '<div class="page-title">' .
                ($this->extras['metadata']['title'] ?? '') .
            '</div>';
        $container .= '</div>'; // Closing page-header

        $container .= '<div class="h5p-goals-assessment-description">';
        $container .= $this->params['description'];
        $container .= '</div>';

        $container .=
            '<div class="legend">' .
                '<span class="legend-header">' . $this->params['legendHeader'] . '</span>' .
                '<ul class="ratings">' .
                    '<li class="rating low">' . $this->params['lowRating'] . '</li>' .
                    '<li class="rating mid">' . $this->params['midRating'] . '</li>' .
                    '<li class="rating high">' . $this->params['highRating'] . '</li>' .
                '</ul>' .
            '</div>';

        $container .= '<div class="goals-assessment-view">';
        $container .=   '<div class="goals-header">';
        $container .=     '<span class="goal-name-header">' . $this->params['goalHeader'] . '</span>';
        $container .=     '<span class="rating-header">' . $this->params['ratingHeader'] . '</span>';
        $container .=   '</div>'; // Closing goals-header
        $container .=   '<div class="rating-header">';
        $container .=   '</div>'; // Closing rating-header

        if (isset($this->params['helpText']) && $this->params['helpText'] !== '') {
            $container .= '<div class="page-header">';
            $container .= '<div class="page-title">' . "\u{2139}\u{fe0f}" . '</div>';
            $container .= '</div>';
            $container .= '<div>' . $this->params['helpText'] . '</div>';
        }

        $container .= $htmlClosing;
    }
}

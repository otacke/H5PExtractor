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
 * Class for generating HTML for H5P.Summary-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorSummaryMajor1Minor10 extends Generator implements GeneratorInterface
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-question h5p-summary', $container);

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-summary-content">';

        $container .= '<div class="summary-evaluation">';
        $container .= '<div class="summary-evaluation-content">';
        $container .= $this->params['intro'];
        $container .= '</div>';
        $container .= '</div>';

        for ($i = 0; $i < count($this->params['summaries']); $i++) {
            $container .= $this->renderOptions([
                'index' => $i,
                'total' => count($this->params['summaries']),
                'choices' => $this->params['summaries'][$i],
            ]);
        }

        $container .= '</div>'; // Closing h5p-summary-content
        $container .= '</div>'; // Closing h5p-question-content

        $container .= $htmlClosing; // container
    }

    /**
     * Render a single set.
     *
     * @param array $params Parameters.
     *
     * @return string HTML for the set.
     */
    private function renderOptions($params)
    {
        $marginBottomStyle = $params['index'] !== $params['total'] - 1 ?
            ' margin-bottom: 2rem;' : '';

        $options =
            '<div ' .
                'class="summary-options"' .
                'style="' . $marginBottomStyle . '"' .
            '>';
        $options .= '<ul class="h5p-panel" style="display: block;">';

        for ($i = 0; $i < count($params['choices']['summary'] ?? []); $i++) {
            $options .= '<li class="summary-claim-unclicked">';
            $options .= $params['choices']['summary'][$i] ?? '';
            $options .= '</li>';
        }

        $options .= '</ul>';
        $options .= '</div>'; // Closing summary-options

        return $options;
    }
}

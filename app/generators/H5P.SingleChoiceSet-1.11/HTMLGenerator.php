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
 * Class for generating HTML for H5P.SingleChoiceSet-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorSingleChoiceSetMajor1Minor11 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-question h5p-single-choice-set', $container);

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-sc-set-wrapper initialized navigatable">';

        for ($i = 0; $i < count($this->params['choices']); $i++) {
            // Sanitization
            if (!isset($this->params['choices'][$i]['question'])) {
                $this->params['choices'][$i]['question'] = '';
            }

            if (!isset($this->params['choices'][$i]['answers'])) {
                $this->params['choices'][$i]['answers'] = [];
            }

            shuffle($this->params['choices'][$i]['answers']);
        }

        for ($i = 0; $i < count($this->params['choices']); $i++) {
            $container .= $this->renderSet([
                'index' => $i,
                'total' => count($this->params['choices']),
                'choices' => $this->params['choices'][$i],
            ]);
        }

        // Closing h5p-sc-set-wrapper
        $container .= '</div>';
        // Closing h5p-question-content
        $container .= '</div>';

        $container .= $htmlClosing; // container
    }

    /**
     * Render a single set.
     *
     * @param array $params Parameters.
     *
     * @return string HTML for the set.
     */
    private function renderSet($params)
    {
        $set  = '<div class="h5p-sc-set">';
        $set .= '<div class="h5p-sc-set-slide h5p-sc h5p-sc-current-slide" style="position: inherit;">';
        $set .= '<div class="h5p-sc-question">' . $params['choices']['question'] . '</div>';

        $set .= '<ul class="h5p-sc-alternatives">';
        foreach ($params['choices']['answers'] as $alternative) {
            $set .= '<li class="h5p-sc-alternative"><div class="h5p-sc-label">' . $alternative . '</div></li>';
        }
        $set .= '</ul>';

        // Closing h5p-sc-set-slide
        $set .= '</div>';
        // Closing h5p-sc-set
        $set .= '</div>';

        return $set;
    }
}

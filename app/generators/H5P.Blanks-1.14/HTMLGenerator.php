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
 * Class for generating HTML for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorBlanksMajor1Minor14 extends Generator implements GeneratorInterface
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
    public function attach($container)
    {
        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $container, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-blanks', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . $this->params['text'] . '</div>';
        $container .= '</div>';

        if ($this->params['behaviour']['separateLines']) {
            $container .= '<div class="h5p-question-content h5p-separate-lines">';
        } else {
            $container .= '<div class="h5p-question-content">';
        }

        // loop through $this->params['questions']
        $questionCount = count($this->params['questions']);
        for ($index = 0; $index < $questionCount; $index++) {
            $questionData = $this->params['questions'][$index];
            $blankWidth = $this->params['behaviour']['separateLines'] ?
                '100%' : '56px';

            $questionData = preg_replace(
                '/\*([^*]+)\*/',
                '<span class="h5p-input-wrapper">' .
                    '<input class="h5p-text-input" style="width: ' .
                        $blankWidth . ';"/>' .
                    '</span>',
                $questionData
            );

            $container .= '<div role="group">';
            $container .= $questionData;
            $container .= '</div>';
        }

        $container .= '</div>';

        $container .= $htmlClosing;

        return $container;
    }
}

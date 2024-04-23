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
 * Class for generating HTML for H5P.TrueFalse-1.8.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorTrueFalseMajor1Minor8 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-true-false', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . $this->params['question'] . '</div>';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-true-false-answers">';

        $container .= '<div class="h5p-true-false-answer">';
        $container .= $this->params['l10n']['trueText'];
        $container .= '</div>';

        $container .= '<div class="h5p-true-false-answer">';
        $container .= $this->params['l10n']['falseText'];
        $container .= '</div>';

        $container .= '</div>';
        $container .= '</div>';

        $container .= $htmlClosing;

        return $container;
    }
}

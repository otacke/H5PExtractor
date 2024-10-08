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
 * Class for generating HTML for H5P.Essay-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorEssayMajor1Minor5 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-question h5p-essay', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $numberLines = (isset($this->params['behaviour']['inputFieldSize'])) ?
            $this->params['behaviour']['inputFieldSize'] :
            10;

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . ($this->params['taskDescription'] ?? ''). '</div>';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content">';
        $container .= '<div>';
        $container .= '<textarea disabled' .
            ' class="h5p-essay-input-field-textfield"' .
            ' rows="' . $numberLines . '" ' .
            ' placeholder="' . ($this->params['placeholderText'] ?? '') . '"' .
            '>';
        $container .= '</textarea>';
        $container .= '</div>';
        $container .= '</div>';

        $container .= $htmlClosing;
    }
}

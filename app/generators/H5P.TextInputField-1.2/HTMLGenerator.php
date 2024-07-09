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
 * Class for generating HTML for H5P.TextInputField-1.2.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorTextInputFieldMajor1Minor2 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-text-input-field',  $container);

        $requiredClass = $this->params['requiredField'] ? ' required' : '';
        $container .= '<div class="h5p-text-input-field-label' . $requiredClass . '">';
        $container .= $this->params['taskDescription'];
        $container .= '</div>'; // Closing h5p-text-input-field-label

        $container .=
            '<textarea ' .
                'class="h5p-text-input-field-textfield" ' .
                'rows="' . $this->params['inputFieldSize'] . '" ' .
                'placeholder="' . $this->params['placeholderText'] . '">' .
            '</textarea>';

        $container .= $htmlClosing;
    }
}

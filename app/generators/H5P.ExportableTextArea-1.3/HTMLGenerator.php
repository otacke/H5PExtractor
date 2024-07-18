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
 * Class for generating HTML for H5P.ExportableTextArea-1.3.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorExportableTextAreaMajor1Minor3 extends Generator implements GeneratorInterface
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-eta', $container);

        // Content type does not use preloaded CSS (WTF? ooold stuff), so we need to add it here
        $container .=
            '<style>' .
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'eta.css') .
            '</style>';

        $container .= '
            <div class="h5p-eta-label">' .
                ($this->params['label'] ?? '') .
            '</div>';

        $container .=
            '<textarea class="h5p-eta-input"></textarea>';

        $container .= $htmlClosing;
    }
}

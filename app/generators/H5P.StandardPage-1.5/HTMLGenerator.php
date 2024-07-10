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
 * Class for generating HTML for H5P.StandardPage-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorStandardPageMajor1Minor5 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-standard-page', $container);

        $container .= '<div class="page-header">';
        $container .=
            '<div class="page-title">' .
                ($this->extras['metadata']['title'] ?? '') .
            '</div>';
        $container .= '</div>'; // Closing page-header

        for ($i = 0; $i < count($this->params['elementList']); $i++) {
            $innerContainer = '<div class="h5p-standard-page-element h5pClassName" style="">';
            $libraryContent = $this->params['elementList'][$i];
            $this->main->newRunnable(
                [
                    'library' => $libraryContent['library'],
                    'params' => $libraryContent['params'],
                ],
                1,
                $innerContainer,
                false,
                [
                    'metadata' => $libraryContent['metadata'],
                ]
            );
            $container .= $innerContainer;
        }

        $container .= $htmlClosing;
    }
}

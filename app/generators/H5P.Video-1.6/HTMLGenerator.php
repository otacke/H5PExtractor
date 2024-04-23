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
 * Class for generating HTML for H5P.Video-1.6.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorVideoMajor1Minor6 extends Generator implements GeneratorInterface
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
        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $container, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        if ($container === '') {
            $htmlClosing = '';
        } else {
            $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';
        }

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-video', $container);

        $imagePath = __DIR__ . '/../../assets/placeholder-video.svg';

        $container .= '<img' .
            ' src="' . FileUtils::fileToBase64($imagePath) . '"' .
            ' style="width: 100%;"' .
            '>';

        $container .= $htmlClosing;
    }
}

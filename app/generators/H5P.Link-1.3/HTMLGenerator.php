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
 * Class for generating HTML for H5P.Link-1.3.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorLinkMajor1Minor3 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param HTMLGeneratorMain $main The main HTML generator.
     */
    public function __construct($params, $contentId, $extras)
    {
        include_once __DIR__ . '/Utils.php';

        UtilsLinkMajor1Minor3::sanitizeParams($params);

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
        $container = str_replace('h5pClassName', 'h5p-link', $container);

        $url = UtilsLinkMajor1Minor3::getUrl($this->params['linkWidget']);
        error_log($url);
        $sanitizedUrl = UtilsLinkMajor1Minor3::sanitizeUrlProtocol($url);
        error_log($sanitizedUrl);

        $container .= '<a href="' . $sanitizedUrl . '" target="_blank">' .
            $this->params['title'] .
            '</a>';

        $container .= $htmlClosing;
    }
}

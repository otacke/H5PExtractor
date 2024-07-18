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
class PlainTextGeneratorLinkMajor1Minor3 extends Generator implements GeneratorInterface
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
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

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
        if (isset($this->params['text'])) {
            $container .= TextUtils::htmlToText($this->params['text']);
        }

        $url = UtilsLinkMajor1Minor3::getUrl($this->params['linkWidget']);
        $sanitizedUrl = UtilsLinkMajor1Minor3::sanitizeUrlProtocol($url);

        $container .= '[' . $this->params['title'] . ']' .
            '(' . $sanitizedUrl . ')';

        $container = trim($container);
    }
}

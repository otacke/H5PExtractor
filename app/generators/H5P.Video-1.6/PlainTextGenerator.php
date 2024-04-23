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
 * Class for generating plain text for H5P.Video-1.6.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorVideoMajor1Minor6 extends Generator implements GeneratorInterface
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
     * Create the plain text for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The plain text for the H5P content type.
     */
    public function attach($container)
    {
        $title = '';
        if (!empty($this->extras['metadata']['a11yTitle'])) {
            $title .= ': ' . $this->extras['metadata']['a11yTitle'];
        } elseif (!empty($this->extras['metadata']['title'])) {
            $title .= ': ' . $this->extras['metadata']['title'];
        }

        $container .= 'Video' . $title . "\n\n";

        return $container;
    }
}

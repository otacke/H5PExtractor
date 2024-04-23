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
class PlainTextGeneratorVideoMajor1Minor6 extends Generator implements PlainTextGeneratorInterface
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
     * @param array                  $params Parameters.
     *
     * @return string The plain text for the H5P content type.
     */
    public function get($params)
    {
        $contentParams = $params['params'];
        $metadata = $params['metadata'] ?? [];

        $text = $params['container'];

        $title = '';
        if (!empty($metadata['a11yTitle'])) {
            $title .= ': ' . $metadata['a11yTitle'];
        } elseif (!empty($metadata['title'])) {
            $title .= ': ' . $metadata['title'];
        }

        $text .= 'Video' . $title . "\n\n";

        return $text;
    }
}

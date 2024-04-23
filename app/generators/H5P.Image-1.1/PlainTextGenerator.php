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
 * Class for generating plain text for H5P.Image-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorImageMajor1Minor1 implements PlainTextGeneratorInterface
{
    /**
     * Constructor.
     *
     * @param PlainTextGeneratorMain $main The main plain text generator.
     */
    public function __construct(PlainTextGeneratorMain $main)
    {
        $this->main = $main;
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

        if (!isset($contentParams['file']['path'])) {
            return '';
        }

        $title = '';
        if (isset($contentParams) && !empty($contentParams['alt'])) {
            $title = $contentParams['alt'];
        } elseif (!empty($metadata['a11yTitle'])) {
            $title = $metadata['a11yTitle'];
        } elseif (!empty($metadata['title'])) {
            $title = $metadata['title'];
        }

        if ($title !== '') {
            $text = '![' . $title . ']' . "\n\n";
        }
        return $text;
    }
}

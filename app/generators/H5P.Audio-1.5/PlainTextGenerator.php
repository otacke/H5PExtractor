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
 * Class for generating plain text for H5P.Audio-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorAudioMajor1Minor5 implements PlainTextGeneratorInterface
{
    private $main;

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

        $title = '';
        if (!empty($metadata['a11yTitle'])) {
            $title .= ': ' . $metadata['a11yTitle'];
        } elseif (!empty($metadata['title'])) {
            $title .= ': ' . $metadata['title'];
        }

        $text .= 'Audio' . $title . "\n\n";

        return $text;
    }
}

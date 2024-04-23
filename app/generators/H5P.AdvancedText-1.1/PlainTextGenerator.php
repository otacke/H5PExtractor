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
 * Class for generating HTML for H5P.AdvancedText-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorAdvancedTextMajor1Minor1 implements PlainTextGeneratorInterface
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
     * Create the HTML for the given H5P content type.
     *
     * @param array                  $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params)
    {
        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['text'])) {
            $text .= TextUtils::htmlToText($contentParams['text']);
        }

        return $text;
    }
}

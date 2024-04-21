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

require_once __DIR__ . '/../PlainTextGeneratorInterface.php';
require_once __DIR__ . '/../../utils/TextUtils.php';

/**
 * Class for generating HTML for H5P.Video-1.6.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorVideo_1_6 implements PlainTextGeneratorInterface
{
    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array                  $params Parameters.
     * @param PlainTextGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
    {
        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['text'])) {
            $text .= TextUtils::htmlToText($contentParams['text']);
        }

        return $text;
    }
}
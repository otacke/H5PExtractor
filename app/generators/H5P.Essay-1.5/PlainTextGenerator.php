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
 * Class for generating HTML for H5P.Essay-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorEssayMajor1Minor5 implements PlainTextGeneratorInterface
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

        if (isset($contentParams['media']['type'])) {
            $text .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $text .= TextUtils::htmlToText(($contentParams['taskDescription'] ?? ''));

        if (!empty($contentParams['placeholderText'])) {
            $text .= $contentParams['placeholderText'] . "\n\n";
        }

        $line = '________________________________________' . "\n";
        $numberLines = (isset($contentParams['behaviour']['inputFieldSize'])) ?
            $contentParams['behaviour']['inputFieldSize'] :
            10;

        for ($i = 0; $i < $numberLines / 2; $i++) {
            $text .= $line . "\n";
        }

        return trim($text);
    }
}

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
require_once __DIR__ . '/../../TextUtils.php';

/**
 * Class for generating plain text for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorBlanks_1_14 implements PlainTextGeneratorInterface
{
    /**
     * Create the plain text for the given H5P content type.
     *
     * @param array                  $params Parameters.
     * @param PlainTextGeneratorMain $main   The main plain text generator.
     *
     * @return string The plain text for the H5P content type.
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

        $text .= TextUtils::htmlToText($contentParams['text']);

        // loop through $contentParams['questions']
        $questionCount = count($contentParams['questions']);
        for (
            $questionIndex = 0; $questionIndex < $questionCount; $questionIndex++
        ) {
            $questionData = $contentParams['questions'][$questionIndex];

            $blank = ($contentParams['behaviour']['separateLines']) ?
                "\n__________\n" : '__________';

            $questionData = preg_replace(
                '/\*([^*]+)\*/',
                $blank,
                $questionData
            );

            $text .= TextUtils::htmlToText($questionData);
        }

        return trim($text);
    }
}

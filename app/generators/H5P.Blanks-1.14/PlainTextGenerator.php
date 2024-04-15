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
 * @link     https://todo
 */

namespace H5PExtractor;

require_once __DIR__ . '/../PlainTextGeneratorInterface.php';
require_once __DIR__ . '/../../TextUtils.php';

/**
 * Class for generating HTML for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class PlainTextGeneratorBlanks_1_14 implements PlainTextGeneratorInterface
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

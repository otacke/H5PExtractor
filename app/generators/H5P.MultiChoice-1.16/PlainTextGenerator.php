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
 * Class for generating HTML for H5P.MultiChoice-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorMultiChoice_1_16 implements PlainTextGeneratorInterface
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

        if ($contentParams['behaviour']['randomAnswers']) {
            shuffle($contentParams['answers']);
        }

        if (isset($contentParams['media']['type'])) {
            $text .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $text .= TextUtils::htmlToText(($contentParams['question'] ?? ''));

        $numCorrect = count(
            array_filter(
                $contentParams['answers'],
                function ($answer) {
                    return $answer['correct'];
                }
            )
        );

        $mode = ($numCorrect === 1) ? 'h5p-radio' : 'h5p-check';
        if ($contentParams['behaviour']['type'] === 'single') {
            $mode = 'h5p-radio';
        } elseif ($contentParams['behaviour']['type'] === 'multi') {
            $mode = 'h5p-check';
        }

        $listItem = "( )";
        if ($mode === 'h5p-check') {
            $listItem = '[ ]';
        }

        $answerCount = count($contentParams['answers']);
        for ($answerIndex = 0; $answerIndex < $answerCount; $answerIndex++) {
            $answerData = $contentParams['answers'][$answerIndex];
            $text .= $listItem . ' ' .
                TextUtils::htmlToText(($answerData['text'] ?? "\n"));
        }

        return trim($text);
    }
}

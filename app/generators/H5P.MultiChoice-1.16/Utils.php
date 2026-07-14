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
 * Utility functions for H5P.MultiChoice-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsMultiChoiceMajor1Minor16
{
    /**
     * Get the text of correct answers.
     *
     * @param array $answers The answers array.
     *
     * @return string The solution text.
     */
    public static function getSolutionTexts($answers)
    {
        $correctAnswers = array_filter(
            $answers,
            function ($answer) {
                return $answer['correct'];
            }
        );

        $solutionTexts = array_map(
            function ($answer) {
                return TextUtils::htmlToText($answer['text']);
            },
            $correctAnswers
        );

        return implode(', ', $solutionTexts);
    }
}

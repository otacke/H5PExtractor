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
 * Class for handling CSS.
 *
 * @category Utility
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsDragText_1_10
{
    /**
     * Parse the given text into an array of text and tags.
     *
     * Example: "This is *bold* text." => ["This is ", "*bold*", " text."]
     *
     * @param string $text The text to parse.
     *
     * @return string[] The parsed text.
     */
    public static function parseText($text)
    {
        return preg_split(
            '/(\*.*?\*)/',
            $text, -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
    }

    /**
     * Lex the given solution text. Weird name, but used by H5P Group.
     *
     * @param string $solutionText The solution text.
     *
     * @return array The lexed solution text.
     */
    public static function lex($solutionText)
    {
        preg_match(
            '/(:([^\\\\*]+))/u',
            $solutionText,
            $tipMatches
        );
        $tip = $tipMatches[0] ?? '';

        preg_match(
            '/(\\\+([^\\*:]+))/u',
            $solutionText,
            $correctFeedbackMatches
        );
        $correctFeedback = $correctFeedbackMatches[0] ?? '';

        preg_match(
            '/(\\\-([^\\*:]+))/u',
            $solutionText,
            $incorrectFeedbackMatches
        );
        $incorrectFeedback = $incorrectFeedbackMatches[0] ?? '';

        $text = str_replace($tip, '', $solutionText);
        $text = str_replace($correctFeedback, '', $text);
        $text = str_replace($incorrectFeedback, '', $text);
        $text = trim($text);
        $text = UtilsDragText_1_10::_cleanCharacter('*', $text);

        $tip = substr($tip, 1);
        $tip = trim($tip);

        return [
            'tip' => $tip,
            'text' => $text
        ];
    }

    /**
     * Clean the given character from the beginning and end of the string.
     *
     * @param string $char The character to clean.
     * @param string $str  The string to clean.
     *
     * @return string The cleaned string.
     */
    private static function _cleanCharacter($char = '', $str = '')
    {
        if (str_starts_with($str, $char)) {
            $str = substr($str, 1);
        }

        if (str_ends_with($str, $char)) {
            $str = substr($str, 0, -1);
        }

        return $str;
    }
}

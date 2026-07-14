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
 * Utility functions for H5P.MarkTheWords-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsMarkTheWordsMajor1Minor11
{
    /**
     * Get the text of correct (marked) words.
     *
     * Correct words in MarkTheWords are marked with asterisks
     * before and after the word in the textField (e.g., "*word*").
     *
     * @param array $params The params array containing 'textField'.
     *
     * @return string The solution text (comma-separated list of correct words).
     */
    public static function getSolutionTexts($params)
    {
        $textField = $params['textField'] ?? '';

        // Strip HTML tags to get plain text (matching JS behavior).
        $plainText = strip_tags($textField);

        // Find all words wrapped in asterisks: *word*.
        preg_match_all('/\*+([^*]+)\*+/', $plainText, $markedMatches);
        $markedWords = $markedMatches[1] ?? [];

        // Handle *** (double asterisk = literal asterisk in the word).
        $markedWords = array_map(
            function ($word) {
                return str_replace('***', '*', $word);
            },
            $markedWords
        );

        // Strip leading and trailing punctuation, matching JS behavior.
        $markedWords = array_map(
            function ($word) {
                $word = self::stripLeadingPunctuation($word);
                $word = self::stripTrailingPunctuation($word);
                $word = trim($word);
                return $word;
            },
            $markedWords
        );

        // Filter out empty strings.
        $markedWords = array_values(array_filter(
            $markedWords,
            function ($word) {
                return $word !== '';
            }
        ));

        // If no marked words found (blankIsCorrect case), return the full text.
        if (empty($markedWords)) {
            return TextUtils::htmlToText($textField);
        }

        return implode(', ', $markedWords);
    }

    /**
     * Strip leading punctuation from a word.
     *
     * Matches the JS removeLeadingPunctuation function.
     *
     * @param string $word The word.
     *
     * @return string The word with leading punctuation stripped.
     */
    private static function stripLeadingPunctuation($word)
    {
        return preg_replace(
            '/^[\[\({\x{E000}\x{00BF}\x{00A1}\x{201C}\x{201D}\x{00AB}\x{201E}]+/u',
            '',
            $word
        );
    }

    /**
     * Strip trailing punctuation from a word.
     *
     * Matches the JS removeTrailingPunctuation function.
     *
     * @param string $word The word.
     *
     * @return string The word with trailing punctuation stripped.
     */
    private static function stripTrailingPunctuation($word)
    {
        return preg_replace(
            '/["…:;?!\]\)}⟩»"]+$/u',
            '',
            $word
        );
    }
}

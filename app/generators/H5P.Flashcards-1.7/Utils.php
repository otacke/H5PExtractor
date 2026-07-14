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
 * Utility functions for H5P.Flashcards-1.7.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsFlashcardsMajor1Minor7
{
    /**
     * Get the solution text.
     *
     * @param array $params The params array.
     *
     * @return string The solution text.
     */
    public static function getSolutionTexts($params)
    {
        $cardSolutions = [];
        if (isset($params['cards']) && is_array($params['cards'])) {
            foreach ($params['cards'] as $card) {
                if (isset($card['answer']) && is_string($card['answer']) && !empty($card['answer'])) {
                    // Split by '/' that is not preceded by '\'
                    $alternatives = preg_split('/(?<!\\\\)\//', $card['answer']);

                    // For each alternative, clean it (replace escaped \/ with /) and trim
                    $cleanedAlternatives = array_map(function ($alt) {
                        return trim(str_replace('\\/', '/', $alt));
                    }, $alternatives);

                    $cardSolutions[] = implode('/', $cleanedAlternatives);
                }
            }
        }

        if (!empty($cardSolutions)) {
            return implode(', ', $cardSolutions);
        }

        return Generator::SOLUTION_FALLBACK;
    }
}

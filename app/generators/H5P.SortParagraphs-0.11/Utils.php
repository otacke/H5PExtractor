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
 * Utility functions for H5P.SortParagraphs-0.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsSortParagraphsMajor0Minor11
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
        $paragraphs = $params['paragraphs'] ?? [];
        if (!is_array($paragraphs)) {
            return Generator::SOLUTION_FALLBACK;
        }

        return array_reduce($paragraphs, function ($carry, $p) {
            return $carry . TextUtils::htmlToText((string)$p);
        }, '');
    }
}

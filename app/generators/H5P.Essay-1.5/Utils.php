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
 * Utility functions for H5P.Essay-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsEssayMajor1Minor5
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
        $parts = [];
        if (isset($params['solution']) && is_array($params['solution'])) {
            if (!empty($params['solution']['introduction'])) {
                $parts[] = TextUtils::htmlToText($params['solution']['introduction']);
            }
            if (!empty($params['solution']['sample'])) {
                $parts[] = TextUtils::htmlToText($params['solution']['sample']);
            }
        }

        if (!empty($parts)) {
            return implode("\n", $parts);
        }

        return Generator::SOLUTION_FALLBACK;
    }
}

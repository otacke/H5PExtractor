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
 * Utility functions for H5P.TrueFalse-1.8.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsTrueFalseMajor1Minor8
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
        if (!isset($params['correct']) || !isset($params['l10n'])) {
            return Generator::SOLUTION_FALLBACK;
        }

        if ($params['correct'] === 'true') {
            return $params['l10n']['trueText'];
        }

        if ($params['correct'] === 'false') {
            return $params['l10n']['falseText'];
        }

        return Generator::SOLUTION_FALLBACK;
    }
}

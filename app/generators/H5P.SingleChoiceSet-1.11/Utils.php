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
 * Utility functions for H5P.SingleChoiceSet-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsSingleChoiceSetMajor1Minor11
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
        $allSolutionTexts = [];
        if (isset($params['choices']) && is_array($params['choices'])) {
            foreach ($params['choices'] as $choice) {
                if (isset($choice['answers']) && is_array($choice['answers']) && !empty($choice['answers'])) {
                    // The first item is the correct answer
                    $solution = TextUtils::htmlToText($choice['answers'][0]);
                    $solution = rtrim($solution, "\n");
                    $allSolutionTexts[] = $solution;
                }
            }
        }
        return implode("\n", $allSolutionTexts);
    }
}

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
 * Utility functions for H5P.ImageSequencing-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsImageSequencingMajor1Minor1
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
        $descriptions = [];
        if (isset($params['sequenceImages']) && is_array($params['sequenceImages'])) {
            foreach ($params['sequenceImages'] as $imageElement) {
                if (isset($imageElement['imageDescription']) &&
                    is_string($imageElement['imageDescription']) &&
                    !empty($imageElement['imageDescription'])
                ) {
                    $descriptions[] = TextUtils::htmlToText($imageElement['imageDescription']);
                }
            }
        }

        if (!empty($descriptions)) {
            return implode(', ', $descriptions);
        }

        return Generator::SOLUTION_FALLBACK;
    }
}

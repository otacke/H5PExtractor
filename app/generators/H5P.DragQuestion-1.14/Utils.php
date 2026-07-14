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
 * Utility functions for H5P.DragQuestion-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsDragQuestionMajor1Minor14
{
    /**
     * Get the solution text.
     *
     * @param array $params The params array.
     * @param HtmlGeneratorMain|PlainTextGeneratorMain $main Main instance.
     *
     * @return string The solution text.
     */
    public static function getSolutionTexts($params, $main)
    {
        $answers = [];

        $task = $params['question']['task'] ?? [];
        foreach ($task['dropZones'] ?? [] as $dropZone) {
            $dropZoneAnswers = [];
            foreach ($dropZone['correctElements'] as $elementIndex) {
                $draggable = $task['elements'][$elementIndex];

                $text = '';
                $main->newRunnable(
                    $draggable['type'] ?? [],
                    1,
                    $text,
                );
                $text = trim($text);

                if ($text !== '') {
                    $dropZoneAnswers[] = $text;
                }
            }

            if (count($dropZoneAnswers) !== 0) {
                $answers[] = implode('/', $dropZoneAnswers);
            }
        }

        return implode(', ', $answers);
    }
}

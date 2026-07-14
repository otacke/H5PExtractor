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
class UtilsInteractiveBookMajor1Minor13
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
        if (!isset($params['chapters'])) {
            return Generator::SOLUTION_FALLBACK;
        }

        $solutions = [];

        foreach ($params['chapters'] as $index => $chapter) {
            $chapterTitle = $chapter['metadata']['title'] ?? '';
            if ($chapterTitle === '') {
                $chapterTitle = $index;
            }

            $innerContainer = '';
            $instance = $main->newRunnable(
                [
                    'library' => $chapter['library'],
                    'params' => $chapter['params'],
                ],
                1,
                $innerContainer,
                false,
                [
                    'metadata' => $chapter['metadata'] ?? [],
                ]
            );

            if ((is_object($instance) && method_exists($instance, 'showSolutions'))) {
                $solutions[] = '## ' . $chapterTitle . "\n" . $instance->showSolutions();
            }
        }

        return implode("\n---\n", $solutions);
    }
}

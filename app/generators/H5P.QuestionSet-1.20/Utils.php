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
 * Utility functions for H5P.QuestionSet-1.20.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsQuestionSetMajor1Minor20
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
        if (!isset($params['questions'])) {
            return Generator::SOLUTION_FALLBACK;
        }

        $solutions = [];

        $progressText = $params['texts']['textualProgress'] ?? '';

        $questions = $params['questions'];
        for ($i = 0; $i < count($questions); $i++) {
            $question = $questions[$i];

            $questionContainer = '';
            $instance = $main->newRunnable(
                [
                    'library' => $question['library'],
                    'params' => $question['params'],
                ],
                1,
                $questionContainer,
                false,
                [
                    'metadata' => $question['metadata'],
                ]
            );

            if ((is_object($instance) && method_exists($instance, 'showSolutions'))) {
                $questionTitle = str_replace(
                    '@current',
                    $i + 1,
                    str_replace(
                        '@total',
                        count($questions),
                        $progressText
                    )
                );

                $solutions[] = '## ' . $questionTitle . "\n" . $instance->showSolutions();
            }
        }

        return implode("\n---\n", $solutions);
    }
}

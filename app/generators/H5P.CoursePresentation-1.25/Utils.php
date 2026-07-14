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
class UtilsCoursePresentationMajor1Minor25
{

    /**
     * Coverage percentage threshold for determining if an element is covered by a goToSlide area.
     */
    const COVERAGE_THRESHOLD_PERCENT = 95.0;

    /**
     * Check if any of the goToSlides covers the telemetry element.
     *
     * @param array $telemetry  The telemetry element.
     * @param array $goToSlides The goToSlides areas.
     *
     * @return bool True if covered, false otherwise.
     */
    public static function isCoveredByGoToSlide($telemetry, $goToSlides)
    {
        $result = false;

        if (!is_array($goToSlides)) {
            return false;
        }

        foreach ($goToSlides as $goToSlide) {
            $overlapPercentage =
                UtilsCoursePresentationMajor1Minor25::calculateCoveragePercentage($telemetry, $goToSlide);

            if ($overlapPercentage > UtilsCoursePresentationMajor1Minor25::COVERAGE_THRESHOLD_PERCENT) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * Calculate what percentage of element1 is covered by element2.
     *
     * @param array $element1 The first element.
     * @param array $element2 The second element.
     *
     * @return float The coverage percentage.
     */
    public static function calculateCoveragePercentage($element1, $element2)
    {
        $intersectLeft = max($element1['x'], $element2['x']);
        $intersectTop = max($element1['y'], $element2['y']);
        $intersectRight = min($element1['x'] + $element1['width'], $element2['x'] + $element2['width']);
        $intersectBottom = min($element1['y'] + $element1['height'], $element2['y'] + $element2['height']);

        $intersectionWidth = max(0, $intersectRight - $intersectLeft);
        $intersectionHeight = max(0, $intersectBottom - $intersectTop);

        $intersectionArea = $intersectionWidth * $intersectionHeight;

        $element1Area = $element1['width'] * $element1['height'];

        if ($element1Area == 0) {
            return 0.0; // Avoid division by zero
        }

        $coveragePercentage = ($intersectionArea / $element1Area) * 100;

        return round($coveragePercentage, 2);
    }

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
        if (!isset($params['presentation']['slides'])) {
            return Generator::SOLUTION_FALLBACK;
        }

        $solutions = [];

        $slideString = (isset($params['l10n']['slide'])) ? $params['l10n']['slide'] : 'Slide'; // TODO: i18n
        $slideProgressTemplate = $slideString . ' %d / %d';

        $slides = $params['presentation']['slides'];
        for ($i = 0; $i < count($slides); $i++) {
            $slide = $slides[$i];
            if (!isset($slide['elements'])) {
                continue;
            }

            $elements = $slide['elements'];

            // sort elements by their position (top to bottom, left to right)
            usort(
                $elements,
                function ($a, $b) {
                    if ($a['y'] == $b['y']) {
                        return $a['x'] - $b['x'];
                    }
                    return $a['y'] - $b['y'];
                }
            );

            foreach ($slide['elements'] as $element) {
                if (isset($element['action'])) {
                    $goToSlides = $slide['goToSlides'] ?? [];
                    if (UtilsCoursePresentationMajor1Minor25::isCoveredByGoToSlide($element, $goToSlides)) {
                        continue; // Skip if elements are supposedly used for navigation
                    }

                    $elementContainer = '';
                    $action = $element['action'];

                    $instance = $main->newRunnable(
                        [
                            'library' => $action['library'],
                            'params' => $action['params'],
                        ],
                        1,
                        $elementContainer,
                        false,
                        [
                            'metadata' => isset($action['metadata']) ? $action['metadata'] : [],
                        ]
                    );

                    if ((is_object($instance) && method_exists($instance, 'showSolutions'))) {
                        $slideTitle = sprintf($slideProgressTemplate, $i + 1, count($slides)) . "\n";

                        $solutions[] = $slideTitle . $instance->showSolutions();
                    }
                }
            }
        }

        return implode("\n\n", $solutions);
    }
}

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
 * Class for generating HTML for H5P.CoursePresentation-1.25.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorCoursePresentationMajor1Minor25 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params     Parameters.
     * @param int   $contentId  Content ID.
     * @param array $extras     Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

        $output = '';

        $slideString = (isset($this->params['l10n']['slide'])) ? $this->params['l10n']['slide'] : 'Slide';
        $slideProgressTemplate = $slideString . ' %d / %d';

        if (isset($this->params['presentation']['slides'])) {
            $slides = $this->params['presentation']['slides'];
            for ($i = 0; $i < count($slides); $i++) {
                $output .= '##' . sprintf($slideProgressTemplate, $i + 1, count($slides)) . "\n";

                $slide = $slides[$i];
                if (!isset($slide['elements'])) {
                    continue;
                }

                $goToSlideAreas = [];
                foreach ($slide['elements'] as $element) {
                    if (isset($element['goToSlide'])) {
                        $goToSlideAreas[] = [
                            'x' => $element['x'],
                            'y' => $element['y'],
                            'width' => $element['width'],
                            'height' => $element['height'],
                        ];
                    }
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
                        if (UtilsCoursePresentationMajor1Minor25::isCoveredByGoToSlide($element, $goToSlideAreas)) {
                            continue; // Skip if elements are supposedly used for navigation
                        }

                        $elementContainer = '';
                        $action = $element['action'];

                        $this->main->newRunnable(
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
                        $output .= $elementContainer . "\n\n";
                    }
                }
                $output .= "\n";
            }
        }

        $container = trim($output);
    }
}

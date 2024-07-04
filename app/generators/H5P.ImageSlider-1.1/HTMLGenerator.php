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
 * Class for generating HTML for H5P.ImageSlider-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorImageSliderMajor1Minor1 extends Generator implements GeneratorInterface
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-image-slider', $container);

        $this->params['imageSlides'] = $this->params['imageSlides'] ?? [];

        for ($i = 0; $i < count($this->params['imageSlides']); $i++) {
            $container .= $this->renderSlidesHolder([
                'image' => $this->params['imageSlides'][$i]['params']['image'],
                'index' => $i,
                'total' => count($this->params['imageSlides']),
            ]);
        }

        $container .= $htmlClosing;
    }

    /**
     * Render a slides holder.
     *
     * @param array $params Parameters.
     *
     * @return string The rendered slides holder.
     */
    private function renderSlidesHolder($params)
    {
        $holder = '<div class="h5p-image-slider-slides-holder">';

        $holder .= '<div class="h5p-image-slider-slides">';

        $holder .= '<div class="h5p-image-slider-slide-holder">';

        $imageContainer = '<div class="h5p-image-slider-image-holder h5pClassName" style="">';
        $this->main->newRunnable(
            [
                'library' => $params['image']['library'],
                'params' => $params['image']['params'],
            ],
            1,
            $imageContainer,
            false,
            [
                'metadata' => $params['image']['metadata'],
            ]
        );

        $holder .= $imageContainer;

        $holder .= '</div>';

        $holder .= '</div>';

        $holder .= '<ul class="h5p-image-slider-progress" style="top: 100%;">';
        for ($i = 0; $i < $params['total']; $i++) {
            $currentClass = ($i === $params['index']) ?
                ' h5p-image-slider-current-progress-element' :
                '';
            $holder .= '<li class="h5p-image-slider-progress-element' . $currentClass . '">';
            $holder .= '<button class="h5p-image-slider-progress-button"></button>';
            $holder .= '</li>';
        }
        $holder .= '</ul>';

        // Closing h5p-image-slider-slides-holder
        $holder .= '</div>';

        return $holder;
    }
}

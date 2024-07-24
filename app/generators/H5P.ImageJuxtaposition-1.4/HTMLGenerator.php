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
 * Class for generating HTML for H5P.ImageJuxtaposition-1.4.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorImageJuxtapositionMajor1Minor4 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-question h5p-image-juxtaposition', $container);

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-image-juxtaposition-container">';

        if (!empty($this->params['title'])) {
            $container .=
                '<div ' .
                    'class="h5p-image-juxtaposition-title"' .
                '>' .
                    $this->params['title'] .
                '</div>';
        }

        $container .= $this->renderSlide([
            'image' => $this->params['imageBefore']['imageBefore'],
            'label' => $this->params['imageBefore']['labelBefore'] ?? null,
            'index' => 0
        ]);

        $container .= $this->renderSlide([
            'image' => $this->params['imageAfter']['imageAfter'],
            'label' => $this->params['imageAfter']['labelAfter'] ?? null,
            'index' => 1
        ]);

        $container .= '</div>'; // Closing h5p-image-juxtaposition-container
        $container .= '</div>'; // Closing h5p-question-content

        $container .= $htmlClosing;
    }

    /**
     * Render a slides holder.
     *
     * @param array $params Parameters.
     *
     * @return string The rendered slides holder.
     */
    private function renderSlide($params)
    {
        $marginStyle = ($params['index'] === 0) ?
            ' margin-bottom: 1rem;' :
            '';

        $slide =
            '<div ' .
                'class="h5p-image-juxtaposition-juxtapose" ' .
                'style="line-height: 0;' . $marginStyle . '"' .
            '>';

        $slide .=
            '<div ' .
                'class="h5p-image-juxtaposition-image" ' .
                'style="position: relative;"' .
            '>';
        $slide .=
            '<img ' .
                'src="' .
                    $this->fileToBase64($params['image']['params']['file']['path']) .
                '" ' .
                'style="max-width: 100%; width: 100%" ' .
            '/>';

        if (!empty($params['label'])) {
            $positionStyle = ($params['index'] === 0) ?
                'left: 0;' :
                'right: 0;';

            $slide .=
                '<div ' .
                    'class="h5p-image-juxtaposition-label"' .
                    'style="' . $positionStyle . '"' .
                '>' .
                        $params['label'] .
                '</div>';
        }

        $slide .= '</div>'; // Closing h5p-image-juxtaposition-image
        $slide .= '</div>'; // Closing h5p-image-juxtaposition-juxtapose

        return $slide;
    }
}

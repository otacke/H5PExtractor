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
class PlainTextGeneratorImageSliderMajor1Minor1 extends Generator implements GeneratorInterface
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

        $this->params['imageSlides'] = $this->params['imageSlides'] ?? [];
        for ($i = 0; $i < count($this->params['imageSlides']); $i++) {
            $params = $this->params['imageSlides'][$i]['params'] ?? [];

            $imageContainer = '';
            $this->main->newRunnable(
                [
                    'library' => $params['image']['library'],
                    'params' => $params['image']['params'],
                ],
                1,
                $imageContainer,
                false,
                [
                    'metadata' => isset($params['image']['metadata']) ? $params['image']['metadata'] : [],
                ]
            );

            $container .= $i . '. ' . $imageContainer . "\n";
        }

        $container = trim($container);
    }
}

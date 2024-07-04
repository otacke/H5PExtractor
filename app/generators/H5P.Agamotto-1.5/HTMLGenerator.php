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
 * Class for generating HTML for H5P.Agamotto-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorAgamottoMajor1Minor5 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-agamotto', $container);

        $this->params['items'] = $this->params['items'] ?? [];

        for ($i = 0; $i < count($this->params['items']); $i++) {
            $container .= $this->renderSlide([
                'image' => $this->params['items'][$i]['params']['image'],
                'labelText' => $this->params['items'][$i]['params']['itemText'],
                'description' => $this->params['items'][$i]['params']['description'],
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
    private function renderSlide($params)
    {
        $slide = '';
        return $slide;
    }
}

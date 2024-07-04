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
        $this->params['items'] = $this->params['items'] ?? [];

        $this->params['items'] = array_filter(
            $this->params['items'],
            function ($item) {
                $path = $item['image']['params']['file']['path'] ?? null;
                return !empty($path);
            }
        );

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-agamotto', $container);

        $container .= '<div class="h5p-question-content">';
        $container .= '<div class="h5p-agamotto">';

        for ($i = 0; $i < count($this->params['items']); $i++) {
            $container .= $this->renderSlide([
                'image' => $this->params['items'][$i]['image'],
                'labelText' => $this->params['items'][$i]['itemText'] ?? '',
                'description' => $this->params['items'][$i]['description'] ?? '',
                'index' => $i,
                'total' => count($this->params['items']),
            ]);
        }

        $container .= '</div>'; // Closing h5p-agamotto
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
        $imagePath = $this->main->h5pFileHandler->getBaseDirectory() . '/' .
            $this->main->h5pFileHandler->getFilesDirectory() . '/' .
            'content' . '/' . $params['image']['params']['file']['path'];

        $bottomMarginStyle = ($params['index'] === $params['total'] - 1) ?
            '' :
            'margin-bottom: 2rem;';

        $slide =
            '<div ' .
                'class="h5p-agamotto-wrapper"' .
                'style="' . $bottomMarginStyle . '"' .
            '>';

        $slide .= '<div class="h5p-agamotto-images-container" style="line-height: 0;">';
        $slide .=
            '<img ' .
                'src="' . FileUtils::fileToBase64($imagePath) . '"' .
                'style="width: 100%;"' .
            '/>';
        $slide .= '</div>'; // Closing h5p-agamotto-images-container

        $slide .= '<div class="h5p-agamotto-descriptions-container">';
        $slide .= '<div>';
        $slide .= $params['description'];
        $slide .= '</div>';
        $slide .= '</div>';
        $slide .= '</div>'; // Closing h5p-agamotto-wrapper

        return $slide;
    }
}

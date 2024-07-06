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
 * Class for generating HTML for H5P.ImageSequencing-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorImageSequencingMajor1Minor1 extends Generator implements GeneratorInterface
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
     * @param array             $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $htmlClosing = TextUtils::getClosingTag($container);
        $container = str_replace('h5pClassName', 'h5p-image-sequencing', $container);

        if (isset($this->params['taskDescription'])) {
            $container .=
                '<div class="h5p-task-description">' .
                    $this->params['taskDescription'] .
                '</div>';
        }

        $container .= '<ul class="sortable ui-sortable">';

        shuffle($this->params['sequenceImages']);

        for ($i = 0; $i < count($this->params['sequenceImages']); $i++) {
            $imgSrc = $this->fileToBase64($this->params['sequenceImages'][$i]['image']['path']);

            $container .=
                '<li class="sequencing-item draggabled ui-sortable-handle ui-droppable">';
            $container .= '<span>';
            $container .= '<div class="image-container">';
            $container .= '<img src="' . $imgSrc . '"/>';
            $container .= '</div>';
            $container .= '<div class="image-desc">';
            $container .=
                '<span class="text">' .
                    $this->params['sequenceImages'][$i]['imageDescription'] .
                '</span>';
            $container .= '</div>';
            $container .= '</span>';
            $container .= '</li>';
        }

        $container .= '</ul>';

        $container .= $htmlClosing;
    }
}

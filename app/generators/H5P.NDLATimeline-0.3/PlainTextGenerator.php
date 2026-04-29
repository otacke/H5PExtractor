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
 * Class for generating HTML for H5P.NDLATimeline-0.3
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorNDLATimelineMajor0Minor3 extends Generator implements GeneratorInterface
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

        $slides = isset($this->params['timelineItems']) ? $this->params['timelineItems'] : [];
        $slides = UtilsNDLATimelineMajor0Minor3::filterAndSortSlides($slides);

        if (isset($this->params['showTitleSlide'])) {
            $container .= $this->buildSlide($this->params['titleSlide']);
            $container .= "\n";
        }

        foreach ($slides as $slide) {
            $container .= $this->buildSlide($slide);
            $container .= "\n";
        }

        $container = trim($container);
    }

    /**
     * Build slide.
     *
     * @param array $slide Slide.
     *
     * @return string Slide representation.
     */
    private function buildSlide($slide)
    {
        $slide_dom = '';
        $date = UtilsNDLATimelineMajor0Minor3::formatDate(
            $slide,
            $this->extras['metadata']['defaultLanguage'],
            $this->params['l10n']['bce']
        );

        $title = $date ? $date . ': ' : '';
        if (isset($slide['title'])) {
            $slideTitle = TextUtils::htmlToText($slide['title']);
            $title .= $slideTitle;
            $slide_dom .= '## ' . $title . "\n";
        }

        if (isset($slide['description'])) {
            $description = '';
            $this->main->newRunnable(
                [
                    'library' => $slide['description']['library'],
                    'params' => $slide['description']['params'],
                ],
                1,
                $description,
                false,
                [
                    'metadata' => $slide['description']['metadata'] ?? [],
                ]
            );
            if (!empty($description)) {
                $slide_dom .= $description . "\n";
            }
        }

        $slide_dom .= $this->formatMedia($slide);

        if (!empty($slide['tags'])) {
            $tags = array_map(fn($tag) => $tag['name'], $slide['tags']);
            $slide_dom .= '(' . implode(', ', $tags) . ')' . "\n";
        }

        return $slide_dom;
    }

    /**
     * Format media.
     *
     * @param array $slide Slide.
     *
     * @return string Media as markdown representation.
     */
    private function formatMedia($slide)
    {
        if (!isset($slide['mediaType'])) {
            return '';
        }
        switch ($slide['mediaType']) {
            case 'custom':
                return !empty($slide['customMedia']) ? $slide['customMedia'] . "\n" : '';
            case 'image':
                $imageTitle = $slide['imageAlt'] ?? ($slide['image']['copyright']['title'] ?? '');
                return !empty($imageTitle) ? '![' . $imageTitle . ']' . "\n" : '';
            case 'video':
                $videoTitle = $slide['video'][0]['copyright']['title'] ?? '';
                return $videoTitle ? 'Video: ' . $videoTitle . "\n" : '';
            case 'audio':
                $audioTitle = $slide['audio'][0]['copyright']['title'] ?? '';
                return $audioTitle ? 'Audio: ' . $audioTitle . "\n" : '';
            default:
                return '';
        }
    }
}

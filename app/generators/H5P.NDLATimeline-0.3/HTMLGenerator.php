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
class HtmlGeneratorNDLATimelineMajor0Minor3 extends Generator implements GeneratorInterface
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

        $imageToTextRatio = '40:60';
        if (isset($this->params['behaviour']) && isset($this->params['behaviour']['imageToTextRatio'])) {
            $imageToTextRatio = $this->params['behaviour']['imageToTextRatio'];
        }
        $containerStyle
            = '--tl-image-ratio:' . explode(':', $imageToTextRatio)[0] . 'fr;' .
              '--tl-text-ratio:' . explode(':', $imageToTextRatio)[1] . 'fr;' .
              'width:' . $this->getRenderWidth() . 'px;';

        $slides = isset($this->params['timelineItems']) ? $this->params['timelineItems'] : [];
        $slides = UtilsNDLATimelineMajor0Minor3::filterAndSortSlides($slides);

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-timeline', $container);

        $container .= '<div style="' . $containerStyle . '" style="height:auto;">';
        $container .= '  <div class="h5p-timeline-wrapper" style="height:auto;">';
        $container .= '    <div class="tl-timeline tl-layout-landscape" style="height:auto;">';

        $container .= '      <div class="tl-storyslider" style="height:auto;">';

        $container .= '        <div class="tl-slider-container-mask" style="height:auto;">';
        $container .= '          <div class="tl-slider-container" style="height:auto;position: relative;">';
        $container .= '            <div class="tl-slider-item-container" style="display:block;height:auto;">';

        if (isset($this->params['showTitleSlide'])) {
            $container .= $this->buildSlide($this->params['titleSlide']);
        }

        foreach ($slides as $slide) {
            $container .= $this->buildSlide($slide);
        }

        $container .= '            </div>';
        $container .= '          </div>';
        $container .= '        </div>';
        $container .= '      </div>';
        $container .= '    </div>';
        $container .= '  </div>';
        $container .= '</div>';

        $container .= $htmlClosing;
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
        $date = UtilsNDLATimelineMajor0Minor3::formatDate(
            $slide,
            $this->extras['metadata']['defaultLanguage'],
            $this->params['l10n']['bce']
        );

        $classNames = $this->getSlideClassNames($slide);
        $slideDOM = '<div class="' . implode(' ', $classNames) . '" style="height:max-content;position: relative;">';
        $slideDOM .= $this->getSlideScrollableContainer($slide, $date);
        $slideDOM .= '  <div class="tl-slide-background"></div>';
        $slideDOM .= '</div>';

        return $slideDOM;
    }

    /**
     * Get class names for a slide.
     *
     * @param array $slide Slide.
     *
     * @return array List of class names.
     */
    private function getSlideClassNames($slide)
    {
        $classNames = ['tl-slide'];
        if ($slide['slideType'] === 'title') {
            $classNames[] = 'tl-slide-titleslide';
        }
        return $classNames;
    }

    /**
     * Get the scrollable container for a slide.
     *
     * @param array  $slide Slide.
     * @param string $date  Date string.
     *
     * @return string HTML for the scrollable container.
     */
    private function getSlideScrollableContainer($slide, $date)
    {
        $container  = '  <div class="tl-slide-scrollable-container">';
        $container .= '    <div class="tl-slide-content-container">';
        $container .= '      <div class="tl-slide-content">';
        $container .= $this->getSlideText($slide, $date);
        $container .= $this->getSlideMedia($slide);
        $container .= '      </div>';
        $container .= '    </div>';
        $container .= '  </div>';
        return $container;
    }

    /**
     * Get the text section for a slide.
     *
     * @param array  $slide Slide.
     * @param string $date  Date string.
     *
     * @return string HTML for the text section.
     */
    private function getSlideText($slide, $date)
    {
        $text  = '        <div class="tl-text">';
        $text .= '          <div class="tl-text-content-container">';
        $text .= $this->getSlideHeadline($slide, $date);
        $text .= '            <div class="tl-text-content">';
        $text .= $this->getSlideTags($slide);
        $text .= $this->getSlideDescription($slide);
        $text .= '            </div>';
        $text .= '          </div>';
        $text .= '        </div>';
        return $text;
    }

    /**
     * Get the headline for a slide.
     *
     * @param array  $slide Slide.
     * @param string $date  Date string.
     *
     * @return string HTML for the headline.
     */
    private function getSlideHeadline($slide, $date)
    {
        // TODO: Why is this not included in content file?
        $dateStyle = 'color: #000;font-family: \'PT Sans Narrow\', sans-serif;';

        $headline  = '            <div class="tl-text-headline-container">';
        $headline .= '              <h2 class="tl-headline tl-headline-title" style="word-break: break-word;">' .
            $slide['title'] .
            '</h2>';
        if (!empty($date)) {
            $headline .= '              <h3 class="tl-headline-date" style="' . $dateStyle . '">' . $date . '</h3>';
        }
        $headline .= '            </div>';
        return $headline;
    }

    /**
     * Get the tags section for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the tags section.
     */
    private function getSlideTags($slide)
    {
        if (empty($slide['tags'])) {
            return '';
        }
        $tagsDOM  = '<div class="h5p-tl-tags-container">';
        $tagsDOM .= '<ul class="tags">';
        foreach ($slide['tags'] as $tag) {
            $tagsDOM .= $this->getTagDOM($tag);
        }
        $tagsDOM .= '</ul>';
        $tagsDOM .= '</div>';
        return $tagsDOM;
    }

    /**
     * Get the HTML for a single tag.
     *
     * @param array $tag Tag data.
     *
     * @return string HTML for the tag.
     */
    private function getTagDOM($tag)
    {
        $backgroundColor = $tag['color'];
        $tagDOM = '<li class="tag" style="' .
        '--background-color:' . $backgroundColor . ';' .
        'background-color: var(--background-color);' .
        'color: oklab(from var(--background-color) calc(max(0, min((0.5 - l) * 100, 1))) a b);' .
        '">';
        $tagDOM .= $tag['name'];
        $tagDOM .= '</li>';
        return $tagDOM;
    }

    /**
     * Get the description section for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the description.
     */
    private function getSlideDescription($slide)
    {
        $descDOM = '              <div class="h5p-tl-slide-description">';
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
                $descDOM .= $description;
            }
        }
        $descDOM .= '              </div>';
        return $descDOM;
    }

    /**
     * Get the media section for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the media section.
     */
    private function getSlideMedia($slide)
    {
        $media  = '        <div class="tl-media">';
        $media .= '          <div class="tl-media-content-container">';
        $media .= '            <div class="tl-media-content" style="height:200px;">';
        $media .= $this->getMediumDOM($slide);
        $media .= '            </div>';
        $media .= '          </div>';
        $media .= '        </div>';
        return $media;
    }

    /**
     * Get the appropriate media DOM for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the media.
     */
    private function getMediumDOM($slide)
    {
        if (!isset($slide['mediaType'])) {
            return '';
        }

        switch ($slide['mediaType']) {
            case 'custom':
                return $this->getCustomMediaDOM($slide);
            case 'image':
                return $this->getImageMediaDOM($slide);
            case 'video':
                return $this->getVideoMediaDOM($slide);
            case 'audio':
                return $this->getAudioMediaDOM($slide);
            default:
                return '';
        }
    }

    /**
     * Get the custom media DOM for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the custom media.
     */
    private function getCustomMediaDOM($slide)
    {
        if (!isset($slide['customMedia'])) {
            return '';
        }
        $mediumDOM = '<img' .
        ' class="tl-media-item tl-media-image tl-media-shadow"' .
        ' src="' . $slide['customMedia'] . '"' .
        '>';
        $mediumDOM .= '</img>';
        return $mediumDOM;
    }

    /**
     * Get the image media DOM for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the image media.
     */
    private function getImageMediaDOM($slide)
    {
        if (!isset($slide['image'])) {
            return '';
        }

        $params = ['file' => $slide['image']];
        if (!empty($slide['imageAlt'])) {
            $params['alt'] = $slide['imageAlt'];
        }

        // NDLA exports may create absolute URLs, sigh
        if (str_starts_with($params['file']['path'], 'http')) {
            $mediumDOM = '<img' .
            ' class="tl-media-item tl-media-image tl-media-shadow"' .
            ' src="' . $params['file']['path'] . '"' .
            ' alt="' . $slide['imageAlt'] . '"' .
            '>';
            $mediumDOM .= '</img>';
            return $mediumDOM;
        }

        $imageDOM = '';
        $this->main->newRunnable(
            [
            'library' => 'H5P.Image 1.1', // Here H5P ignores the version
            'params' => $params,
            ],
            1,
            $imageDOM,
            false,
            []
        );

        return !empty($imageDOM) ? $imageDOM : '';
    }

    /**
     * Get the video media DOM for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the video media.
     */
    private function getVideoMediaDOM($slide)
    {
        if (!isset($slide['video'])) {
            return '';
        }

        $params = ['sources' => $slide['video']];
        $videoDOM = '';
        $this->main->newRunnable(
            [
            'library' => 'H5P.Video 1.6', // Here H5P ignores the version
            'params' => $params,
            ],
            1,
            $videoDOM,
            false,
            []
        );

        return !empty($videoDOM) ? $videoDOM : '';
    }

    /**
     * Get the audio media DOM for a slide.
     *
     * @param array $slide Slide.
     *
     * @return string HTML for the audio media.
     */
    private function getAudioMediaDOM($slide)
    {
        if (!isset($slide['audio'])) {
            return '';
        }

        $params = [
        'files' => $slide['audio'],
        'playerMode' => 'full'
        ];

        $audioDOM = '';
        $this->main->newRunnable(
            [
            'library' => 'H5P.Audio 1.5', // Here H5P ignores the version
            'params' => $params,
            ],
            1,
            $audioDOM,
            false,
            []
        );

        return !empty($audioDOM) ? $audioDOM : '';
    }
}

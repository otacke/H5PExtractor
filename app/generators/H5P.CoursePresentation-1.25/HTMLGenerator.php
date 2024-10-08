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
class HtmlGeneratorCoursePresentationMajor1Minor25 extends Generator implements GeneratorInterface
{
    // These subcontents are rendered as stacked elements, not sequentially
    const OVERFLOW_SUBCONTENTS = [
        'H5P.Dialogcards',
        'H5P.MultiMediaChoice', // Not necessarily, but likely
        'H5P.SingleChoiceSet',
        'H5P.Summary',
        'H5P.InteractiveVideo'
    ];

    const BASE_WIDTH_PX = 640;
    const BASE_FONT_SIZE_PX = 16;
    const BASE_ASPECT_RATIO = 16 / 9;

    private $overflowContentQueue;

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
        $this->overflowContentQueue = [];
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
        $container = str_replace('h5pClassName', 'h5p-course-presentation', $container);

        $container .= '<style>';
        $container .=
            '.h5p-course-presentation ' .
            '.h5p-element-button.h5p-course-presentation-overflow-button:before{content:none;}';
        $container .= '</style>';

        $slidesParams = $this->params['presentation']['slides'] ?? [];
        for ($slideIndex = 0; $slideIndex < count($slidesParams); $slideIndex++) {
            $container .= $this->buildSlideContainer([
                'hasTasks' => array_map([$this, 'hasTask'], $slidesParams),
                'presentation' => $this->params['presentation'],
                'override' => $this->params['override'],
                'l10n' => $this->params['l10n'],
                'index' => $slideIndex,
                'maxIndex' => count($slidesParams) - 1
            ]);

            if (count($this->overflowContentQueue) > 0) {
                $container .= '<div class="h5p-extractor-content-wrapper">';
                for ($contentIndex = 0; $contentIndex < count($this->overflowContentQueue); $contentIndex++) {
                    $overflowContent = $this->overflowContentQueue[$contentIndex]['content'];
                    $container .=
                        '<div class="h5p-extractor-bubble-header">' .
                            '<div class="h5p-extractor-bubble-bobble">'.
                            $contentIndex + 1 .
                            '</div>' .
                            '<div class="h5p-extractor-bubble-title">' .
                                $this->overflowContentQueue[$contentIndex]['title'] .
                            '</div>' .
                        '</div>';
                    $container .= $overflowContent;
                    $container .= '<div style="height: 1rem;"></div>';
                }
                $this->overflowContentQueue = [];
                $container .= '</div>'; // Closing .h5p-course-presentation-overflow-wrapper

                if ($slideIndex < count($slidesParams) - 1) {
                    $container .= '<div style="height: 3rem;"></div>';
                }
            }
        }

        $container .= $htmlClosing;
    }

    /**
     * Build a slide.
     *
     * @param array $params   Parameters.
     * @param int   $index    Index of the slide.
     * @param int   $maxIndex Maximum index of the slides.
     *
     * @return string The slide HTML.
     */
    private function buildSlideContainer($params)
    {
        $fullWidth = $this->getRenderWidth();
        $fullHeight = $fullWidth / self::BASE_ASPECT_RATIO;

        $properties = [
            'width' => $fullWidth . 'px',
            'height' => $fullHeight . 'px',
            'position' => 'relative',
            'display' =>  'flex',
            'flex-direction' => 'column',
            'font-size' =>
                (self::BASE_FONT_SIZE_PX * $this->getRenderWidth() / self::BASE_WIDTH_PX) . 'px'
        ];
        $style = DOMUtils::buildStyleAttribute($properties);

        $slide = '<div class="h5p-wrapper" ' . $style . '>';

        $style =
            $params['override']['activeSurface'] ?
                'style="break-inside: avoid; height: ' . $fullHeight . 'px"' :
                'style="' .
                    'break-inside: avoid;' .
                    'width: ' . $fullWidth . 'px;' .
                    'height: ' . $fullHeight * 0.9 . 'px;' .
                    'position: relative' .
                '"';

        $slide .=
            '<div ' .
                'class="h5p-box-wrapper" ' . $style .

        '>';
        $slide .=
            '<div ' .
                'class="h5p-presentation-wrapper" ' .
                'style="width: ' . $fullWidth . 'px; height: ' . $fullHeight * 0.9 . 'px;"' .
            '>';

        if ($params['presentation']['keywordListAlwaysShow']) {
            $slide .= $this->buildKeywordsWrapper([
                'slides' => $params['presentation']['slides'],
                'opacity' => $params['presentation']['keywordListOpacity'],
                'l10n' => $params['l10n'],
                'index' => $params['index'],
            ]);
        }

        $slide .= $this->buildSlide([
            'presentation' => $params['presentation'],
            'index' => $params['index'],
            'width' => $fullWidth,
            'height' => $fullHeight * 0.9,
        ]);
        $slide .= '</div>'; // Closing .h5p-presentation-wrapper
        $slide .= '</div>'; // Closing .h5p-box-wrapper

        if (!$params['override']['activeSurface']) {
            $slide .= $this->buildNavigation([
                'hasTasks' => $params['hasTasks'],
                'index' => $params['index'],
                'maxIndex' => $params['maxIndex'],
                'width' => $fullWidth,
                'height' => $fullHeight * 0.035,
            ]);

            $slide .= $this->buildFooter(
                $params['presentation']['slides'][$params['index']],
                $params['index'],
                $params['maxIndex'],
                $fullWidth,
                $fullHeight * 0.065,
            );
        }

        $slide .= '</div>'; // Closing .h5p-wrapper

        return $slide;
    }

    /**
     * Build the keywords wrapper.
     *
     * @param array $params Parameters.
     *
     * @return string The keywords wrapper HTML.
     */
    private function buildKeywordsWrapper($params = [])
    {
        $style = DOMUtils::buildStyleAttribute([
            'background-color' =>
                'rgba(251, 251, 251, ' . $params['opacity'] / 100 . ')'
        ]);

        $wrapper  = '<div class="h5p-keywords-wrapper h5p-open" ' . $style . '>';

        $wrapper .= '<ol role="menu" class="list-unstyled">';
        for ($i = 0; $i < count($params['slides']); $i++) {
            $slide = $params['slides'][$i];

            $subtitle = $params['l10n']['slide'] . ' ' . ($i + 1);
            $title = isset($slide['keywords'][0]['main']) ?
                $slide['keywords'][0]['main'] :
                $params['l10n']['noTitle'];

            $currentClass = ($i === $params['index']) ? ' h5p-current' : '';
            $wrapper .=
                '<li role="menuitem" class="' . $currentClass . '">' .
                    '<div class="h5p-keyword-subtitle">' . $subtitle . '</div>' .
                    '<div class="h5p-keyword-title">' . $title . '</div>' .
                '</li>';
        }
        $wrapper .= '</ol>';

        $wrapper .= '</div>'; // Closing .h5p-keywords-wrapper

        return $wrapper;
    }

    /**
     * Build a slide.
     *
     * @param array $params Parameters.
     *
     * @return string The slide HTML.
     */
    private function buildSlide($params = [])
    {
        $slide =
            '<div ' .
                'class="h5p-slides-wrapper" ' .
                'style="position: relative; width: ' . $params['width'] . 'px; height: ' . $params['height'] . 'px;"' .
            '>';

        $slideParams = $params['presentation']['slides'][$params['index']] ?? [];
        $slideParams['elements'] = $slideParams['elements'] ?? [];

        $backgroundImagePath =
            $slideParams['slideBackgroundSelector']['imageSlideBackground']['path'] ?? '';
        if ($backgroundImagePath === '') {
            $backgroundImagePath =
                $params['presentation']['globalBackgroundSelector']['imageGlobalBackground']['path'] ??
                '';
        }

        $backgroundColor =
            $slideParams['slideBackgroundSelector']['fillSlideBackground'] ?? '';

        if ($backgroundColor === '') {
            $backgroundColor =
                $params['presentation']['globalBackgroundSelector']['fillSlideBackground'] ??
                '';
        }

        // Required to work on older render engines
        $properties = [
            'width' => $params['width'] . 'px',
            'height' => $params['height'] . 'px',
            'transform' =>  'none',
            'background-size' => $params['width'] . 'px' . ' ' . $params['height'] . 'px',
            'display' => 'block',
        ];
        if ($backgroundColor !== '') {
            $properties['background-color'] = $backgroundColor;
        }
        if ($backgroundImagePath !== '') {
            $properties['background-image'] = 'url(' .
                $this->buildFileSource($backgroundImagePath) .
            ')';
        }

        $style = DOMUtils::buildStyleAttribute($properties);

        $hasBackground = ($backgroundImagePath !== '') ?
            ' has-background' :
            '';

        $slide .= '<div class="h5p-slide h5p-current' . $hasBackground . '" '. $style . '>';
        $slide .=
            '<div role="document" ' .
                'style="position: relative; width: ' . $params['width'] . 'px; height: ' . $params['height'] . 'px"' .
            '>';
        foreach ($slideParams['elements'] as $element) {
            $slide .= $this->buildElement($element, $params['width'], $params['height']);
        }

        $slide .= '</div>'; // Closing role=document

        $slide .= '</div>'; // Closing .h5p-slide

        $slide .= '</div>'; // Closing .h5p-slides-wrapper

        return $slide;
    }

    /**
     * Build an element.
     *
     * @param array $elementParams Parameters.
     * @param int   $width         Width of the slide.
     * @param int   $height        Height of the slide.
     *
     * @return string The element HTML.
     */
    private function buildElement($elementParams, $width, $height)
    {
        $machineName = (isset($elementParams['action'])) ?
            explode(' ', $elementParams['action']['library'])[0] :
            '';

        /*
         * For print, content that consists of multiple slides, pages, etc. that
         * would normally be accessed sequentially will be rendered as a single
         * stacked sequence of DOM elements. That rendering will not fit into
         * the container, so it will be treated as if it was supposed to be
         * rendered as a button expected to b clicked and then displayed.
         * underneath the slide.
         */
        $isOverflowContent = in_array($machineName, self::OVERFLOW_SUBCONTENTS);

        $buttonWrapperClass = (
            $isOverflowContent || isset($elementParams['displayAsButton']) && $elementParams['displayAsButton']
        ) ?
            ' h5p-element-button-wrapper' :
            '';

        $buttonSize = $elementParams['buttonSize'] ?? 'big';
        $buttonSizeClass = ' h5p-element-button-' . $buttonSize;

        $transparencyClass = ($elementParams['backgroundOpacity'] === 0) ?
            ' h5p-transparent' :
            '';

        $properties = [
            'left' => $elementParams['x'] / 100 * $width . 'px',
            'top' => $elementParams['y'] / 100 * $height . 'px',
            'width' => $elementParams['width'] / 100 * $width . 'px',
            'height' => $elementParams['height'] / 100 * $height . 'px',
        ];
        $style = DOMUtils::buildStyleAttribute($properties);

        $element =
            '<div ' .
                'class="h5p-element ' . $buttonSizeClass . $buttonWrapperClass. $transparencyClass . '"' .
                $style .
            '>';

        if ($buttonWrapperClass !== '') {
            $innerContainer = '<div class="h5pClassName" style="">';
            $this->main->newRunnable(
                [
                    'library' => $elementParams['action']['library'],
                    'params' => $elementParams['action']['params'],
                ],
                1,
                $innerContainer,
                false,
                [
                    'metadata' => $elementParams['action']['metadata'],
                ]
            );
            $this->overflowContentQueue[] = [
                'title' => ($elementParams['action']['metadata']['title'] ?? $machineName),
                'content' => $innerContainer
            ];

            // TODO: Why does Pressbooks not display this in print?
            $element .=
            '<div ' .
                'class="h5p-element-button' . $buttonSizeClass . ' ' . 'h5p-course-presentation-overflow-button"' .
            '>' . count($this->overflowContentQueue) . '</div>';
        } elseif (isset($elementParams['action'])) {
            if (!$isOverflowContent) {
                $element .=
                '<div ' .
                    'class="h5p-element-outer ' .
                        $this->getLibraryTypePmz($machineName) . '-outer-element' .
                    '" ' .
                    'style="background: rgba(255, 255, 255, ' . $elementParams['backgroundOpacity'] / 100 .');"' .
                '>';
                $innerContainer = '<div class="h5p-element-inner h5pClassName" style="">';
                $this->main->newRunnable(
                    [
                        'library' => $elementParams['action']['library'],
                        'params' => $elementParams['action']['params'],
                    ],
                    1,
                    $innerContainer,
                    false,
                    [
                        'metadata' => $elementParams['action']['metadata'] ?? [],
                    ]
                );
                $element .= $innerContainer;

                $element .= '</div>'; // Closing .h5p-element-outer
            }
        }

        // We do not need to handle GoToSlide

        $element .= '</div>'; // Closing .h5p-element

        return $element;
    }

    /**
     * Get the library type in PMZ format.
     * Direct port of JavaScript function from CoursePresentation.
     *
     * @param string $machineName Machine name.
     *
     * @return string The library type in PMZ format.
     */
    private function getLibraryTypePmz($machineName)
    {
        return $this->kebabCase(strtolower($machineName));
    }

    /**
     * Convert a string to kebab case.
     * Direct port of JavaScript function from CoursePresentation.
     *
     * @param string $string The string to convert.
     *
     * @return string The kebab-cased string.
     */
    private function kebabCase($string)
    {
        return preg_replace('/[\W]/', '-', $string);
    }

    /**
     * Check if the slide has at least one scored subcontent.
     *
     * @param array $params Parameters.
     *
     * @return bool True if the slide has a scored subcontent, false otherwise.
     */
    private function hasTask($params)
    {
        $elements = $params['elements'] ?? [];
        foreach ($elements as $element) {
            $versionedMachineName = isset($element['action']) ?
                $element['action']['library'] ?? '' :
                '';

            if ($this->isScoredContentType($versionedMachineName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Build the navigation for the slide.
     *
     * @param array $params   Parameters.
     *
     * @return string The navigation HTML.
     */
    private function buildNavigation($params)
    {
        $width = $params['width'];
        $height = $params['height'];

        // Lots of unpleasant customization, because neither grid nor flex render properly in Pressbooks
        $navigation = '<nav class="h5p-cp-navigation" style="width:' . $width .'px;height:' . $height . 'px">';

        $navigation .= '<div class="h5p-progressbar" style="display:block;width:' . $width .'px;height:' . $height . 'px">';

        for ($i = 0; $i <= $params['maxIndex']; $i++) {
            $selected = ($i === $params['index']) ?
                ' h5p-progressbar-part-selected' :
                '';
            $show = ($i <= $params['index']) ?
                ' h5p-progressbar-part-show' :
                '';
            $navigation .=
                '<div ' .
                    'class="h5p-progressbar-part' . $show . $selected . '"' .
                    'style="float:left;width:' . $width / ($params['maxIndex'] + 1) - 1 . 'px;height:' . $height . 'px"' .
                '>';

            if ($params['hasTasks'][$i]) {
                $navigation .= '<a href="#">';
                $navigation .=
                    '<div class="h5p-progressbar-part-has-task"></div>';
                $navigation .= '</a>';
            }

            $navigation .= '</div>';
        }

        $navigation .= '</div>';

        $navigation .= '</nav>'; // Closing .h5p-cp-navigation

        return $navigation;
    }

    /**
     * Build the footer for the slide.
     *
     * @param array $params   Parameters.
     * @param int   $index    Index of the slide.
     * @param int   $maxIndex Maximum index of the slides.
     * @param int   $width    Width of the footer in px
     * @param int   $height   Height of the footer in px
     *
     * @return string The footer HTML.
     */
    private function buildFooter($params = [], $index = 0, $maxIndex = 0, $width = 0, $height = 0)
    {
        // Lots of unpleasant customization, because neither grid nor flex render properly in Pressbooks
        $footer = '<div class="h5p-footer" style="display:block;width:' . $width . 'px;height: ' . $height . 'px">';

        // LEFT (Title)
        $footer .= '<div class="h5p-footer-left-adjusted" style="float:left;width:' . $width * 0.4 . 'px; height:' . $height .'px">';

        $title = '';
        if (isset($params['keywords']) &&
            isset($params['keywords'][0]) &&
            isset($params['keywords'][0]['main'])
        ) {
            $title = $params['keywords'][0]['main'];
        }

        $footer .=
            '<div class="h5p-footer-button h5p-footer-toggle-keywords">' .
                '<span class="h5p-icon-menu"></span>' .
                '<span class="current-slide-title">' . $title . '</span>' .
            '</div>';

        $footer .= '</div>'; // Closing .h5p-footer-left-adjusted

        // CENTER
        $footer .= '<div class="h5p-footer-center-adjusted" style="float:left;width:' . $width * 0.2 . 'px; height:' . $height .'px">';

        $disabled = $index === 0 ? 'true' : 'false';
        $footer .=
            '<div ' .
                'class="h5p-footer-button h5p-footer-previous-slide"' .
                ' aria-disabled="' . $disabled . '"' .
            '></div>';

        $footer .= '<div class="h5p-footer-slide-count">';

        $footer .=
            '<div class="h5p-footer-slide-count-current">' .
                $index + 1 .
            '</div>';
        $footer .= '<div class="h5p-footer-slide-count-delimiter">/</div>';
        $footer .=
            '<div class="h5p-footer-slide-count-max">' .
                $maxIndex + 1 .
            '</div>';

        $footer .= '</div>';

        $disabled = $index === $maxIndex ? 'true' : 'false';
        $footer .=
            '<div ' .
                'class="h5p-footer-button h5p-footer-next-slide"' .
                ' aria-disabled="' . $disabled . '"' .
            '></div>';
        $footer .= '</div>'; // Closing .h5p-footer-center-adjusted

        // RIGHT (toolbar)
        $footer .= '<div class="h5p-footer-right-adjusted" style="float:left;width:' . $width * 0.4 . 'px; height:' . $height .'px">';
        $footer .= '</div>';

        $footer .= '</div>'; // Closing h5p-footer

        return $footer;
    }
}

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
 * Class for generating HTML for H5P.ImageHotspots-1.10.
 * TODO: Popup content
 * TODO: Custom icons
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorImageHotspotsMajor1Minor10 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param HTMLGeneratorMain $main The main HTML generator.
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
        $this->params['hotspots'] = $this->params['hotspots'] ?? [];

        // Filter out hotspots without content
        $this->params['hotspots'] = array_filter(
            $this->params['hotspots'],
            function ($hotspot) {
                $hotspot['content'] = $hotspot['content'] ?? [];
                $hotspot['content'] = array_filter(
                    $hotspot['content'],
                    function ($content) {
                        return isset($content['library']);
                    }
                );
                return count($hotspot['content']) > 0;
            }
        );

        // Sort hotspots by position
        usort(
            $this->params['hotspots'],
            function ($a, $b) {
                // Sanity checks, move data to the back if invalid
                $firstIsValid = $a['position'] && $a['position']['x'] && $a['position']['y'];
                $secondIsValid = $b['position'] && $b['position']['x'] && $b['position']['y'];
                if (!$firstIsValid) {
                    return 1;
                }

                if (!$secondIsValid) {
                    return -1;
                }

                // Order top-to-bottom, left-to-right
                if ($a['position']['y'] !== $b['position']['y']) {
                    return $a['position']['y'] < $b['position']['y'] ? -1 : 1;
                } else {
                    // a and b y position is equal, sort on x
                    return $a['position']['x'] < $b['position']['x'] ? -1 : 1;
                }
            }
        );

        $imageSrc = $this->buildFileSource($this->params['image']['path']);

        $defaultFontSize = 24;

        if ($this->main->scope === 'all') {
            $this->params['iconType'] = 'numbers';
        }
        $this->params['iconType'] = $this->params['iconType'] ?? 'icon';

        $this->params['icon'] = $this->params['icon'] ?? 'plus';

        $color = $this->params['color'] ?? '#981d99';

        if ($this->params['iconType'] === 'icon') {
            $iconClass = ' h5p-image-hotspot-' . $this->params['icon'];
        } elseif ($this->params['iconType'] === 'image') {
            $iconClass = '';
        } else {
            $iconClass = ' h5p-image-hotspot-number';
        }

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-image-hotspots', $container);

        $containerTemplate = $container;
        $container = '';

        if ($this->params['iconType'] === 'image') {
            $iconImage = $this->buildFileSource($this->params['iconImage']['path'] ?? '');
        }

        for ($i = -1; $i < count($this->params['hotspots'] ?? []); $i++) {
            $container .= $this->buildOverview(
                $containerTemplate,
                [
                    'imageSrc' => $imageSrc,
                    'defaultFontSize' => $defaultFontSize,
                    'color' => $color,
                    'iconClass' => $iconClass,
                    'iconImage' => $iconImage ?? '',
                ],
                $i
            );
        }
    }

    /**
     * Build the overview for the image hotspots.
     * @param string $containerTemplate The template for the container.
     * @param array $params The parameters for the container.
     * @param int $index The index of the hotspot to focus on, -1 for none.
     */
    private function buildOverview($containerTemplate, $params, $index)
    {
        $htmlClosing = TextUtils::getClosingTag($containerTemplate);

        $container = $containerTemplate;

        $container .= '<div class="h5p-image-hotspots-container"' .
            ' style="width: ' . $this->getRenderWidth() . 'px; ' .
            'font-size: ' . $params['defaultFontSize'] . 'px;' .
            '">';

        if ($this->main->scope !== 'all' || $index === -1) {
            $container .= '<img ' .
            'src="' . $params['imageSrc'] . '" ' .
            'class="h5p-image-hotspots-image" ' .
            'style="width: ' . $this->getRenderWidth() . 'px"/>';

            $hotspotsCount = count($this->params['hotspots'] ?? []);
            for ($i = 0; $i < $hotspotsCount; $i++) {
                $hotspot = $this->params['hotspots'][$i];
                $positioning =
                    ($hotspot['position']['legacyPositioning'] ?? false) ?
                    ' legacy-positioning' :
                    '';

                $focusStyle = ($index > 0 && $index === $i) ?
                    ' transform: scale(1.5);' :
                    '';

                if ($this->params['iconType'] === 'icon') {
                    $container .=
                        '<button ' .
                        'class="h5p-image-hotspot' . $params['iconClass'] . $positioning . '" ' .
                        'style="' .
                            'top: ' . $hotspot['position']['y'] . '%; ' .
                            'left: ' . $hotspot['position']['x'] .  '%; ' .
                            'color: ' . $params['color'] . ';' .
                            $focusStyle .
                        '"></button>';
                } elseif ($this->params['iconType'] === 'image') {
                    $container .= '<img class="h5p-image-hotspot ' . $positioning . '" ';
                    $container .= 'src="' . $params['iconImage'] . '" ';
                    $container .= 'style="' .
                        'top: ' . $hotspot['position']['y'] . '%; ' .
                        'left: ' . $hotspot['position']['x'] .  '%; ' .
                        'color: ' . $params['color'] . ';' .
                        $focusStyle . '"';
                    $container .= '/>';
                } else {
                    $container .=
                        '<style>' .
                        '.h5p-image-hotspots-container { counter-reset: hotspot; }' .
                        '.h5p-image-hotspot-number {' .
                            'background-color: #000000;' .
                            'border: 3px solid #ffffff;' .
                        '}' .
                        '.h5p-image-hotspot-number::before {' .
                            'color: #ffffff; ' .
                            'content: counter(hotspot); ' .
                            'counter-increment: hotspot;' .
                            'font-size: 75%;' .
                        '}' .
                        '</style>';
                    $container .=
                        '<button ' .
                        'class="h5p-image-hotspot' . $params['iconClass'] . $positioning . '" ' .
                        'style="' .
                            'top: ' . $hotspot['position']['y'] . '%; ' .
                            'left: ' . $hotspot['position']['x'] .  '%; ' .
                            'scale: 1.5; ' .
                            $focusStyle .
                        '"></button>';
                }
            }
        }

        if ($this->main->scope !== 'all') {
            $overlayHotspot = $index >= 0 ? $this->params['hotspots'][$index] : null;
            if (isset($overlayHotspot)) {
                $width = $this->getRenderWidth();
                $pointerWidthInPercent = 1.55;
                $hotspotWidth = (1.1666667 * 1.2 * $params['defaultFontSize']) / $width * 100;

                $machineNames = array_map(function ($content) {
                    return explode(' ', $content['library'])[0];
                }, $overlayHotspot['content'] ?? []);

                /*
                 * This is a little different then the original code, but this feels
                 * like it was intended to work.
                 */
                if (in_array('H5P.Video', $machineNames)) {
                    $classnamePopup = ' h5p-video';
                } elseif (in_array('H5P.Image', $machineNames)) {
                    $classnamePopup = ' h5p-image';
                } else {
                    $classnamePopup = ' h5p-text';
                }

                $hasHeader = ($overlayHotspot['header'] ?? '') !== '';

                if ($overlayHotspot['alwaysFullscreen']) {
                    $toTheLeft = false;
                    $popupLeft = 0;
                    $popupWidth = 100;

                    $classnamePopup .= ' fullscreen-popup';
                } else {
                    $toTheLeft = $overlayHotspot['position']['x'] > 50;
                    $popupLeft = $toTheLeft ? 0 :
                        $overlayHotspot['position']['x'] + $hotspotWidth + $pointerWidthInPercent;
                    $popupWidth = $toTheLeft ?
                        $overlayHotspot['position']['x'] - $hotspotWidth - $pointerWidthInPercent :
                        100 - $popupLeft;
                }

                if ($hasHeader) {
                    $classnamePopup .= ' h5p-image-hotspot-has-header';
                }

                $container .= '<div class="h5p-image-hotspots-overlay visible">';

                // Popup
                $left = ($toTheLeft === true) ? '0%' : (100 - $popupWidth) . '%';
                $top = $popupWidth === 100 ?
                    0 :
                    max(0, $overlayHotspot['position']['y']);
                $transform = $popupWidth === 100 ?
                    '' :
                    'translateY(-50%)';

                $container .=
                    '<div ' .
                        'class="h5p-image-hotspot-popup' . $classnamePopup . '" ' .
                        'style="' .
                            'height: auto; ' .
                            'left: ' . $left . '; ' .
                            'pointer-events: none; ' .
                            'top: ' . $top . '%; ' .
                            'transform: ' . $transform . '; ' .
                            'width: ' . $popupWidth . '%;' .
                        '"' .
                    '>';

                $classnameContent = !$hasHeader ?
                    'h5p-image-hotspot-popup-content-no-header' :
                    '';

                $container .= '<div class="h5p-image-hotspot-popup-content ' .
                    $classnameContent . '" style="max-height: 100%;">';
                if ($hasHeader) {
                    $container .= '<div class="h5p-image-hotspot-popup-header">' .
                        $overlayHotspot['header'] . '</div>';
                }

                $container .= '<div class="h5p-image-hotspot-popup-body">';

                foreach ($overlayHotspot['content'] as $content) {
                    $subcontentContainer = '<div class="h5p-image-hotspot-popup-body-fraction h5pClassName" style="">';
                    $this->main->newRunnable(
                        [
                            'library' => $content['library'],
                            'params' => $content['params'],
                        ],
                        1,
                        $subcontentContainer,
                        false,
                        [
                            'metadata' => $content['metadata'] ?? [],
                        ]
                    );
                    $container .= $subcontentContainer;
                }

                $container .= '</div>';

                // Closing h5p-image-hotspot-popup-content
                $container .= '</div>';

                // Close button
                $container .= '<button class="h5p-image-hotspot-close-popup-button"></button>';

                // Closing h5p-image-hotspot-popup
                $container .= '</div>';

                // Pointer
                $positionClass = $toTheLeft ? ' to-the-left' : ' to-the-right';
                $left = $toTheLeft ?
                    $popupWidth . '%' :
                    $popupLeft . '%';
                $container .= '<div class="h5p-image-hotspot-popup-pointer visible' .
                    $positionClass . $positioning . '" style="top: ' .
                    $overlayHotspot['position']['y'] . '%; left: ' . $left .
                    '">';
                $container .= '</div>';

                // Closing h5p-image-hotspots-overlay
                $container .= '</div>';
            }
        } else {
            $overlayHotspot = $index >= 0 ? $this->params['hotspots'][$index] : null;

            // Closing h5p-image-hotspots-container
            $container .= '</div>';

            if ($index > -1) {
                $container .= '<div class="h5p-extractor-content-wrapper">';
                $container .=
                    '<div class="h5p-extractor-bubble-header">' .
                        '<div class="h5p-extractor-bubble-bobble">' .
                            $index + 1 .
                        '</div>' .
                        '<div class="h5p-extractor-bubble-title">' .
                            ($overlayHotspot['header'] ?? '') .
                        '</div>' .
                    '</div>';

                foreach ($overlayHotspot['content'] as $content) {
                    $subcontentContainer =
                        '<div class="h5p-image-hotspot-popup-body-fraction h5pClassName" style="">';

                    $this->main->newRunnable(
                        [
                            'library' => $content['library'],
                            'params' => $content['params'],
                        ],
                        1,
                        $subcontentContainer,
                        false,
                        [
                            'metadata' => $content['metadata'] ?? [],
                        ]
                    );
                    $container .= $subcontentContainer;
                }

                // Closing h5p-extractor-popup
                $container .= '</div>';
            }
        }

        if ($this->main->scope !== 'all') {
            // Closing h5p-image-hotspots-container
            $container .= '</div>';
        }

        $container .= $htmlClosing;

        return $container;
    }
}

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
 * Class for generating HTML for H5P.AudioRecorder-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorAudioRecorderMajor1Minor0 extends Generator implements GeneratorInterface
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', '', $container);
        /*
         * VUE component, sets all CSS as style attribute directly. WTF?!
         * Putting this in custom CSS file.
         */
        $container .=
            '<style>' .
                file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'h5p-audio-recorder.css') .
            '</style>';

        $container .= '<div class="h5p-audio-recorder">';

        $container .= '<div class="h5p-audio-recorder-view">';

        $container .=
            '<div class="recording-indicator-wrapper">' .
                '<div class="hidden h5p-audio-recorder-vu-meter" style="transform: scale(0.7);"></div>' .
                '<div class="fa-microphone"></div>' .
            '</div>';

        $container .=
            '<div class="title">' .
                $this->params['title'] .
            '</div>';

        $container .=
            '<div role="status" class="ready">' .
                $this->params['l10n']['statusReadyToRecord'] .
            '</div>';

        $container .=
            '<div class="audio-recorder-timer">' .
                '00:00' .
            '</div>';

        $container .=
         '<div class="button-row">' .
            '<div class="button-row-double">' .
                '<button class="button record">' .
                    '<span class="fa-circle"></span>' .
                    $this->params['l10n']['recordAnswer'] .
                '</button>' .
            '</div>' .
            '<span class="button-row-left"></span>' .
            '<span class="button-row-right"></span>' .
        '</div>';

        $container .= '</div>'; // Closing h5p-audio-recorder-view
        $container .= '</div>'; // Closing h5p-audio-recorder

        $container .= $htmlClosing;
    }
}

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
 * Class for generating HTML for H5P.Audio-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorAudioMajor1Minor5 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-audio-wrapper', $container);

        if ($this->params['playerMode'] === 'minimalistic') {
            $container .= '<div class="h5p-audio-inner">';
            $container .= '<button';
            if ($this->params['fitToWrapper'] !== false) {
                $container .= ' style="width: 100%; height: 100%;"';
            }
            $container .= ' class="h5p-audio-minimal-button h5p-audio-minimal-play"';
            $container .= '/>';
            $container .= '</div>';
        } elseif ($this->params['playerMode'] === 'full') {
            $imagePath = __DIR__ . '/../../assets/placeholder-audio.svg';

            $container .= '<img' .
                ' src="' . FileUtils::fileToBase64($imagePath) . '"' .
                ' style="width: 100%;"' .
                '>';
        } elseif ($this->params['playerMode'] === 'transparent') {
            // Intenionally left empty
        }

        $container .= $htmlClosing;
    }
}

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
class HtmlGeneratorAudioMajor1Minor5 extends Generator implements HtmlGeneratorInterface
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
    public function get($params)
    {
        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        if ($params['container'] === '') {
            $htmlClosing = '';
        } else {
            $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';
        }

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $html = str_replace('h5pClassName', 'h5p-audio-wrapper', $html);

        if ($contentParams['playerMode'] === 'minimalistic') {
            $html .= '<div class="h5p-audio-inner">';
            $html .= '<button';
            if ($contentParams['fitToWrapper'] !== false) {
                $html .= ' style="width: 100%; height: 100%;"';
            }
            $html .= ' class="h5p-audio-minimal-button h5p-audio-minimal-play"';
            $html .= '/>';
            $html .= '</div>';
        } elseif ($contentParams['playerMode'] === 'full') {
            $imagePath = __DIR__ . '/../../assets/placeholder-audio.svg';

            $html .= '<img' .
                ' src="' . FileUtils::fileToBase64($imagePath) . '"' .
                ' style="width: 100%;"' .
                '>';
        } elseif ($contentParams['playerMode'] === 'transparent') {
            // Intenionally left empty
        }

        $html .= $htmlClosing;

        return $html;
    }
}

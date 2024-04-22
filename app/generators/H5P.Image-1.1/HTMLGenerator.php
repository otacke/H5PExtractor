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
 * Class for generating HTML for H5P.Image-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorImageMajor1Minor1 implements HtmlGeneratorInterface
{
    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array             $params Parameters.
     * @param HtmlGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
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
        $html = str_replace('h5pClassName', 'h5p-image', $html);

        if (isset($params['params']['file']['path'])) {
            $imagePath = $main->h5pFileHandler->getBaseDirectory() . '/' .
                $main->h5pFileHandler->getFilesDirectory() . '/' .
                'content' . '/' . $params['params']['file']['path'];
        }

        $alt = '';
        if (isset($contentParams) && !empty($contentParams['alt'])) {
            $alt = $contentParams['alt'];
        } elseif (!empty($metadata['a11yTitle'])) {
            $alt = $metadata['a11yTitle'];
        } elseif (!empty($metadata['title'])) {
            $alt = $metadata['title'];
        }

        $html .= '<img';
        $html .= ' width="100%"';
        $html .= ' height="100%"';

        if (isset($imagePath)) {
            $html .= ' src="' . FileUtils::fileToBase64($imagePath) . '"';
        } else {
            $html .= ' class="h5p-placeholder"';
        }

        $html .= ' alt="' . $alt .  '"';
        $html .= ' />';

        $html .= $htmlClosing;

        return $html;
    }
}

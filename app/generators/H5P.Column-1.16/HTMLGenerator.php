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
 * Class for generating HTML for H5P.Column-1.16.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorColumnMajor1Minor16 implements HtmlGeneratorInterface
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
        include_once __DIR__ . '/Utils.php';

        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $html = str_replace('h5pClassName', 'h5p-column', $html);

        $html .= '<div>';

        $this->previousHasMargin = null;

        if (isset($contentParams['content'])) {
            foreach ($contentParams['content'] as $content) {
                $libraryContent = $content['content'];
                $version = explode(' ', $libraryContent['library'])[1];

                $container = '<div class="h5p-column-content h5pClassName">';

                $separatorResults = UtilsColumnMajor1Minor16::addSeparator(
                    explode(' ', $libraryContent['library'])[0],
                    $content['useSeparator'],
                    $this->previousHasMargin
                );
                $this->previousHasMargin = $separatorResults['previousHasMargin'];
                $html .= $separatorResults['separator'];

                $html .= $main->createContent(
                    array(
                        'machineName' => explode(' ', $libraryContent['library'])[0],
                        'majorVersion' => explode('.', $version)[0],
                        'minorVersion' => explode('.', $version)[1],
                        'params' => $libraryContent['params'],
                        'metadata' => $libraryContent['metadata'],
                        'container' => ''
                    )
                );
            }
        }

        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

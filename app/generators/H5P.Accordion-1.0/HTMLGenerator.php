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
 * Class for generating HTML for H5P.Accordion-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorAccordionMajor1Minor0 implements HtmlGeneratorInterface
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

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $html = str_replace('h5pClassName', 'h5p-accordion', $html);

        // Actual content
        $randomId = uniqid();

        if (isset($contentParams['panels'])) {
            $panelCount = count($contentParams['panels']);
            for ($panelIndex = 0; $panelIndex < $panelCount; $panelIndex++) {
                $panelData = $contentParams['panels'][$panelIndex];

                $html .= '<h2';
                $html .= ' id="h5p-panel-link-' .$randomId . '-' . $panelIndex .
                    '" class="h5p-panel-title h5p-panel-expanded">';
                $html .= '<button';
                $html .= ' class="h5p-panel-button" tabindex="0"';
                $html .= ' aria-expanded="true"';
                $html .= ' aria-controls="h5p-panel-content-0-' .
                    $panelIndex . '">';
                $html .= $panelData['title'];
                $html .= '</button>';
                $html .= '</h2>';

                $content = $panelData['content'];
                $version = explode(' ', $content['library'])[1];

                $container  = '<div';
                $container .= ' id="h5p-panel-content-' . $randomId . '-' .
                    $panelIndex . '" style="display: block;"';
                $container .=
                    ' class="h5p-panel-content h5pClassName"';
                $container .=
                    ' role="region" aria-labelledby="h5p-panel-link-0-' .
                        $panelIndex . '" aria-hidden="false">';

                $html .= $main->createContent(
                    array(
                        'machineName' => explode(' ', $content['library'])[0],
                        'majorVersion' => explode('.', $version)[0],
                        'minorVersion' => explode('.', $version)[1],
                        'params' => $content['params'],
                        'container' => $container
                    )
                );
            }
        }

        $html .= $htmlClosing;

        return $html;
    }
}

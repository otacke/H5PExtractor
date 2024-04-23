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
class HtmlGeneratorAccordionMajor1Minor0 extends Generator implements GeneratorInterface
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
        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $container, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-accordion', $container);

        // Actual content
        $randomId = uniqid();

        if (isset($this->params['panels'])) {
            $panelCount = count($this->params['panels']);
            for ($panelIndex = 0; $panelIndex < $panelCount; $panelIndex++) {
                $panelData = $this->params['panels'][$panelIndex];

                $container .= '<h2';
                $container .= ' id="h5p-panel-link-' .$randomId . '-' . $panelIndex .
                    '" class="h5p-panel-title h5p-panel-expanded">';
                $container .= '<button';
                $container .= ' class="h5p-panel-button" tabindex="0"';
                $container .= ' aria-expanded="true"';
                $container .= ' aria-controls="h5p-panel-content-0-' .
                    $panelIndex . '">';
                $container .= $panelData['title'];
                $container .= '</button>';
                $container .= '</h2>';

                $content = $panelData['content'];

                $innerContainer  = '<div';
                $innerContainer .= ' id="h5p-panel-content-' . $randomId . '-' .
                    $panelIndex . '" style="display: block;"';
                $innerContainer .=
                    ' class="h5p-panel-content h5pClassName"';
                $innerContainer .=
                    ' role="region" aria-labelledby="h5p-panel-link-0-' .
                        $panelIndex . '" aria-hidden="false">';

                $this->main->newRunnable(
                    [
                        'library' => $content['library'],
                        'params' => $content['params'],
                    ],
                    1,
                    $innerContainer,
                    false,
                    [
                        'metadata' => $content['metadata'],
                    ]
                );

                $container .= $innerContainer;
            }
        }

        $container .= $htmlClosing;
    }
}

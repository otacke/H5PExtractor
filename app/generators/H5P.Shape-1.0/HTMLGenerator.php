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
 * Class for generating HTML for H5P.Shape-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorShapeMajor1Minor0 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-shape', $container);

        $props = (
            $this->params['type'] === 'vertical-line' ||
            $this->params['type'] === 'horizontal-line'
        ) ?
            $this->params['line'] :
            $this->params['shape'];

        // Directly taken from JavaScript implementation
        $borderWidth = ($props['borderWidth'] * 0.0835) . 'em';

        $cssProperties = [
            'border-color' => $props['borderColor'],
        ];

        if ($this->params['type'] === 'vertical-line') {
            $cssProperties['border-left-width'] = $borderWidth;
            $cssProperties['border-left-style'] = $props['borderStyle'];
        } elseif ($this->params['type'] === 'horizontal-line') {
            $cssProperties['border-top-width'] = $borderWidth;
            $cssProperties['border-top-style'] = $props['borderStyle'];
        } else {
            $cssProperties['background-color'] = $props['fillColor'];
            $cssProperties['border-style'] = $props['borderStyle'];
            $cssProperties['border-width'] = $borderWidth;
        }

        if ($this->params['type'] === 'rectangle') {
            $cssProperties['border-radius'] = $props['borderRadius'] * 0.25 . 'em';
        }

        $style = DOMUtils::buildStyleAttribute($cssProperties);

        $container .=
            '<div ' .
                'class="h5p-shape-element h5p-shape-' . $this->params['type'] . '"' .
                $style .
            '>' .
            '</div>';

        $container .= $htmlClosing;
    }
}

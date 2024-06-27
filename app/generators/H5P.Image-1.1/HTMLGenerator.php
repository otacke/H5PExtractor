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
class HtmlGeneratorImageMajor1Minor1 extends Generator implements GeneratorInterface
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
        $container = str_replace('h5pClassName', 'h5p-image', $container);

        if (isset($this->params['file']['path'])) {
            $imagePath = $this->main->h5pFileHandler->getBaseDirectory() . '/' .
                $this->main->h5pFileHandler->getFilesDirectory() . '/' .
                'content' . '/' . $this->params['file']['path'];
        }

        $alt = '';
        if (!empty($this->params['alt'])) {
            $alt = $this->params['alt'];
        } elseif (!empty($this->extras['metadata']['a11yTitle'])) {
            $alt = $this->extras['metadata']['a11yTitle'];
        } elseif (!empty($this->extras['metadata']['title'])) {
            $alt = $this->extras['metadata']['title'];
        }

        $container .= '<img';
        $container .= ' width="100%"';
        $container .= ' height="100%"';

        if (isset($imagePath)) {
            $container .= ' src="' . FileUtils::fileToBase64($imagePath) . '"';
            $container .= ' alt="' . $alt .  '"';
        } else {
            $container .= ' class="h5p-placeholder"';
        }

        $container .= ' />';

        $container .= $htmlClosing;
    }
}

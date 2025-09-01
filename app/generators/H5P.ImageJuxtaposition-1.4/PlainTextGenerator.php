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
 * Class for generating HTML for H5P.ImageJuxtaposition-1.4.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorImageJuxtapositionMajor1Minor4 extends Generator implements GeneratorInterface
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
        if (!empty($this->params['title'])) {
            $container .= TextUtils::htmlToText($this->params['title']);
        }

        $container .= "##1\n";
        $imageBeforeParams = $this->params['imageBefore']['imageBefore'];
        $imageContainer = '';
            $this->main->newRunnable(
                [
                    'library' => $imageBeforeParams['library'],
                    'params' => $imageBeforeParams['params'],
                ],
                1,
                $imageContainer,
                false,
                [
                    'metadata' => isset($imageBeforeParams['metadata']) ? $imageBeforeParams['metadata'] : [],
                ]
            );
        $container .= $imageContainer . "\n";
        if (isset($this->params['imageBefore']['labelBefore'])) {
            $container .= TextUtils::htmlToText($this->params['imageBefore']['labelBefore']);
        }

        $container .= "\n\n";

        $container .= "##2\n";
        $imageAfterParams = $this->params['imageAfter']['imageAfter'];
        $imageContainer = '';
            $this->main->newRunnable(
                [
                    'library' => $imageAfterParams['library'],
                    'params' => $imageAfterParams['params'],
                ],
                1,
                $imageContainer,
                false,
                [
                    'metadata' => isset($imageAfterParams['metadata']) ? $imageAfterParams['metadata'] : [],
                ]
            );
        $container .= $imageContainer . "\n";
        if (isset($this->params['imageAfter']['labelAfter'])) {
            $container .= TextUtils::htmlToText($this->params['imageAfter']['labelAfter']);
        }

        $container = trim($container);
    }
}

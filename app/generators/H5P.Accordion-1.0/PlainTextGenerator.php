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
 * Class for generating plain text for H5P.Accordion-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorAccordionMajor1Minor0 extends Generator implements GeneratorInterface
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
     * Create the output for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        if (isset($this->params['panels'])) {
            $panelCount = count($this->params['panels']);
            for ($panelIndex = 0; $panelIndex < $panelCount; $panelIndex++) {
                $panelData = $this->params['panels'][$panelIndex];

                $container .= '**' . $panelData['title'] . "**\n\n";
                $content = $panelData['content'];
                $version = explode(' ', $content['library'])[1];

                $innerContainer = '';
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
    }
}

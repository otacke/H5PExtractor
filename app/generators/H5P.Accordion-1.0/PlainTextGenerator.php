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
class PlainTextGeneratorAccordionMajor1Minor0 implements PlainTextGeneratorInterface
{
    /**
     * Create the output for the given H5P content type.
     *
     * @param array                  $params Parameters.
     * @param PlainTextGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
    {
        $contentParams = $params['params'];

        $text = $params['container'];


        if (isset($contentParams['panels'])) {
            $panelCount = count($contentParams['panels']);
            for ($panelIndex = 0; $panelIndex < $panelCount; $panelIndex++) {
                $panelData = $contentParams['panels'][$panelIndex];

                $text .= '**' . $panelData['title'] . "**\n\n";
                $content = $panelData['content'];
                $version = explode(' ', $content['library'])[1];

                $text .= $main->createContent(
                    array(
                        'machineName' => explode(' ', $content['library'])[0],
                        'majorVersion' => explode('.', $version)[0],
                        'minorVersion' => explode('.', $version)[1],
                        'params' => $content['params'],
                        'container' => ''
                    )
                );
            }
        }

        return $text;
    }
}

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
class PlainTextGeneratorColumnMajor1Minor16 implements PlainTextGeneratorInterface
{
    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array                  $params Parameters.
     * @param PlainTextGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
    {
        include_once __DIR__ . '/Utils.php';

        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['content'])) {
            foreach ($contentParams['content'] as $content) {
                $libraryContent = $content['content'];
                $version = explode(' ', $libraryContent['library'])[1];

                $separatorResults = UtilsColumnMajor1Minor16::addSeparator(
                    explode(' ', $libraryContent['library'])[0],
                    $content['useSeparator'],
                    $this->previousHasMargin ?? null
                );
                $this->previousHasMargin = $separatorResults['previousHasMargin'];
                $text .= ($separatorResults['separator'] !== '') ?
                    '---' . "\n\n" :
                    '';

                $text .= $main->createContent(
                    array(
                        'machineName' => explode(' ', $libraryContent['library'])[0],
                        'majorVersion' => explode('.', $version)[0],
                        'minorVersion' => explode('.', $version)[1],
                        'params' => $libraryContent['params'],
                        'container' => ''
                    )
                );
            }
        }

        return trim($text);
    }
}

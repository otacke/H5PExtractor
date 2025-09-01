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
 * Class for generating HTML for H5P.StandardPage-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorStandardPageMajor1Minor5 extends Generator implements GeneratorInterface
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
        if (isset($this->extras['metadata']['title']) && $this->extras['metadata']['title'] !== '') {
            $container .= '## ' . ($this->extras['metadata']['title'] ?? '') . "\n";
        }

        for ($i = 0; $i < count($this->params['elementList']); $i++) {
            $innerContainer = '';
            $libraryContent = $this->params['elementList'][$i];
            $this->main->newRunnable(
                [
                    'library' => $libraryContent['library'],
                    'params' => $libraryContent['params'],
                ],
                1,
                $innerContainer,
                false,
                [
                    'metadata' => isset($libraryContent['metadata']) ? $libraryContent['metadata'] : [],
                ]
            );
            $container .= $innerContainer . "\n\n";
        }

        if (isset($this->params['text'])) {
            $container .= TextUtils::htmlToText($this->params['text']);
        }

        $container = trim($container);
    }
}

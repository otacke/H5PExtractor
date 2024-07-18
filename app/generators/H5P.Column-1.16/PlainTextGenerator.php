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
class PlainTextGeneratorColumnMajor1Minor16 extends Generator implements GeneratorInterface
{
    private $previousHasMargin = false;

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
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

        if (isset($this->params['content'])) {
            foreach ($this->params['content'] as $content) {
                $libraryContent = $content['content'];

                $separatorResults = UtilsColumnMajor1Minor16::addSeparator(
                    explode(' ', $libraryContent['library'])[0],
                    $content['useSeparator'],
                    $this->previousHasMargin ?? null
                );
                $this->previousHasMargin = $separatorResults['previousHasMargin'];
                $container .= ($separatorResults['separator'] !== '') ?
                    "\n\n" . '---' . "\n\n" :
                    '';

                $innerContainer = '';
                $this->main->newRunnable(
                    [
                        'library' => $libraryContent['library'],
                        'params' => $libraryContent['params'],
                    ],
                    1,
                    $innerContainer,
                    false,
                    [
                        'metadata' => $libraryContent['metadata'],
                    ]
                );

                $container .= $innerContainer;
            }
        }

        $container = trim($container);
    }
}

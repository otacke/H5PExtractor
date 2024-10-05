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
class HtmlGeneratorColumnMajor1Minor16 extends Generator implements GeneratorInterface
{
    private $previousHasMargin;

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
     * @param array             $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-column', $container);

        $container .= '<div>';

        $this->previousHasMargin = null;

        if (isset($this->params['content'])) {
            foreach ($this->params['content'] as $content) {
                $libraryContent = $content['content'];

                $separatorResults = UtilsColumnMajor1Minor16::addSeparator(
                    explode(' ', $libraryContent['library'])[0],
                    $content['useSeparator'],
                    $this->previousHasMargin
                );
                $this->previousHasMargin = $separatorResults['previousHasMargin'];
                $container .= $separatorResults['separator'];

                $innerContainer = '<div class="h5p-column-content h5pClassName" style="break-inside:avoid;">';
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

        $container .= '</div>';

        $container .= $htmlClosing;
    }
}

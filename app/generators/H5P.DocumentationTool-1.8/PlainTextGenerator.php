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
 * Class for generating HTML for H5P.DocumentationTool-1.8.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorDocumentationToolMajor1Minor8 extends Generator implements GeneratorInterface
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
        // Filter out DocumentExportPage for printing
        $this->params['pagesList'] = array_filter($this->params['pagesList'], function ($page) {
            return explode(' ', $page['library'])[0] !== 'H5P.DocumentExportPage';
        });

        $container .= $this->buildNavigation();

        $pagesListLength = count($this->params['pagesList']);
        for ($i = 0; $i < $pagesListLength; $i++) {
            $container .= $this->buildPage($i) . "\n";
        }

        $container = trim($container);
    }

    /**
     * Build the navigation menu.
     * @return string The text for the navigation menu.
     */
    private function buildNavigation()
    {
        $navigation = '';
        $navigation .= '**' . $this->params['taskDescription'] . "**\n";

        for ($i = 0; $i < count($this->params['pagesList']); $i++) {
            $navigation .= "- " . ($this->params['pagesList'][$i]['metadata']['title'] ?? '') . "\n";
        }

        $navigation .= "\n";

        return $navigation;
    }

    /**
     * Build a page.
     *
     * @param int $current The current page index.
     *
     * @return string The HTML for the page.
     */
    private function buildPage($current)
    {
        $page = '';

        $innerContainer = '';
        $libraryContent = $this->params['pagesList'][$current];

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
        $page .= $innerContainer . "\n";

        return $page;
    }
}

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
class HtmlGeneratorDocumentationToolMajor1Minor8 extends Generator implements GeneratorInterface
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
     * @param array             $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        $htmlClosing = TextUtils::getClosingTag($container);

        $originalContainer = $container;

        $pagesListLength = count($this->params['pagesList']);
        for ($i = 0; $i < $pagesListLength; $i++) {
            /* In theory, one could derive this automatically and do in the parent,
            * but content types may not follow the common schema to define the main
            * class name.
            */
            $container .= str_replace('h5pClassName', 'h5p-documentation-tool', $originalContainer);
            $container .= '<div class="h5p-documentation-tool-main-content">';
            $container .= $this->buildNavigation($i);
            $container .= $this->buildPage($i);
            $container .= '</div>'; // Closing h5p-documentation-tool-main-content
            $container .= $htmlClosing;
            $container .= '<span>&nbsp;</span>';
        }
    }

    /**
     * Build the navigation menu.
     *
     * @param int $current The current page index.
     *
     * @return string The HTML for the navigation menu.
     */
    private function buildNavigation($current)
    {
        $container  = '<div class="h5p-navigation-menu">';
        $container .= '<div class="h5p-navigation-menu-header">';
        $container .= $this->params['taskDescription'];
        $container .= '</div>'; // Closing h5p-navigation-menu-header
        $container .= '<div class="h5p-navigation-menu-entries">';
        for ($i = 0; $i < count($this->params['pagesList']); $i++) {
            $currentClass = $current === $i ? ' current' : '';
            $container .= '<div class="h5p-navigation-menu-entry' . $currentClass . '">';
            $container .= $this->params['pagesList'][$i]['metadata']['title'] ?? '';
            $container .= '</div>'; // Closing h5p-navigation-menu-entry
        }
        $container .= '</div>'; // Closing h5p-navigation-menu-entries
        $container .= '</div>'; // Closing h5p-navigation-menu

        return $container;
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
        $container = '<div class="h5p-documentation-tool-page-container">';
        $container .= '<div class="h5p-documentation-tool-page current">';

        $innerContainer = '<div class="h5pClassName" style="">';
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
                'metadata' => $libraryContent['metadata'],
            ]
        );
        $container .= $innerContainer;

        $container .= '</div>'; // Closing h5p-documentation-tool-page
        $container .= '</div>'; // Closing h5p-documentation-tool-page-container

        return $container;
    }
}

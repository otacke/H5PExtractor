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
 * @link     https://todo
 */

namespace H5PExtractor;

require_once __DIR__ . '/../utils/' . 'CSSUtils.php';
require_once __DIR__ . '/../utils/' . 'FileUtils.php';
require_once __DIR__ . '/../utils/' . 'H5PUtils.php';

/**
 * Class for generating HTML for H5P content.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class HtmlGeneratorMain
{
    /**
     * The H5P file handler.
     *
     * @param H5PFileHandler $h5pFileHandler The H5P file handler.
     */
    public function __construct($h5pFileHandler)
    {
        $this->h5pFileHandler = $h5pFileHandler;
    }

    /**
     * Create the HTML for the given H5P content file.
     *
     * @return string The HTML for the given H5P content file.
     */
    public function create()
    {
        $css = $this->getH5PCoreCSS();

        // Get the CSS for the H5P content + subcontents
        $preloadedDependencies
            = $this->h5pFileHandler->getH5PInformation('preloadedDependencies');

        for ($i = 0; $i < count($preloadedDependencies); $i++) {
            $dependency = $preloadedDependencies[$i];
            $css .= $this->h5pFileHandler->getH5PContentTypeCSS(
                $dependency['machineName'],
                $dependency['majorVersion'],
                $dependency['minorVersion']
            );
        }
        $css = CSSUtils::removeClientHandlingCSS($css);

        $contentHtml = $this->createContent(
            array(
                'machineName' =>
                    $this->h5pFileHandler->getH5PInformation('mainLibrary'),
                'majorVersion' =>
                    $this->h5pFileHandler->getH5PInformation('majorVersion'),
                'minorVersion' =>
                    $this->h5pFileHandler->getH5PInformation('minorVersion'),
                'params' =>
                    $this->h5pFileHandler->getH5PContentParams(),
                'container' =>
                    '<div class="h5p-container h5pClassName">',
                'fileHandler' =>
                    $this->h5pFileHandler
            )
        );

        return $this->_createMain($css, $contentHtml);
    }

    /**
     * Create the main HTML for the H5P content.
     *
     * @param string $css         The CSS for the H5P content + subcontents.
     * @param string $contentHtml The main HTML for the H5P content.
     *
     * @return string The main HTML for the H5P content.
     */
    private function _createMain($css = '', $contentHtml = '')
    {
        $html  = '<style>' . $css . '</style>';
        $html .= '<div class="h5p-iframe">';
        $html .= '<div class="h5p-content h5p-initialized h5p-frame">';
        $html .= $contentHtml;
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Create the output for the given H5P content type.
     *
     * @param array $params Parameters.
     *
     * @return string The output for the H5P content type.
     */
    public function createContent($params)
    {
        // Parse and pick from available generators
        $bestLibraryMatch = H5PUtils::getBestLibraryMatch(
            scandir(__DIR__),
            $params['machineName'],
            $params['majorVersion'],
            $params['minorVersion']
        );

        if (!$bestLibraryMatch) {
            return 'No renderer for ' . $params['machineName'] . ' available.';
        }

        $bestLibraryMatchVersion
            = explode('-', $bestLibraryMatch)[1];
        $bestLibraryMatchMajorVersion
            = explode('.', $bestLibraryMatchVersion)[0];
        $bestLibraryMatchMinorVersion
            = explode('.', $bestLibraryMatchVersion)[1];

        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        if (!file_exists(__DIR__ . '/' . $bestLibraryMatch . '/HTMLGenerator.php')) {
            return 'No renderer for ' . $params['machineName'] . ' available.';
        }

        include_once __DIR__ . '/' . $bestLibraryMatch . '/HTMLGenerator.php';

        $className = H5PUtils::buildClassName(
            $params['machineName'],
            $bestLibraryMatchMajorVersion,
            $bestLibraryMatchMinorVersion,
            'H5PExtractor\HtmlGenerator'
        );

        $generator = new $className();
        return $generator->get($params, $this);
    }

    /**
     * Render the HTML for the given H5P question media.
     *
     * @param array $params The parameters for the media.
     *
     * @return string The HTML for the H5P question media.
     */
    public function renderH5PQuestionMedia($params)
    {
        $html = '';
        if (!isset($params['library'])) {
            return '';
        }

        $machineName = explode(' ', $params['library'])[0];
        if ($machineName === 'H5P.Image') {
            if (!isset($params['params']['file']['path'])) {
                return '';
            }

            $imagePath = $this->h5pFileHandler->getBaseDirectory() . '/' .
                $this->h5pFileHandler->getFilesDirectory() . '/' .
                'content' . '/' . $params['params']['file']['path'];

            $html
                = '<div class="h5p-question-image h5p-question-image-fill-width">';
            $html .= '<div class="h5p-question-image-wrap">';
            $html .= '<img';
            $html .= ' src="' . FileUtils::fileToBase64($imagePath). '"';
            $html .= ' alt="' . ($params['params']['alt'] ?? '') .  '"';
            $html .= ' style="max-height: none;"';
            $html .= ' />';
            $html .= '</div>';
            $html .= '</div>';
        } else if ($machineName === 'H5P.Audio') {
            return '<div style="margin:1em;"><em>Audio introduction</em></div>';
        } else if ($machineName === 'H5P.Video') {
            return '<div style="margin:1em;"><em>Video introduction</em></div>';
        }

        return $html;
    }

    /**
     * Get the CSS from H5P core and adjust it to match the generated HTML.
     *
     * @return string The CSS from H5P core.
     */
    public function getH5PCoreCSS()
    {
        $stylesPath = __DIR__ . '/../../public/styles';
        $requiredFiles = [
            'h5p.css',
            'h5p-confirmation-dialog.css',
            'h5p-core-button.css',
            'h5p-tooltip.css',
            'font-open-sans.css'
        ];

        $coreCss = '';
        foreach ($requiredFiles as $fileName) {
            $coreCss .= file_get_contents(
                $stylesPath . '/' . $fileName
            );
        }

        // Adjust to match the CSS selectors in the generated HTML
        $coreCss = str_replace('html.h5p-iframe', '.h5p-iframe', $coreCss);

        // Remove the border from the content
        $coreCss .= '.h5p-content{border:none}';

        // Remove the font import, we've loaded it manually
        $coreCss = str_replace("@import 'font-open-sans.css';", "", $coreCss);

        // Replace URLs to fonts with respective base64 encoded strings
        $coreCss = CSSUtils::replaceUrlsWithBase64($coreCss, $stylesPath);

        return $coreCss;
    }
}
?>

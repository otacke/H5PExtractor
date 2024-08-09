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
 * Class for generating HTML for H5P content.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorMain
{
    public $h5pFileHandler;
    public $renderWidth;
    public $target;
    public $scope;
    private $javaScripts;

    /**
     * The H5P file handler.
     *
     * @param H5PFileHandler $h5pFileHandler The H5P file handler.
     * @param int            $renderWidth    The render width.
     */
    public function __construct(
        $h5pFileHandler,
        $renderWidth = 1024,
        $target = 'print',
        $scope = 'all'
    ) {
        $this->h5pFileHandler = $h5pFileHandler;
        $this->renderWidth = $renderWidth;
        $this->target = $target;
        $this->scope = $scope;
        $this->javaScripts = [];
    }

    /**
     * Create the HTML for the given H5P content file.
     *
     * @return string The HTML for the given H5P content file.
     */
    public function create()
    {
        // Add extractor CSS
        try {
            $css = file_get_contents(
                __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
                'assets' . DIRECTORY_SEPARATOR . 'extractor.css'
            );
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }

        try {
            $css .= $this->getH5PCoreCSS();
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }

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

            $versionedMachineName = $dependency['machineName'] . ' ' .
                $dependency['majorVersion'] . '.' . $dependency['minorVersion'];

            $this->javaScripts[$versionedMachineName] =
                $this->h5pFileHandler->getH5PContentTypeJs(
                    $dependency['machineName'],
                    $dependency['majorVersion'],
                    $dependency['minorVersion']
                );
        }
        $css = CSSUtils::removeClientHandlingCSS($css);

        $metadataFields = [
            'title',
            'a11yTitle',
            'license',
            'licenseVersion',
            'yearFrom',
            'yearTo',
            'source',
            'authors',
            'licenseExtras',
            'changes',
            'authorComments'
        ];

        $metadata = [];
        foreach ($metadataFields as $property) {
            $metadata[$property] =
                $this->h5pFileHandler->getH5PInformation($property);
        };

        $library = [
            'params' => $this->h5pFileHandler->getH5PContentParams(),
            'library' =>
                $this->h5pFileHandler->getH5PInformation('mainLibrary') . ' ' .
                $this->h5pFileHandler->getH5PInformation('majorVersion') . '.' .
                $this->h5pFileHandler->getH5PInformation('minorVersion')
        ];

        $container = '<div class="h5p-container h5pClassName" style="">';

        $this->newRunnable(
            $library,
            1,
            $container,
            false,
            [
                'metadata' => $metadata
            ]
        );

        return $this->createMain($css, $container);
    }

    /**
     * Create the main HTML for the H5P content.
     *
     * @param string $css         The CSS for the H5P content + subcontents.
     * @param string $contentHtml The main HTML for the H5P content.
     *
     * @return string The main HTML for the H5P content.
     */
    private function createMain($css = '', $contentHtml = '')
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
     * Build a placeholder HTML for the given H5P content type.
     *
     * @param string $machineName The machine name of the H5P content type.
     *
     * @return string The placeholder HTML for the H5P content type.
     */
    private function buildPlaceholder($machineName)
    {
        $html  = '<p style="text-align: center">';
        $html .= 'No HTML renderer for <em>' . $machineName . '</em> available.';
        $html .= '</p>';

        $iconData = FileUtils::fileToBase64(
            $this->h5pFileHandler->getIconPath($machineName)
        );

        if ($iconData) {
            $html .= '<img src="' . $iconData .
            '" style="width: min(16rem, 100%); margin: 0 auto; display: block;" />';
        }

        return $html;
    }

    /**
     * Create a new runnable instance. (analogous to H5P.newRunnable).
     *
     * @param array  $library    The library to create a new runnable for.
     * @param int    $contentId  The content ID (not used for now).
     * @param string $attachTo   The container to attach the content to.
     * @param bool   $skipResize Whether to skip resizing the content (not used).
     * @param array  $extras     Additional data such as metadata.
     *
     * @return string The HTML for the H5P content type.
     */
    public function newRunnable(
        $library,
        $contentId,
        &$attachTo = '<div>',
        $skipResize = false, // Just for keeping the same signature as H5P.newRunnable
        $extras = []
    ) {
        try {
            $nameSplit = explode(' ', $library['library'] ?? '', 2);
            $machineName = $nameSplit[0];
            $versionSplit = explode('.', $nameSplit[1], 2);
        } catch (\Exception $e) {
            throw new \Exception('Invalid library string: ' . $library['library']);
        }

        if (getType($library['params']) !== 'object' &&
            getType($library['params']) !== 'array'
        ) {
            throw new \Exception('Invalid library params for ' . $library['library']);
        }

        $extras = $extras ?? [];
        if (isset($library['subContentId'])) {
            $extras['subContentId'] = $library['subContentId'];
        }

        if (isset($library['metadata'])) {
            $extras['metadata'] = $library['metadata'];
        }

        $extras['metadata']['defaultLanguage'] =
            $this->h5pFileHandler->getH5PInformation('defaultLanguage') ?? 'en';

        $generatorClassName = $this->loadBestGenerator($library['library']);
        if (!$generatorClassName) {
            if (isset($attachTo)) {
                $attachTo = $this->buildPlaceholder($library['library']);
            }
            return;
        }
        $generator = new $generatorClassName($library['params'], $contentId, $extras);

        $generator->setMain($this);
        $generator->setLibraryInfo([
            'versionedName' => $library['library'],
            'versionedNameNoSpaces' => $machineName . '-' . $versionSplit[0] . '.' . $versionSplit[1],
            'machineName' => $machineName,
            'majorVersion' => $versionSplit[0],
            'minorVersion' => $versionSplit[1]
        ]);

        if (isset($attachTo)) {
            $generator->attach($attachTo);
        }

        return $generator;
    }

    /**
     * Get the best matching library for the given machine name and version.
     *
     * @param string $fullName The full name of the library.
     */
    private function loadBestGenerator($fullName)
    {
        $library = H5PUtils::getLibraryFromString($fullName);
        if (!$library) {
            return false; // Invalid full name
        }

        list($machineName, $majorVersion, $minorVersion) = array_values($library);

        $bestGeneratorFullName = H5PUtils::getBestLibraryMatch(
            scandir(__DIR__),
            $machineName,
            $majorVersion,
            $minorVersion
        );

        if (!$bestGeneratorFullName) {
            return false;
        }

        $library = H5PUtils::getLibraryFromString($bestGeneratorFullName, '-');
        if (!$library) {
            return false; // Invalid full name
        }

        list($machineName, $majorVersion, $minorVersion) = array_values($library);

        $generatorPath
            = __DIR__ . DIRECTORY_SEPARATOR . $bestGeneratorFullName . DIRECTORY_SEPARATOR . 'HTMLGenerator.php';

        if (!file_exists($generatorPath)) {
            return false; // No generator found
        }

        include_once $generatorPath;

        return H5PUtils::buildClassName(
            $machineName,
            $majorVersion,
            $minorVersion,
            'H5PExtractor\HTMLGenerator'
        );
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

        $title = '';
        if (empty($title) && isset($params['metadata'])) {
            $metadata = $params['metadata'];

            if (!empty($metadata['a11yTitle'])) {
                $title = $metadata['a11yTitle'];
            } elseif (!empty($metadata['title'])) {
                $title = $metadata['title'];
            }
        }

        $machineName = explode(' ', $params['library'])[0];

        if ($machineName === 'H5P.Image') {
            $html
                = '<div class="h5p-question-image h5p-question-image-fill-width">';
            $html .= '<div class="h5p-question-image-wrap">';

            $container = '';  // H5P.Question doesn't use a container

            $this->newRunnable(
                [
                    'library' => $params['library'],
                    'params' => $params['params'],
                ],
                1,
                $container,
                false,
                [
                    'metadata' => $params['metadata'],
                ]
            );

            $html .= $container;
            $html .= '</div>';
            $html .= '</div>';
        } elseif ($machineName === 'H5P.Audio') {
            $container = '<div class="h5p-question-audio h5pClassName" style="">';

            $this->newRunnable(
                [
                    'library' => $params['library'],
                    'params' => $params['params'],
                ],
                1,
                $container,
                false,
                [
                    'metadata' => $params['metadata'],
                ]
            );

            $html = $container;
        } elseif ($machineName === 'H5P.Video') {
            $container = '<div class="h5p-question-video h5pClassName" style="">';

            $this->newRunnable(
                [
                    'library' => $params['library'],
                    'params' => $params['params'],
                ],
                1,
                $container,
                false,
                [
                    'metadata' => $params['metadata'],
                ]
            );

            $html = $container;
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
        $h5pCorePath = 'h5p/h5p-core';

        $vendorPath = FileUtils::getVendorPath(__DIR__);
        $stylesPath =
            $vendorPath . DIRECTORY_SEPARATOR .
            $h5pCorePath . DIRECTORY_SEPARATOR . 'styles';

        if (!isset($stylesPath)) {
            throw new \Exception(
                'Could not find the H5P core styles.'
            );
            return ''; // No core styles found.
        }

        $requiredFiles = [
            'h5p.css',
            'h5p-confirmation-dialog.css',
            'h5p-core-button.css',
            'h5p-tooltip.css', // Only as of H5P core 1.26
            'font-open-sans.css' // Only as of H5P core 1.26
        ];

        $coreCss = '';
        foreach ($requiredFiles as $fileName) {
            if (!file_exists($stylesPath . DIRECTORY_SEPARATOR . $fileName)) {
                if ($fileName === 'font-open-sans.css') {
                    // TODO: Add a fallback font
                }
                return;
            }

            $coreCss .= file_get_contents(
                $stylesPath . DIRECTORY_SEPARATOR . $fileName
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

    /**
     * Determine whether the given content type is scored.
     * Is based on checking the JavaScript for the presence of a getScore function.
     *
     * @param string $versionedMachineName The versioned machine name of the content type.
     *
     * @return bool True if the content type is scored, false otherwise.
     */
    public function isScoredContentType($versionedMachineName)
    {
        $js = $this->javaScripts[$versionedMachineName] ?? '';

        return strpos($js, 'getScore') !== false;
    }
}

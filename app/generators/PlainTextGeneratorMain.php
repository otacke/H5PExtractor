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
 * Class for generating plain text for H5P content.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorMain
{
    public $h5pFileHandler;
    public $renderWidth;

    /**
     * The H5P file handler.
     *
     * @param H5PFileHandler $h5pFileHandler The H5P file handler.
     * @param int            $renderWidth    The render width.
     */
    public function __construct($h5pFileHandler, $renderWidth)
    {
        $this->h5pFileHandler = $h5pFileHandler;
        $this->renderWidth = $renderWidth;
    }

    /**
     * Create the plain text for the given H5P content file.
     *
     * @return string The plain text for the given H5P content file.
     */
    public function create()
    {
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

        $container = '';

        $this->newRunnable(
            $library,
            1,
            $container,
            false,
            [
                'metadata' => $metadata
            ]
        );

        return $container;
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
    public function newRunnable($library, $contentId, &$attachTo = '', $skipResize = false, $extras = [])
    {
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
                $attachTo = 'No plain text renderer for ' . $machineName . ' available.';
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
            = __DIR__ . DIRECTORY_SEPARATOR . $bestGeneratorFullName . DIRECTORY_SEPARATOR . 'PlainTextGenerator.php';

        if (!file_exists($generatorPath)) {
            return false; // No generator found
        }

        include_once $generatorPath;

        return H5PUtils::buildClassName(
            $machineName,
            $majorVersion,
            $minorVersion,
            'H5PExtractor\PlainTextGenerator'
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
        $text = '';
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

        if (in_array($machineName, ['H5P.Image', 'H5P.Audio', 'H5P.Video'])) {
            $container = '';
            $this->newRunnable(
                [
                    'library' => $params['library'],
                    'params' => $params['params'],
                ],
                1,
                $container,
                false,
                [
                    'metadata' => $params['metadata']
                ]
            );
            $text .= $container;
        }

        $text .= "\n";
        return $text;
    }
}

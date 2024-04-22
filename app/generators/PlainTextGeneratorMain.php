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

        $contentText = $this->createContent(
            array(
                'machineName' =>
                    $this->h5pFileHandler->getH5PInformation('mainLibrary'),
                'majorVersion' =>
                    $this->h5pFileHandler->getH5PInformation('majorVersion'),
                'minorVersion' =>
                    $this->h5pFileHandler->getH5PInformation('minorVersion'),
                'params' =>
                    $this->h5pFileHandler->getH5PContentParams(),
                'metadata' =>
                    $metadata,
                'container' =>
                    '',
                'fileHandler' =>
                    $this->h5pFileHandler
            )
        );

        return $contentText;
    }

    /**
     * Create the outpur for the given H5P content type.
     *
     * @param array $params Parameters.
     * @param array $metadata Metadata.
     *
     * @return string The output for the H5P content type.
     */
    public function createContent($params, $metadata = null)
    {
        // Parse and pick from available generators
        $bestLibraryMatch = H5PUtils::getBestLibraryMatch(
            scandir(__DIR__),
            $params['machineName'],
            $params['majorVersion'],
            $params['minorVersion']
        );

        if (!$bestLibraryMatch) {
            return 'No plain text renderer for ' . $params['machineName'] .
                ' available.' . "\n\n";
        }

        $bestLibraryMatchVersion
            = explode('-', $bestLibraryMatch)[1];
        $bestLibraryMatchMajorVersion
            = explode('.', $bestLibraryMatchVersion)[0];
        $bestLibraryMatchMinorVersion
            = explode('.', $bestLibraryMatchVersion)[1];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        $generatorPath
            = __DIR__ . '/' . $bestLibraryMatch . '/PlainTextGenerator.php';

        if (!file_exists($generatorPath)
        ) {
            return 'No plain text renderer for ' . $params['machineName'] .
                ' available.' . "\n\n";
        }

        include_once __DIR__ . '/' . $bestLibraryMatch . '/PlainTextGenerator.php';

        $className = H5PUtils::buildClassName(
            $params['machineName'],
            $bestLibraryMatchMajorVersion,
            $bestLibraryMatchMinorVersion,
            'H5PExtractor\PlainTextGenerator'
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
        $version = explode(' ', $params['library'])[1];

        if ($machineName === 'H5P.Image') {
            return $this->createContent([
                'machineName' => $machineName,
                'majorVersion' => explode('.', $version)[0],
                'minorVersion' => explode('.', $version)[1],
                'params' => $params['params'],
                'metadata' => $params['metadata'],
                'container' => ''
            ]);
        } elseif ($machineName === 'H5P.Audio') {
            return 'Audio: ' . $title . "\n\n";
        } elseif ($machineName === 'H5P.Video') {
            return 'Video: ' . $title . "\n\n";
        }

        return $text;
    }
}

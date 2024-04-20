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

require_once __DIR__ . '/../utils/' . 'FileUtils.php';
require_once __DIR__ . '/../utils/' . 'H5PUtils.php';

/**
 * Class for generating plain text for H5P content.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
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
            return 'No plain text renderer for ' . $params['machineName'] .
                ' available.';
        }

        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        $generatorPath
            = __DIR__ . '/' . $bestLibraryMatch . '/PlainTextGenerator.php';

        if (!file_exists($generatorPath)
        ) {
            return 'No plain text renderer for ' . $params['machineName'] .
                ' available.';
        }

        include_once __DIR__ . '/' . $bestLibraryMatch . '/PlainTextGenerator.php';

        $className = H5PUtils::buildClassName(
            $params['machineName'],
            $params['majorVersion'],
            $params['minorVersion'],
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

        $machineName = explode(' ', $params['library'])[0];
        if ($machineName === 'H5P.Image') {
            if (!isset($params['params']['file']['path'])) {
                return '';
            }

            if (isset($params['params']['alt'])) {
                $text = '![' . $params['params']['alt'] . ']' . "\n";
            }
            return $text . "\n";
        } else if ($machineName === 'H5P.Audio') {
            return 'Audio introduction';
        } else if ($machineName === 'H5P.Video') {
            return 'Video introduction';
        }

        return $text;
    }
}
?>

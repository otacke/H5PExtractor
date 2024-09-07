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
 * Interface for generators for H5P content types.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class Generator
{
    protected $params;
    protected $contentId;
    protected $extras;
    protected $main;
    public $libraryInfo;
    private $cacheIsScoredContentType = [];

    public function __construct($params = [], $contentId = 0, $extras = [])
    {
        $this->params = $params;
        $this->contentId = $contentId;
        $this->extras = $extras;
    }

    /**
     * Set the main class.
     *
     * @param HtmlGeneratorMain $main The main class.
     */
    public function setMain($main)
    {
        $this->main = $main;
    }

    /**
     * Set the library info.
     *
     * @param array $libraryInfo The library info.
     */
    public function setLibraryInfo($libraryInfo)
    {
        $this->libraryInfo = $libraryInfo;
    }

    /**
     * Get render width.
     *
     * @return int The render width.
     */
    public function getRenderWidth()
    {
        $machineName = $this->libraryInfo['machineName'] ?? '';

        // Use custom render width if set for machine name
        if (isset($this->main->renderWidths[$machineName])) {
            return $this->main->renderWidths[$machineName];
        }

        return $this->main->renderWidth;
    }

    /**
     * Build src for given content path. Will use base64 encoding if no URL path to H5P content is set.
     *
     * @param string $contentPath Path to the file.
     *
     * @return string URL or base64 encoded file content.
     */
    public function buildFileSource($contentPath)
    {
        if (gettype($contentPath) !== 'string' || $contentPath === '') {
            return '';
        }

        if (isset($this->main->h5pContentUrl)) {
            return $this->main->h5pContentUrl . $contentPath;
        }

        return $this->fileToBase64($contentPath);
    }

    /**
     * Get base64 encoded file content.
     *
     * @param string $contentPath Path to the file.
     *
     * @return string Base64 encoded file content.
     */
    public function fileToBase64($contentPath)
    {
        if (getType($contentPath) !== 'string' || $contentPath === '') {
            return '';
        }

        if (isset($this->main->h5pContentUrl)) {
            return $this->main->h5pContentUrl . $contentPath;
        }

        $fullPath =
            $this->main->h5pFileHandler->getBaseDirectory() . DIRECTORY_SEPARATOR .
            $this->main->h5pFileHandler->getFilesDirectory() . DIRECTORY_SEPARATOR .
            'content' . DIRECTORY_SEPARATOR . $contentPath;

        return FileUtils::fileToBase64($fullPath);
    }

    /**
     * Get the size of an image.
     *
     * @param string $contentPath Path to the image.
     *
     * @return array Image size.
     */
    public function getImageSize($contentPath)
    {
        if (gettype($contentPath) !== 'string' || $contentPath === '') {
            return null;
        }

        $fullPath =
            $this->main->h5pFileHandler->getBaseDirectory() . DIRECTORY_SEPARATOR .
            $this->main->h5pFileHandler->getFilesDirectory() . DIRECTORY_SEPARATOR .
            'content' . DIRECTORY_SEPARATOR . $contentPath;

        return FileUtils::getImageSize($fullPath);
    }

    /**
     * Determine whether the given content type is scored.
     *
     * @param string $versionedMachineName The versioned machine name of the content type.
     *
     * @return bool True if the content type is scored, false otherwise.
     */
    public function isScoredContentType($versionedMachineName)
    {
        /*
         * Cache the result to avoid multiple calls to the main class that
         * might be expensive.
         */
        if (isset($this->cacheIsScoredContentType[$versionedMachineName])) {
            return $this->cacheIsScoredContentType[$versionedMachineName];
        }

        $isScored = $this->main->isScoredContentType($versionedMachineName);
        $this->cacheIsScoredContentType[$versionedMachineName] = $isScored;

        return $isScored;
    }
}

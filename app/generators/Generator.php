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

    public function __construct($params = [], $contentId = 0, $extras = [])
    {
        $this->params = $params;
        $this->contentId = $contentId;
        $this->extras = $extras;
    }

    public function setMain($main)
    {
        $this->main = $main;
    }

    public function setLibraryInfo($libraryInfo)
    {
        $this->libraryInfo = $libraryInfo;
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

        $fullPath =
            $this->main->h5pFileHandler->getBaseDirectory() . '/' .
            $this->main->h5pFileHandler->getFilesDirectory() . '/' .
            'content' . '/' . $contentPath;

        return FileUtils::fileToBase64($fullPath);
    }
}

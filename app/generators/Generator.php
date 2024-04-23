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

    public function __construct($params, $contentId, $extras) {
        $this->params = $params;
        $this->contentId = $contentId;
        $this->extras = $extras;
    }

    public function setMain($main) {
        $this->main = $main;
    }

    public function setLibraryInfo($libraryInfo) {
        $this->libraryInfo = $libraryInfo;
    }
}

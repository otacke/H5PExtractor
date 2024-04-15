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

/**
 * Class for handling CSS.
 *
 * @category File
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class FileUtils
{
    /**
     * Convert the given file to a base64 encoded string.
     *
     * @param string $url The URL of the file to convert.
     *
     * @return string The base64 encoded string.
     */
    public static function fileToBase64($url)
    {
        $url = explode('?', $url)[0];

        if (!file_exists($url)) {
            return '';
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($fileInfo, $url);

        $fileContent = file_get_contents($url);

        return 'data:' . $fileType . ';base64,' . base64_encode($fileContent);
    }
}

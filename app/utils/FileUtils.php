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
 * Class for handling CSS.
 *
 * @category File
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class FileUtils
{
    /**
     * Convert the given file to a base64 encoded string.
     *
     * @param string $path The URL of the file to convert.
     *
     * @return string The base64 encoded string.
     */
    public static function fileToBase64($path)
    {
        if (getType($path) !== 'string') {
            return '';
        }

        $path = explode('?', $path)[0];

        if (!file_exists($path)) {
            return '';
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($fileInfo, $path);

        $fileContent = file_get_contents($path);

        return 'data:' . $fileType . ';base64,' . base64_encode($fileContent);
    }

    /**
     * Get composer vendor path.
     *
     * @param string $startDir The directory to start the search from.
     *
     * @return string|null The vendor path or null if not found.
     */
    public static function getVendorPath($startDir)
    {
        while (!file_exists($startDir . '/vendor')) {
            $startDir = dirname($startDir);
            if ($startDir === '/') {
                return null;
            }
        }

        return $startDir . '/vendor';
    }
}

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
 * Class for handling H5P specific stuff.
 *
 * @category File
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class H5PUtils
{
    /**
     * Build the class name for the given H5P content type.
     *
     * @param string $machineName  The machine name of the content type.
     * @param int    $majorVersion The major version of the content type.
     * @param int    $minorVersion The minor version of the content type.
     * @param string $prefix       The optional prefix for the class name.
     *
     * @return string The class name for the given H5P content type.
     */
    public static function buildClassName(
        $machineName, $majorVersion, $minorVersion, $prefix = ''
    ) {
        return $prefix . explode('.', $machineName)[1] . '_' .
          $majorVersion . '_' . $minorVersion;
    }

    /**
     * Get the best matching library from the given list.
     * Will pick exact match if available, otherwise the closest lower version,
     * otherwise the closest higher version, otherwise null.
     *
     * @param array  $list         The list of libraries.
     * @param string $machineName  The machine name of the library.
     * @param int    $majorVersion The target major version.
     * @param int    $minorVersion The target minor version.
     *
     * @return string|null The best matching library or null if none found.
     */
    public static function getBestLibraryMatch(
        $list, $machineName, $majorVersion, $minorVersion
    ) {
        $list = array_filter(
            $list, function ($name) use ($machineName) {
                return strpos($name, $machineName . '-') !== false;
            }
        );

        if (count($list) === 0) {
            return null; // No library with this machine name available
        }

        $target = $machineName . '-' .
          $majorVersion . '.' . $minorVersion;

        if (in_array($target, $list)) {
            return $target; // Exact match found
        }

        $list[] = $target;

        usort(
            $list, function ($a, $b) {
                $versionA = explode('-', $a)[1];
                $versionB = explode('-', $b)[1];
                $versionA = explode('.', $versionA);
                $versionB = explode('.', $versionB);
                if ($versionA[0] != $versionB[0]) {
                    return $versionA[0] - $versionB[0];
                } else {
                    return $versionA[1] - $versionB[1];
                }
            }
        );

        $position = array_search($target, $list);

        if ($position === 0) {
            return $list[1]; // Can only provide newer version
        } else {
            return $list[$position - 1]; // Can only provide older version
        }
    }
}

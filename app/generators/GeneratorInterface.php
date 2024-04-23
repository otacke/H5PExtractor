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
 * Interface for HTML generators for H5P content types.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
interface GeneratorInterface
{
    /**
     * Create the HTML for the given H5P content type in params
     *
     * @param string $container The container for the H5P content type.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container);
}

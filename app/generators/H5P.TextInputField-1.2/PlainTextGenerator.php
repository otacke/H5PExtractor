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
 * Class for generating HTML for H5P.TextInputField-1.2.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorTextInputFieldMajor1Minor2 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params     Parameters.
     * @param int   $contentId  Content ID.
     * @param array $extras     Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        if (isset($this->params['taskDescription']) && $this->params['taskDescription'] !== '') {
            $container .= TextUtils::htmlToText($this->params['taskDescription']) . "\n";
        }

        if (!empty($this->params['placeholderText'])) {
            $container .= ($this->params['placeholderText'] ?? '') . "\n\n";
        }

        $line = '________________________________________' . "\n";
        $numberLines = (isset($this->params['behaviour']['inputFieldSize'])) ?
            $this->params['behaviour']['inputFieldSize'] :
            10;

        for ($i = 0; $i < $numberLines / 2; $i++) {
            $container .= $line . "\n";
        }

        $container = trim($container);
    }
}

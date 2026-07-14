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
 * Class for generating HTML for H5P.SortParagraphs-0.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorSortParagraphsMajor0Minor11 extends Generator implements GeneratorInterface
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
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'Utils.php';
        if (isset($this->params['taskDescription'])) {
            $container .= TextUtils::htmlToText($this->params['taskDescription']);
        }

        $numberOfParagraphs = count($this->params['paragraphs']);
        $paragraphs = GeneralUtils::shuffle($this->params['paragraphs']);
        for ($i = 0; $i < $numberOfParagraphs; $i++) {
            $container .= ($i + 1) . '. ' . TextUtils::htmlToText($paragraphs[$i]);
        }

        $container = trim($container);
    }

    /**
     * Get the solution text.
     *
     * @return string The solution text.
     */
    public function showSolutions()
    {
        return UtilsSortParagraphsMajor0Minor11::getSolutionTexts($this->params);
    }
}

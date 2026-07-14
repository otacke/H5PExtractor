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
 * Class for generating HTML for H5P.ImageSequencing-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorImageSequencingMajor1Minor1 extends Generator implements GeneratorInterface
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
            $container .= TextUtils::htmlToText($this->params['taskDescription']) . "\n\n";
        }

        $shuffledImages = GeneralUtils::shuffle($this->params['sequenceImages']);
        for ($i = 0; $i < count($shuffledImages); $i++) {
            $container .=
                '___ ' .
                TextUtils::htmlToText($shuffledImages[$i]['imageDescription']) .
                "\n\n";
        }
    }

    /**
     * Get the solution text.
     *
     * @return string The solution text.
     */
    public function showSolutions()
    {
        return UtilsImageSequencingMajor1Minor1::getSolutionTexts($this->params);
    }
}

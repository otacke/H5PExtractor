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
 * Class for generating HTML for H5P.FindTheWords-1.4.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorFindTheWordsMajor1Minor4 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params    Parameters.
     * @param int   $contentId Content ID.
     * @param array $extras    Extras.
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

        $words = UtilsFindTheWordsMajor1Minor4::extractWords($this->params['wordList']);

        $grid = UtilsFindTheWordsMajor1Minor4::placeWordsOnGrid(
            $words,
            UtilsFindTheWordsMajor1Minor4::mapOrientations($this->params['behaviour']['orientations'])
        );
        $grid = UtilsFindTheWordsMajor1Minor4::fillBlanks($grid, $this->params);

        $gridSize = count($grid);

        if (isset($this->params['taskDescription'])) {
            $container .= TextUtils::htmlToText($this->params['taskDescription']);
        }

        $container .= "\n";

        // Output the grid
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $container .= $cell;
            }
            $container .= "\n";
        }

        $container .= "\n";
        $container .= "*" . $this->params['l10n']['wordListHeader'] . "*\n";

        $container .= implode(", ", $words);
    }
}

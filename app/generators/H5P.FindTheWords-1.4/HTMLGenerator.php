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
class HtmlGeneratorFindTheWordsMajor1Minor4 extends Generator implements GeneratorInterface
{
    private static $CELL_MIN_SIZE_PX = 32;
    private static $CELL_MAX_SIZE_PX = 96;
    private static $CELL_MARGIN_PX = 8;
    private static $CHAR_SPACING_FACTOR = 0.66;
    private static $HEADING_FONT_SIZE_FACTOR = 1.2;

    /**
     * Constructor.
     *
     * @param HTMLGeneratorMain $main The main HTML generator.
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

        $sizes = $this->computeDOMSizes($grid);

        $gridSize = count($grid);

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-find-the-words', $container);
        $container = str_replace(
            'style="',
            'style="display: flex; flex-direction: column;',
            $container
        );

        $container .= '<div class="h5p-play-area">';
        $container .=
            '<div class="h5p-task-description">' .
                $this->params['taskDescription'] .
            '</div>';
        $container .=
            '<div ' .
                'class="game-container"' .
                'style="display: flex; flex-direction: column; font-family: sans-serif;"' .
            '>';

        $container .= '<div class="puzzle-container">';
        $container .= '<div ' .
            'class="dom-canvas-grid"' .
            'style="' .
                'height: ' . $sizes['gridHeight'] . 'px;' .
                'width: ' . $sizes['gridWidth'] . 'px;' .
                'font-size: ' . $sizes['fontSize'] . 'px;' .
                'line-height: ' . $sizes['lineHeight'] . 'px;' .
            '"' .
        '>';

        // Output the grid
        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $container .=
                    '<div ' .
                        'class="dom-canvas-grid-cell"' .
                        'style="' .
                            'box-sizing: border-box;' .
                            'display: inline-block;' .
                            'margin: 0;' .
                            '-webkit-touch-callout: none;' .
                            '-webkit-user-select: none;' .
                            'user-select: none;' .
                            'width: ' . $sizes['cellWidth'] . 'px;' .
                            'height: ' . $sizes['cellHeight'] . 'px;' .
                            'padding-left: ' . $sizes['cellPaddingLeft'] . 'px;' .
                            'padding-top: ' . $sizes['cellPaddingTop'] . 'px;' .
                        '"' .
                    '>' .
                        $cell .
                    '</div>';
            }
        }

        $headingFontSize = $sizes['fontSize'] * self::$HEADING_FONT_SIZE_FACTOR;
        $headingPaddingTopBottom = $sizes['fontSize'] / 2;

        $container .= '</div>'; // Closing dom-canvas-grid
        $container .= '</div>'; // Closing puzzle-container
        $container .= '<div class="vocabulary-container" style="width: ' . $sizes['gridWidth'] . 'px;">';
        $container .=
            '<div ' .
                'class="vocHeading"' .
                'style="' .
                    'font-size: ' . $headingFontSize . 'px;' .
                    'overflow-y: auto;' .
                    'padding: ' . $headingPaddingTopBottom . 'px 8px;' .
                '"' .
            '>';
        $container .= '<em class="fa fa-book fa-fw"></em>';
        $container .= $this->params['l10n']['wordListHeader'];
        $container .= '</div>';

        $container .= '<ul ' .
            'style="'.
                'margin: 0;' .
                'padding-right: ' . $headingPaddingTopBottom .'px;' .
                'padding-top: ' . $headingPaddingTopBottom . 'px;' .
            '"' .
        '>';

        $marginBotton = $sizes['fontSize'] / 2;
        for ($i = 0; $i < count($words); $i++) {
            $container .= '<li style="display: inline-block; margin: 0 0 ' . $marginBotton . 'px 0;">';
            $container .= '<div ' .
                'class="word"' .
                'style="' .
                    'font-size:' . $sizes['fontSize'] . 'px;' .
                '"' .
            '>';
            $container .= '<em class="fa fa-check"></em>';
            $container .= $words[$i];
            $container .= '</div>';
        }
        $container .= '</ul>';

        $container .= '</div>'; // Closing vocabulary-container
        $container .= '</div>'; // Closing game-container
        $container .= '</div>'; // Closing h5p-play-area

        $container .= $htmlClosing;
    }

    /**
     * Compute sizes for the grid and its elements.
     *
     * @param array $grid The grid.
     *
     * @return array The sizes.
     */
    private function computeDOMSizes($grid)
    {
        $containerWidth = $this->getRenderWidth();
        $gridCol = count($grid);
        $gridMaxWidth = $gridCol * self::$CELL_MAX_SIZE_PX + 2 * self::$CELL_MARGIN_PX;
        $gridElementStdSize = ($containerWidth - 2 * self::$CELL_MARGIN_PX) / $gridCol;

        if ($gridMaxWidth < $containerWidth) {
            $elementSize = self::$CELL_MAX_SIZE_PX;
        } elseif ($gridElementStdSize > self::$CELL_MIN_SIZE_PX) {
            $elementSize = $gridElementStdSize;
        } else {
            $elementSize = self::$CELL_MIN_SIZE_PX;
        }

        $gridWidth = $elementSize * count($grid[0]) * self::$CHAR_SPACING_FACTOR;
        $gridHeight = $elementSize * count($grid) * self::$CHAR_SPACING_FACTOR;

        return [
            'elementSize' => $elementSize,
            'gridWidth' => $gridWidth,
            'gridHeight' => $gridHeight,
            'fontSize' => $elementSize / 3 * self::$CHAR_SPACING_FACTOR,
            'lineHeight' => $elementSize / 3 * self::$CHAR_SPACING_FACTOR,
            'cellWidth' => $gridWidth / count($grid[0]),
            'cellHeight' => $gridHeight / count($grid),
            'cellPaddingLeft' => $elementSize / 4 * self::$CHAR_SPACING_FACTOR,
            'cellPaddingTop' => $elementSize / 3 * self::$CHAR_SPACING_FACTOR
        ];
    }
}

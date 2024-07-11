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
    private static $CELL_MAX_SIZE_PX = 64;
    private static $CELL_MARGIN_PX = 8;
    private static $CHAR_SPACING_FACTOR = 0.66;

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
        $words = $this->extractWords($this->params['wordList']);

        $grid = $this->placeWordsOnGrid(
            $words,
            $this->mapOrientations($this->params['behaviour']['orientations'])
        );
        $grid = $this->fillBlanks($grid);

        $sizes = $this->computeDOMSizes($grid);

        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-find-the-words', $container);
        $container = str_replace(
            'style=""',
            'style="display: flex; flex-direction: column;"',
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
                'style="display: flex; flex-direction: row"' .
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

        $container .= '</div>'; // Closing dom-canvas-grid
        $container .= '</div>'; // Closing puzzle-container

        $container .= '<div class="vocabulary-container">';
        $container .=
            '<div ' .
                'class="vocHeading"' .
                'style="overflow-y: auto;"' .
            '>';
        $container .= '<em class="fa fa-book fa-fw"></em>';
        $container .= $this->params['l10n']['wordListHeader'];
        $container .= '</div>';

        $container .= '<ul>';
        for ($i = 0; $i < count($words); $i++) {
            $container .= '<li>';
            $container .= '<div class="word">';
            $container .= '<em class="fa fa-check" style="visibility: hidden;"></em>';
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
     * Extract and sanitize words from the parameters.
     *
     * @param string $params The parameters.
     *
     * @return array The extracted words.
     */
    private function extractWords($params = '')
    {
        return array_map('trim', explode(',', strtoupper($params)));
    }

    /**
     * Map orientations to directions.
     *
     * @param array $params The parameters.
     *
     * @return array The directions.
     */
    private function mapOrientations($params)
    {
        $allDirections = [
            'horizontal', 'vertical', 'diagonal',
            'horizontalreversed', 'verticalreversed', 'diagonalreversed'
        ];

        $orientations = array_keys(
            array_filter($params ?? $allDirections)
        );

        $orientationToDirectionMap = [
            'horizontal' => 'horizontal',
            'vertical' => 'vertical',
            'diagonal' => 'diagonal',
            'diagonalUp' => 'diagonal',
            'horizontalBack' => 'horizontalreversed',
            'verticalUp' => 'verticalreversed',
            'diagonalBack' => 'diagonalreversed',
            'diagonalUpBack' => 'diagonalreversed',
        ];

        return array_values(array_unique(array_map(
            fn($orientation) => $orientationToDirectionMap[$orientation],
            array_intersect($orientations, array_keys($orientationToDirectionMap))
        )));
    }

    /**
     * Fill blanks in the grid with random characters.
     *
     * @param array $grid The grid.
     *
     * @return array The grid with blanks filled.
     */
    private function fillBlanks($grid)
    {
        $pool = strtoupper(
            $this->params['behaviour']['fillPool'] ??
                'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
        );

        $gridSize = count($grid);
        for ($i = 0; $i < $gridSize; $i++) {
            for ($j = 0; $j < $gridSize; $j++) {
                if ($grid[$i][$j] == '.') {
                    $grid[$i][$j] = $pool[mt_rand(0, strlen($pool) - 1)];
                }
            }
        }
        return $grid;
    }

    /**
     * Place words on a grid.
     *
     * @param array $words The words to place.
     * @param array $allowedDirections The directions in which to place the words.
     * @param int $maxAttempts The maximum number of attempts to place a word.
     */
    private function placeWordsOnGrid(
        $words,
        $allowedDirections = [
            'horizontal', 'vertical', 'diagonal',
            'horizontalreversed', 'verticalreversed', 'diagonalreversed'
        ],
        $maxAttempts = 50
    ) {
        // Sort words by length, hoping there are more options for overlapping
        usort($words, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        // Initialize grid with dimensions enough for the longest word
        $gridSize = strlen($words[0]);
        $directions = [
            'horizontal' => [1, 0],
            'vertical' => [0, 1],
            'diagonal' => [1, 1],
            'horizontalreversed' => [-1, 0],
            'verticalreversed' => [0, -1],
            'diagonalreversed' => [-1, -1]
        ];

        $allWordsPlaced = false;
        while (!$allWordsPlaced) {
            $grid = array_fill(0, $gridSize, array_fill(0, $gridSize, '.'));
            $allWordsPlaced = true;

            foreach ($words as $word) {
                $wordPlaced = false;
                $attempts = 0;

                while (!$wordPlaced && $attempts < $maxAttempts) {
                    $startX = mt_rand(0, $gridSize - 1);
                    $startY = mt_rand(0, $gridSize - 1);
                    $directionKey = $allowedDirections[mt_rand(0, count($allowedDirections) - 1)];
                    $dx = $directions[$directionKey][0];
                    $dy = $directions[$directionKey][1];

                    if ($this->canPlaceWord($grid, $startX, $startY, $dx, $dy, $word)) {
                        $this->placeWord($grid, $startX, $startY, $dx, $dy, $word);
                        $wordPlaced = true;
                    }

                    $attempts++;
                }

                if (!$wordPlaced) {
                    $allWordsPlaced = false;
                    break;
                }
            }

            if (!$allWordsPlaced) {
                $gridSize++;
            }
        }

        return $grid;
    }

    /**
     * Check if a word can be placed on the grid.
     *
     * @param array $grid The grid.
     * @param int $startX The x-coordinate of the starting point.
     * @param int $startY The y-coordinate of the starting point.
     * @param int $dx The x-direction.
     * @param int $dy The y-direction.
     * @param string $word The word to place.
     */
    private function canPlaceWord(&$grid, $startX, $startY, $dx, $dy, $word)
    {
        $gridSize = count($grid);
        for ($i = 0; $i < strlen($word); $i++) {
            $x = $startX + $i * $dx;
            $y = $startY + $i * $dy;
            if ($x < 0 || $x >= $gridSize || $y < 0 || $y >= $gridSize) {
                return false;
            }
            if ($grid[$y][$x] != '.' && $grid[$y][$x] != $word[$i]) {
                return false;
            }
        }
        return true;
    }

    /**
     * Place a word on the grid.
     *
     * @param array $grid The grid.
     * @param int $startX The x-coordinate of the starting point.
     * @param int $startY The y-coordinate of the starting point.
     * @param int $dx The x-direction.
     * @param int $dy The y-direction.
     * @param string $word The word to place.
     */
    private function placeWord(&$grid, $startX, $startY, $dx, $dy, $word)
    {
        for ($i = 0; $i < strlen($word); $i++) {
            $x = $startX + $i * $dx;
            $y = $startY + $i * $dy;
            $grid[$y][$x] = $word[$i];
        }
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
        $containerWidth = $this->main->renderWidth;
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
            'fontSize' => $elementSize / 3 / self::$CHAR_SPACING_FACTOR * self::$CHAR_SPACING_FACTOR,
            'lineHeight' => $elementSize / 3 / self::$CHAR_SPACING_FACTOR * self::$CHAR_SPACING_FACTOR,
            'cellWidth' => $gridWidth / count($grid[0]),
            'cellHeight' => $gridHeight / count($grid),
            'cellPaddingLeft' => $elementSize / 4 * self::$CHAR_SPACING_FACTOR,
            'cellPaddingTop' => $elementSize / 3 * self::$CHAR_SPACING_FACTOR
        ];
    }
}

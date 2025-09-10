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
 * @category Utility
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsFindTheWordsMajor1Minor4
{
    /**
     * Extract and sanitize words from the parameters.
     *
     * @param string $params The parameters.
     *
     * @return array The extracted words.
     */
    public static function extractWords($params = '')
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
    public static function mapOrientations($params)
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
     * Place words on a grid.
     *
     * @param array $words The words to place.
     * @param array $allowedDirections The directions in which to place the words.
     * @param int $maxAttempts The maximum number of attempts to place a word.
     */
    public static function placeWordsOnGrid(
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

                    if (self::canPlaceWord($grid, $startX, $startY, $dx, $dy, $word)) {
                        self::placeWord($grid, $startX, $startY, $dx, $dy, $word);
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
     * Fill blanks in the grid with random characters.
     *
     * @param array $grid The grid.
     * @param array $params The parameters.
     *
     * @return array The grid with blanks filled.
     */
    public static function fillBlanks($grid, $params)
    {
        $pool = strtoupper(
            $params['behaviour']['fillPool'] ??
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
     * Check if a word can be placed on the grid.
     *
     * @param array $grid The grid.
     * @param int $startX The x-coordinate of the starting point.
     * @param int $startY The y-coordinate of the starting point.
     * @param int $dx The x-direction.
     * @param int $dy The y-direction.
     * @param string $word The word to place.
     */
    public static function canPlaceWord(&$grid, $startX, $startY, $dx, $dy, $word)
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
    public static function placeWord(&$grid, $startX, $startY, $dx, $dy, $word)
    {
        for ($i = 0; $i < strlen($word); $i++) {
            $x = $startX + $i * $dx;
            $y = $startY + $i * $dy;
            $grid[$y][$x] = $word[$i];
        }
    }
}

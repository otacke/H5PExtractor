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
class DOMUtils
{
  /**
   * Estimate the font size that can be used to fit a given text into a given area.
   *
   * @param string $text The text to fit.
   * @param int $textAreaWidthPx The width of the area to fit the text into in pixels.
   * @param int $textAreaHeightPx The height of the area to fit the text into in pixels.
   * @param int $minFontSize The minimum font size to consider in pixels.
   * @param int $maxFontSize The maximum font size to consider in pixels.
   * @param float $averageCharWidthEm The average width of a character in em units.
   * @param float $lineHeight The line height in em units.
   */
    public static function estimateFittingFontSize(
        $text,
        $textAreaWidth,
        $textAreaHeight,
        $minFontSize = 0,
        $maxFontSize = 16,
        $averageCharWidthEm = 0.5,
        $lineHeight = 1.5
    ) {
      /*
       * Just solving an equation:
       * absolute width of text = charCount of text * fontSize * averageCharWidthEm
       * number of lines required = absolute height of text / $textAreaWidth * $lineHeight
       * height in pixels required = number of lines required * fontSize
       * Solve for fontSize
       */
        $estimatedFontSizeThreshold = sqrt(
            $textAreaHeight * $textAreaWidth /
            (strlen($text) * $averageCharWidthEm * $lineHeight)
        );

        return max($minFontSize, min($estimatedFontSizeThreshold, $maxFontSize));
    }

    /**
     * Build a style attribute.
     *
     * @param array $cssProperties CSS properties.
     *
     * @return string The style attribute.
     */
    public static function buildStyleAttribute($cssProperties, $spaced = true)
    {
        $delimiter = $spaced ? ' ' : '';

        $style = '';
        if (count($cssProperties) > 0) {
            $style = $delimiter . 'style="';
            foreach ($cssProperties as $name => $value) {
                $style .= $name . ':' . $value . ';';
            }
            $style .= '"';
        }

        return $style;
    }
}

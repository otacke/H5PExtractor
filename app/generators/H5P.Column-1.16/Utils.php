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
class UtilsColumnMajor1Minor16
{
      // Odd hardcoded list of content types with margins as in original Column code
      const HAS_MARGINS = [
        'H5P.AdvancedText',
        'H5P.AudioRecorder',
        'H5P.Essay',
        'H5P.Link',
        'H5P.Accordion',
        'H5P.Table',
        'H5P.GuessTheAnswer',
        'H5P.Blanks',
        'H5P.MultiChoice',
        'H5P.TrueFalse',
        'H5P.DragQuestion',
        'H5P.Summary',
        'H5P.DragText',
        'H5P.MarkTheWords',
        'H5P.ImageHotspotQuestion',
        'H5P.MemoryGame',
        'H5P.Dialogcards',
        'H5P.QuestionSet',
        'H5P.DocumentationTool'
      ];

      // Odd hardcoded list of content types with margins as in original Column code
      const HAS_TOP_MARGINS = [
        'H5P.SingleChoiceSet'
      ];

      // Odd hardcoded list of content types with margins as in original Column code
      const HAS_BOTTOM_MARGINS = [
        'H5P.CoursePresentation',
        'H5P.Dialogcards',
        'H5P.GuessTheAnswer',
        'H5P.ImageSlider'
      ];

      public static function addSeparator(
          $libraryName,
          $useSeparator,
          $previousHasMargin = null
      ) {
          $thisHasMargin = in_array($libraryName, UtilsColumnMajor1Minor16::HAS_MARGINS);
          $separator = '';

          if (isset($previousHasMargin)) {
              $separatorClass = 'h5p-column-ruler';

              if (!$thisHasMargin &&
              !in_array($libraryName, UtilsColumnMajor1Minor16::HAS_TOP_MARGINS)
              ) {
                  $separatorClass .= ' h5p-column-space-before';
                  if (!$previousHasMargin &&
                  $useSeparator === 'enabled'
                  ) {
                      $separatorClass .= ' h5p-column-space-after';
                  }
              } elseif (!$previousHasMargin &&
              $useSeparator === 'enabled'
              ) {
                  $separatorClass .= ' h5p-column-space-before';
              }

              if ($useSeparator !== 'disabled') {
                  $separator = '<div class="' . $separatorClass . '"></div>';
              }
          }

          $previousHasMargin = $thisHasMargin ||
          in_array($libraryName, UtilsColumnMajor1Minor16::HAS_BOTTOM_MARGINS);

          return [
          'separator' => $separator,
          'previousHasMargin' => $previousHasMargin
          ];
      }
}

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
class UtilsNDLATimelineMajor0Minor3
{
    /**
     * Filter slides with startDate and sort them by date.
     *
     * @param array $slides Slides.
     *
     * @return array Filtered an rearranges slides.
     */
    public static function filterAndSortSlides(array $slides): array
    {
        $slides = array_filter($slides, fn($slide) => isset($slide['startDate']));
        usort(
            $slides,
            function ($a, $b) {
                $aDate = UtilsNDLATimelineMajor0Minor3::parseDateString($a['startDate']);
                $bDate = UtilsNDLATimelineMajor0Minor3::parseDateString($b['startDate']);

                foreach (['year', 'month', 'day'] as $part) {
                    $aVal = $aDate[$part] ?? 0;
                    $bVal = $bDate[$part] ?? 0;
                    if ($aVal !== $bVal) {
                        return $aVal <=> $bVal;
                    }
                }

                return 0;
            }
        );
        return $slides;
    }

    /**
     * Format date range.
     *
     * @param array  $slide   Slide.
     * @param string $langTag Language tag.
     *
     * @return string Formatted date range.
     */
    public static function formatDate($slide, $langTag, $bce)
    {
        $fulldate = '';
        if (($slide['slideType'] ?? null) !== 'regular' || !isset($slide['startDate'])) {
            return '';
        }

        $startDate = UtilsNDLATimelineMajor0Minor3::parseDateString($slide['startDate']);
        $startYear = UtilsNDLATimelineMajor0Minor3::formatYear($startDate['year'], $bce);
        $startDayMonth = UtilsNDLATimelineMajor0Minor3::formatDayMonth(
            $startDate['month'],
            $startDate['day'],
            $langTag
        );

        $fulldate .= $startDayMonth . ' ' . $startYear;

        if (isset($slide['endDate'])) {
            $endDate = UtilsNDLATimelineMajor0Minor3::parseDateString($slide['endDate']);
            $endYear = UtilsNDLATimelineMajor0Minor3::formatYear($endDate['year'], $bce);
            $endDayMonth = UtilsNDLATimelineMajor0Minor3::formatDayMonth(
                $endDate['month'],
                $endDate['day'],
                $langTag
            );
            $fulldate .= ' - ' . $endDayMonth . ' ' . $endYear;
        }

        return $fulldate;
    }

    /**
     * Format year to name negative values.
     *
     * @param number $year Year.
     *
     * @return string Formatted year.
     */
    public static function formatYear($year, $bce = 'BCE')
    {
        return $year < 0
            ? -$year . ' ' . $bce
            : '' . $year;
    }

    /**
     * Format day and month to locale version.
     *
     * @param integer $month   Month number.
     * @param integer $day     Day number.
     * @param string  $langTag Language tag.
     *
     * @return string Formatted day and month.
     */
    public static function formatDayMonth(?int $month, ?int $day, string $langTag) : string
    {
        if ($month === null || $month < 1 || $month > 12) {
            return '';
        }
        if ($day !== null && ($day < 1 || $day > 31)) {
            return '';
        }

        $safeYear = 2000;
        $safeDay = $day ?? 1;
        $dateStr = sprintf('%04d-%d-%d', $safeYear, $month, $safeDay);
        $dt = \DateTime::createFromFormat('!Y-n-j', $dateStr);
        if ($dt === false) {
            return '';
        }

        $pattern = $day === null ? 'LLLL' : 'd LLLL';
        $fmt = new \IntlDateFormatter(
            $langTag,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $formatted = $fmt->format($dt);
        return ($formatted === false) ? '' : $formatted;
    }

    /**
     * Parse custom datestring into array.
     *
     * @param string $dateString Custom date string.
     *
     * @return array date as array.
     */
    public static function parseDateString($dateString)
    {
        // Match optional negative sign, year, month, and day
        if (preg_match('/^(-?\d{1,4})(?:-(\d{2}))?(?:-(\d{2}))?$/', $dateString, $matches)) {
            return [
                'year'  => (int)$matches[1],
                'month' => isset($matches[2]) ? (int)$matches[2] : null,
                'day'   => isset($matches[3]) ? (int)$matches[3] : null,
            ];
        }
        // Return null if invalid format
        return null;
    }
}

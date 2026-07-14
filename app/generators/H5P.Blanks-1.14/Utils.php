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
 * Utility functions for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class UtilsBlanksMajor1Minor14
{
    /**
     * Get the solution text.
     *
     * @param array $params The params array.
     *
     * @return string The solution text.
     */
    public static function getSolutionTexts($params)
    {
        if (!isset($params["questions"]) || !is_array($params["questions"])) {
            return Generator::SOLUTION_FALLBACK;
        }

        $allSolutions = [];

        foreach ($params["questions"] as $question) {
            // Find all asterisk-delimited blanks: *...*
            preg_match_all("/\*([^*]+)\*/", $question, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $clozeContent = $match[1];

                // Strip HTML tags from within the blank (JS: replaceAll)
                $clozeContent = preg_replace("/<\/?[a-z][a-z0-9]*\b[^>]*>/i", "", $clozeContent);

                // Parse the solution text: split by "/" for alternatives,
                // strip tip after ":", trim, and decode HTML entities.
                $solutions = self::parseSolutionText($clozeContent);

                $allSolutions = array_merge($allSolutions, $solutions);
            }
        }

        if (empty($allSolutions)) {
            return Generator::SOLUTION_FALLBACK;
        }

        return implode(", ", $allSolutions);
    }

    /**
     * Parse a cloze solution text (content between asterisks).
     *
     * Handles:
     * - Tips separated by ":" (e.g., "browser:Something you use")
     * - Alternative answers separated by "/" (e.g., "browser/web-browser")
     * - HTML entity decoding (e.g., "&amp;" becomes "&")
     *
     * Mirrors Blanks.prototype.parseSolution in blanks.js.
     *
     * @param string $solutionText The solution text (content between "*..." markers).
     *
     * @return string[] Array of solution strings (alternatives, no tips).
     */
    private static function parseSolutionText($solutionText)
    {
        // Extract tip: text after the last ":" is the tip, everything before is the solution.
        $tipStart = strpos($solutionText, ":");
        if ($tipStart !== false) {
            $solutionText = substr($solutionText, 0, $tipStart);
        }

        // Split alternatives by "/"
        $solutions = explode("/", $solutionText);

        // Trim and decode HTML entities for each alternative.
        for ($i = 0; $i < count($solutions); $i++) {
            $solutions[$i] = trim($solutions[$i]);
            $solutions[$i] = html_entity_decode($solutions[$i], ENT_QUOTES | ENT_HTML5, "UTF-8");
        }

        return $solutions;
    }
}

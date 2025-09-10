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
 * Class for generating HTML for H5P.Table-1.1.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorTableMajor1Minor1 extends Generator implements GeneratorInterface
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
        $markdownTable = $this->htmlTableToMarkdown($this->params['text']);
        $container .= $markdownTable;

        $container = trim($container);
    }

    /**
     * Convert an HTML table to a Markdown table.
     *
     * @param string $html The HTML content containing the table.
     *
     * @return string The Markdown representation of the table.
     */
    private function htmlTableToMarkdown($html)
    {
        $html = preg_replace("/\r|\n|\t/", " ", $html);
        $html = preg_replace("/\s{2,}/", " ", $html);

        if (!preg_match("/<table\b[^>]*>(.*?)<\/table>/is", $html, $m)) {
            return '';
        }
        $table = $m[1];

        // get all TRs grouped by section
        $theadHtml = '';
        if (preg_match("/<thead\b[^>]*>(.*?)<\/thead>/is", $table, $thm)) {
            $theadHtml = $thm[1];
            // remove thead from table so it won't be double-counted
            $table = preg_replace("/<thead\b[^>]*>.*?<\/thead>/is", "", $table);
        }

        $rowsHtml = [];
        if (preg_match_all("/<tbody\b[^>]*>(.*?)<\/tbody>/is", $table, $tb)) {
            foreach ($tb[1] as $segment) {
                if (preg_match_all("/<tr\b[^>]*>(.*?)<\/tr>/is", $segment, $rt)) {
                    foreach ($rt[1] as $r) {
                        $rowsHtml[] = $r;
                    }
                }
            }
        } else {
            if (preg_match_all("/<tr\b[^>]*>(.*?)<\/tr>/is", $table, $rt)) {
                foreach ($rt[1] as $r) {
                    $rowsHtml[] = $r;
                }
            }
        }

        // parse cells helper
        $parseCells = function ($rowHtml) {
            $cells = [];
            if (preg_match_all("/<(td|th)\b[^>]*>(.*?)<\/\\1>/is", $rowHtml, $c)) {
                foreach ($c[2] as $cell) {
                    $text = trim(html_entity_decode(strip_tags($cell)));
                    $text = str_replace('|', '\\|', $text);
                    $cells[] = $text;
                }
            }
            return $cells;
        };

        // Build header
        $header = [];
        if ($theadHtml) {
            // take first tr from thead as header
            if (preg_match("/<tr\b[^>]*>(.*?)<\/tr>/is", $theadHtml, $htr)) {
                $header = $parseCells($htr[1]);
            }
        } else {
            // look for first TR that contains TH among the collected rowsHtml
            foreach ($rowsHtml as $idx => $rHtml) {
                if (preg_match("/<th\b[^>]*>/i", $rHtml)) {
                    $header = $parseCells($rHtml);
                    // remove that row from data rows
                    array_splice($rowsHtml, $idx, 1);
                    break;
                }
            }
        }

        // If still no header, optionally use first data row as header (comment out if undesired)
        if (empty($header) && !empty($rowsHtml)) {
            $header = $parseCells($rowsHtml[0]);
            array_splice($rowsHtml, 0, 1);
        }

        // parse remaining rows
        $parsed = [];
        foreach ($rowsHtml as $rHtml) {
            $row = $parseCells($rHtml);
            if (!empty($row)) {
                $parsed[] = $row;
            }
        }

        if (empty($header) && empty($parsed)) {
            return '';
        }

        // normalize columns
        $colCount = max(array_map('count', array_merge([$header], $parsed)));
        for ($i = 0; $i < $colCount; $i++) {
            if (!isset($header[$i])) {
                $header[$i] = '';
            }
        }
        foreach ($parsed as &$r) {
            if (count($r) < $colCount) {
                $r = array_pad($r, $colCount, '');
            }
        }
        unset($r);

        // compute widths and make equal
        $widths = array_fill(0, $colCount, 0);
        for ($c = 0; $c < $colCount; $c++) {
            $widths[$c] = max($widths[$c], mb_strlen($header[$c]));
            foreach ($parsed as $r) {
                $widths[$c] = max($widths[$c], mb_strlen($r[$c]));
            }
        }
        $maxWidth = max($widths);
        for ($c = 0; $c < $colCount; $c++) {
            $widths[$c] = $maxWidth;
        }

        $pad = function ($text, $w) {
            return $text . str_repeat(' ', max(0, $w - mb_strlen($text)));
        };

        // build markdown
        $markdown = '|';
        foreach ($header as $i => $h) {
            $markdown .= ' ' . $pad($h, $widths[$i]) . ' |';
        }
        $markdown .= "\n|";

        foreach ($widths as $w) {
            $markdown .= ' ' . str_repeat('-', $w) . ' |';
        }
        $markdown .= "\n";

        foreach ($parsed as $r) {
            $markdown .= '|';
            for ($i = 0; $i < $colCount; $i++) {
                $markdown .= ' ' . $pad($r[$i] ?? '', $widths[$i]) . ' |';
            }
            $markdown .= "\n";
        }

        return $markdown;
    }
}

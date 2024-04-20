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
 * @link     https://todo
 */

namespace H5PExtractor;

require_once __DIR__ . '/../HtmlGeneratorInterface.php';

/**
 * Class for generating HTML for H5P.MarkTheWords-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class HtmlGeneratorMarkTheWords_1_11 implements HtmlGeneratorInterface
{

    /**
     * Get the content of the lines in the given input.
     *
     * @param string $input The input.
     *
     * @return string[] The content of the lines in the given input.
     */
    private function _getLinesContent($input)
    {
        if (strpos($input, '<p>') === false) {
            return array($input);
        }

        $input = str_replace(array("\r", "\n", "\r\n"), '', $input);

        $pattern = '/<p>(.*?)<\/p>/';
        preg_match_all($pattern, $input, $matches);
        return $matches[1];
    }

    /**
     * Interpret the given text.
     *
     * @param string $input The input.
     *
     * @return string The interpreted text.
     */
    private function _interpretText($input)
    {
        $output = str_replace('<br>', "\n\n", $input);

        // Remove asterisks as required
        $pattern = '/\*(\w+\**)\*/';
        $callback = function ($matches) {
            return str_replace('**', '*', $matches[1]);
        };
        $output = preg_replace_callback($pattern, $callback, $output);

        $output = htmlspecialchars_decode($output);

        // Sandwich each word with span, but keep HTML tags
        $pattern = '/(?:<[^>]+>)|(\b(?:\w+|-|–)+\b)/';
        $callback = function ($matches) {
            $match = $matches[1] ?? htmlspecialchars($matches[0]);
            return '<span role="option">' . $match . "</span>";
        };

        $output = preg_replace_callback($pattern, $callback, $output);

        // Asterisks that were inside asterisks may belong to the word
        $output = str_replace(
            '</span>*',
            '*</span>',
            $output
        );
        $output = str_replace(
            '*<span role="option">',
            '<span role="option">*',
            $output
        );

        // Remove gaps in between tags
        $output = str_replace('&gt;</span><span role="option">', '&gt;', $output);
        $output = str_replace('</span><span role="option">&lt;', '&lt;', $output);

        $output = str_replace("\n\n", '<br>', $output);

        return $output;
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array             $params Parameters.
     * @param HtmlGeneratorMain $main   The main HTML generator.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params, $main)
    {
        $contentParams = $params['params'];

        $html = $params['container'];

        preg_match('/<([a-zA-Z]+)(?:\s+[^>]*)?>/', $html, $matches);
        $tag_name = isset($matches[1]) ? $matches[1] : '';

        $htmlClosing = ($tag_name) ? '</' . $tag_name . '>' : '</div>';

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $html = str_replace('h5pClassName', 'h5p-mark-the-words', $html);

        if (isset($contentParams['media']['type'])) {
            $html .= $main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $html .= '<div class="h5p-question-introduction">';
        $html .= '<div>' . ($contentParams['taskDescription'] ?? ''). '</div>';
        $html .= '</div>';

        $html .= '<div class="h5p-question-content h5p-word">';
        $html .= '<div class="h5p-word-inner">';
        $html .= '<div class="h5p-word-selectable-words">';

        $textField = $contentParams['textField'] ?? '';

        $lines = $this->_getLinesContent($textField);
        foreach ($lines as $line) {
            $line = $this->_interpretText($line);
            $line = '<p>' . $line . '</p>';

            $html .= $line;
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        $html .= $htmlClosing;

        return $html;
    }
}

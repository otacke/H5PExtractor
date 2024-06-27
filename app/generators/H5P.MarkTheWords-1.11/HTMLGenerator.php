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
 * Class for generating HTML for H5P.MarkTheWords-1.11.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class HtmlGeneratorMarkTheWordsMajor1Minor11 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params     Parameters.
     * @param int   $contentId  Content ID.
     * @param array $extras     Extras.
     */
    public function __construct($params, $contentId, $extras)
    {
        parent::__construct($params, $contentId, $extras);
    }

    /**
     * Get the content of the lines in the given input.
     *
     * @param string $input The input.
     *
     * @return string[] The content of the lines in the given input.
     */
    private function getLinesContent($input)
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
    private function interpretText($input)
    {
        $output = str_replace('<br>', "\n\n", $input);

        // Define the regular expression pattern for matching content between asterisks
        $pattern = '/\*+(.*?)\*+/';

        // Define a callback function to process matches
        $callback = function ($matches) {
            $content = $matches[1]; // Get the content between asterisks
            $content = str_replace('***', '*', $content); // Replace consecutive asterisks with a single asterisk

            // Check if the content is empty or contains only whitespace
            if (trim($content) === '') {
                // Content is empty or whitespace, wrap asterisks in <span role="option">
                return '<span role="option">' . $matches[0] . '</span>';
            } else {
                // Content is not empty, wrap it in <span role="option">
                return '<span role="option">' . $content . '</span>';
            }
        };

        // Use preg_replace_callback to apply the callback function to each match
        $output = preg_replace_callback($pattern, $callback, $input);

        $output = str_replace("\n\n", '<br>', $output);

        error_log('After: ' . $output);

        return $output;
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
        $htmlClosing = TextUtils::getClosingTag($container);

        /* In theory, one could derive this automatically and do in the parent,
         * but content types may not follow the common schema to define the main
         * class name.
         */
        $container = str_replace('h5pClassName', 'h5p-question h5p-mark-the-words', $container);

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $container .= '<div class="h5p-question-introduction">';
        $container .= '<div>' . ($this->params['taskDescription'] ?? ''). '</div>';
        $container .= '</div>';

        $container .= '<div class="h5p-question-content h5p-word">';
        $container .= '<div class="h5p-word-inner">';
        $container .= '<div class="h5p-word-selectable-words">';

        $textField = $this->params['textField'] ?? '';

        $lines = $this->getLinesContent($textField);
        foreach ($lines as $line) {
            $line = $this->interpretText($line);
            $line = '<p>' . $line . '</p>';

            $container .= $line;
        }

        $container .= '</div>';
        $container .= '</div>';
        $container .= '</div>';

        $container .= $htmlClosing;
    }
}

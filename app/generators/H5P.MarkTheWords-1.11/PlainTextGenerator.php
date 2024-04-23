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
class PlainTextGeneratorMarkTheWordsMajor1Minor11 implements PlainTextGeneratorInterface
{
    /**
     * Constructor.
     *
     * @param PlainTextGeneratorMain $main The main plain text generator.
     */
    public function __construct(PlainTextGeneratorMain $main)
    {
        $this->main = $main;
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
        // Remove asterisks as required
        $pattern = '/\*(\w+\**)\*/';
        $callback = function ($matches) {
            return str_replace('**', '*', $matches[1]);
        };
        $output = preg_replace_callback($pattern, $callback, $input);

        $output = htmlspecialchars_decode($output);

        $nbsp_before = uniqid();
        $nbsp_after = uniqid();

        // Sandwich each word with non-breaking spaces, keep HTML tags together
        $pattern = '/(?:<[^>]+>)|(\b(?:\w+|-|–)+\**\b)/';
        $callback = function ($matches) use ($nbsp_before, $nbsp_after) {
            $match = $matches[1] ?? $matches[0];
            return $nbsp_before . $match . $nbsp_after;
        };
        $output = preg_replace_callback($pattern, $callback, $output);

        // Asterisks that were inside asterisks may belong to the word
        $output = str_replace('*' . $nbsp_before, $nbsp_before . '*', $output);
        $output = str_replace($nbsp_after . '*', '*' .$nbsp_after, $output);

        // Remove gaps in between tags
        $output = str_replace('>' . $nbsp_after . $nbsp_before, '>', $output);
        $output = str_replace($nbsp_after . $nbsp_before . '<', '<', $output);

        $output = str_replace($nbsp_before, "\u{00A0}", $output);
        $output = str_replace($nbsp_after, "\u{00A0}", $output);

        return $output;
    }

    /**
     * Create the HTML for the given H5P content type.
     *
     * @param array                  $params Parameters.
     *
     * @return string The HTML for the H5P content type.
     */
    public function get($params)
    {
        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['media']['type'])) {
            $text .= $this->main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $text .= TextUtils::htmlToText(($contentParams['taskDescription'] ?? ''));

        $textField = $contentParams['textField'] ?? '';
        $lines = $this->getLinesContent($textField);

        foreach ($lines as $line) {
            $line = str_replace('<br>', "\n", $line);

            $text .= $this->interpretText($line);
            $text .= "\n\n";
        }

        return trim($text);
    }
}

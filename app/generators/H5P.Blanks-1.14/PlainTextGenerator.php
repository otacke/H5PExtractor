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
 * Class for generating plain text for H5P.Blanks-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorBlanksMajor1Minor14 implements PlainTextGeneratorInterface
{
    private $main;

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
     * Create the plain text for the given H5P content type.
     *
     * @param array                  $params Parameters.
     *
     * @return string The plain text for the H5P content type.
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

        $text .= TextUtils::htmlToText($contentParams['text']);

        // loop through $contentParams['questions']
        $questionCount = count($contentParams['questions']);
        for ($index = 0; $index < $questionCount; $index++) {
            $questionData = $contentParams['questions'][$index];

            $blank = ($contentParams['behaviour']['separateLines']) ?
                "\n__________\n" : '__________';

            $questionData = preg_replace(
                '/\*([^*]+)\*/',
                $blank,
                $questionData
            );

            $text .= TextUtils::htmlToText($questionData);
        }

        return trim($text);
    }
}

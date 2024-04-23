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
 * Class for generating HTML for H5P.Essay-1.5.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorEssayMajor1Minor5 extends Generator implements PlainTextGeneratorInterface
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

        if (!empty($contentParams['placeholderText'])) {
            $text .= $contentParams['placeholderText'] . "\n\n";
        }

        $line = '________________________________________' . "\n";
        $numberLines = (isset($contentParams['behaviour']['inputFieldSize'])) ?
            $contentParams['behaviour']['inputFieldSize'] :
            10;

        for ($i = 0; $i < $numberLines / 2; $i++) {
            $text .= $line . "\n";
        }

        return trim($text);
    }
}

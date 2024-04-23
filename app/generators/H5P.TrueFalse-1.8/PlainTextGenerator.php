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
 * Class for generating plain text for H5P.TrueFalse-1.8.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorTrueFalseMajor1Minor8 extends Generator implements PlainTextGeneratorInterface
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

        $text .= TextUtils::htmlToText(($contentParams['question'] ?? ''));

        $text .= '( ) ' . $contentParams['l10n']['trueText'] . "\n";
        $text .= '( ) ' . $contentParams['l10n']['falseText'];

        return trim($text);
    }
}

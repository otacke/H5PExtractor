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
 * Class for generating HTML for H5P.DragText-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorDragTextMajor1Minor10 extends Generator implements PlainTextGeneratorInterface
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
        include_once __DIR__ . '/Utils.php';

        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['media']['type'])) {
            $text .= $this->main->renderH5PQuestionMedia(
                $contentParams['media']['type']
            );
        }

        $taskDescription = $contentParams['taskDescription'] ?? '';

        $text .= TextUtils::htmlToText($taskDescription);
        if (strpos($taskDescription, '<p>') !== 0) {
            $text .= "\n\n";
        }

        $draggables = [];

        $textParts = [];
        $textFieldHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $contentParams['textField'] ?? ''
        );
        $segments = UtilsDragTextMajor1Minor10::parseText($textFieldHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                $textParts[] = $segment;
                continue;
            }

            $lexed = UtilsDragTextMajor1Minor10::lex($segment);

            $draggables[] = $lexed['text'];

            $dropzone = '__________';
            if ($lexed['tip'] !== '') {
                $dropzone .= ' (' . $lexed['tip'] . ')';
            }
            $textParts[] = $dropzone;
        }

        $distractorsHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $contentParams['distractors'] ?? ''
        );
        $segments = UtilsDragTextMajor1Minor10::parseText($distractorsHtml);
        foreach ($segments as $segment) {
            if (!str_starts_with($segment, '*')
                || !str_ends_with($segment, '*')
            ) {
                continue;
            }

            $lexed = UtilsDragTextMajor1Minor10::lex($segment);
            $draggables[] = $lexed['text'];
        }

        $text .= implode(' ', $textParts);

        $text = str_replace(['<br>', '<br/>'], "\n", $text);

        shuffle($draggables);
        $text .= "\n\n" . implode(", ", $draggables);

        return trim($text);
    }
}

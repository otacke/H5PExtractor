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

require_once __DIR__ . '/../PlainTextGeneratorInterface.php';
require_once __DIR__ . '/../../utils/TextUtils.php';
require_once __DIR__ . '/Utils.php';

/**
 * Class for generating HTML for H5P.DragText-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://todo
 */
class PlainTextGeneratorDragText_1_10 implements PlainTextGeneratorInterface
{
    /**
     * Create the plain text for the given H5P content type.
     *
     * @param array                  $params Parameters.
     * @param PlainTextGeneratorMain $main   The main HTML generator.
     *
     * @return string The plain text for the H5P content type.
     */
    public function get($params, $main)
    {
        $contentParams = $params['params'];

        $text = $params['container'];

        if (isset($contentParams['media']['type'])) {
            $text .= $main->renderH5PQuestionMedia(
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
        $segments = UtilsDragText_1_10::parseText($textFieldHtml);
        foreach ($segments as $segment) {
            if (
                !str_starts_with($segment, '*') ||
                !str_ends_with($segment, '*')
            ) {
                $textParts[] = $segment;
                continue;
            }

            $lexed = UtilsDragText_1_10::lex($segment);

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
        $segments = UtilsDragText_1_10::parseText($distractorsHtml);
        foreach ($segments as $segment) {
            if (
                !str_starts_with($segment, '*') ||
                !str_ends_with($segment, '*')
            ) {
                continue;
            }

            $lexed = UtilsDragText_1_10::lex($segment);
            $draggables[] = $lexed['text'];
        }

        $text .= implode(' ', $textParts);

        $text = str_replace(['<br>', '<br/>'], "\n", $text);

        shuffle($draggables);
        $text .= "\n\n" . implode(", ", $draggables);

        return trim($text);
    }
}

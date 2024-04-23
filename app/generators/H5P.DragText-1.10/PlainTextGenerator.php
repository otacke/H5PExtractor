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
class PlainTextGeneratorDragTextMajor1Minor10 extends Generator implements GeneratorInterface
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
     * @param string $container Container for H5P content.
     *
     * @return string The plain text for the H5P content type.
     */
    public function attach($container)
    {
        include_once __DIR__ . '/Utils.php';

        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        $taskDescription = $this->params['taskDescription'] ?? '';

        $container .= TextUtils::htmlToText($taskDescription);
        if (strpos($taskDescription, '<p>') !== 0) {
            $container .= "\n\n";
        }

        $draggables = [];

        $textParts = [];
        $textFieldHtml = preg_replace(
            '/(\r\n|\n|\r)/',
            '<br/>',
            $this->params['textField'] ?? ''
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
            $this->params['distractors'] ?? ''
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

        $container .= implode(' ', $textParts);

        $container = str_replace(['<br>', '<br/>'], "\n", $container);

        shuffle($draggables);
        $container .= "\n\n" . implode(", ", $draggables);

        $container = trim($container);

        return $container;
    }
}

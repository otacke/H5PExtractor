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
 * Class for generating HTML for H5P.DragQuestion-1.14.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorDragQuestionMajor1Minor14 extends Generator implements GeneratorInterface
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
     * @param string $container Container for H5P content.
     *
     * @return string The HTML for the H5P content type.
     */
    public function attach(&$container)
    {
        if ($this->params['behaviour']['showTitle'] ?? false) {
            $container .=
                ($this->extras['metadata']['title'] ?? 'Drag and Drop') . "\n\n";
        }

        $task = $this->params['question']['task'] ?? [];

        // Could be fun to try to represent this in ASCII art ;-)
        $container .= '**Dropzones**' . "\n\n"; // TODO i18n

        foreach ($task['dropZones'] ?? [] as $dropZone) {
            $container .= '__________';

            $dropZoneLabel = str_replace('</div><div>', ' ', $dropZone['label']);
            $dropZoneLabel = str_replace('<br>', ' ', $dropZoneLabel);
            $dropZoneLabel = str_replace('&nbsp;', ' ', $dropZoneLabel);
            $dropZoneLabel = preg_replace('/>\s+/', '>', $dropZoneLabel);
            $dropZoneLabel = preg_replace('/\s+</', '<', $dropZoneLabel);
            $dropZoneLabel = trim($dropZoneLabel);

            if ($dropZone['showLabel'] && $dropZoneLabel !== '') {
                $container .= ' (' . TextUtils::htmlToText($dropZoneLabel) . ')';
            }

            $container .= ", ";
        }

        $container .= "\n\n";

        $elements = $task['elements'] ?? [];
        usort($elements, fn($a, $b) => $a['y'] !== $b['y'] ? $a['y'] <=> $b['y'] : $a['x'] <=> $b['x']);

        $nonDraggables = array_filter($elements, fn($el) => count($el['dropZones'] ?? []) === 0);
        $draggables = array_filter($elements, fn($el) => count($el['dropZones'] ?? []) !== 0);

        $appendElement = function ($element) use (&$container) {
            $elementParams = $element['type'] ?? [];
            if (str_starts_with($elementParams['library'], 'H5P.AdvancedText ')) {
                $text = $elementParams['params']['text'];
                $text = str_replace('-</p><p>', '-', $text);
                $text = str_replace('-<br>', '-', $text);
                $text = str_replace('</p><p>', ' ', $text);
                $text = str_replace('<br>', ' ', $text);
                $text = trim($text);
                $element['type']['params']['text'] = $text;
            }

            $innerContainer = '';
            $this->main->newRunnable(
                $element['type'] ?? [],
                1,
                $innerContainer
            );

            $container .= " - " . $innerContainer . "\n";
        };

        if (!empty($nonDraggables)) {
            $container .= '**Non-Draggables**' . "\n\n"; // TODO i18n
            foreach ($nonDraggables as $element) {
                $appendElement($element);
            }
            $container .= "\n";
        }

        $container .= '**Draggables**' . "\n\n"; // TODO i18n

        foreach ($draggables as $element) {
            $appendElement($element);
        }

        $container = trim($container);
    }
}

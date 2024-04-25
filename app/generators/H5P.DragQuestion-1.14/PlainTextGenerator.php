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

            if ($dropZone['showLabel'] && trim($dropZone['label']) !== '') {
                $container .= ' (' . TextUtils::htmlToText($dropZone['label']) . ')';
            }

            $container .= ", ";
        }

        $container .= "\n\n";

        $container .= '**Draggables**' . "\n\n"; // TODO i18n

        foreach ($task['elements'] ?? [] as $draggable) {
            if (count($draggable['dropZones'] ?? []) === 0) {
                continue; // Just "decoration"
            }

            $innerContainer = '';
            $this->main->newRunnable(
                $draggable['type'] ?? [],
                1,
                $innerContainer
            );

            $container .= $innerContainer . ", ";
        }

        $container = trim($container);
    }
}

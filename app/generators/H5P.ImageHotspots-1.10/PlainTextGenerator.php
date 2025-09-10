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
 * Class for generating HTML for H5P.ImageHotspots-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorImageHotspotsMajor1Minor10 extends Generator implements GeneratorInterface
{
    /**
     * Constructor.
     *
     * @param array $params    Parameters.
     * @param int   $contentId Content ID.
     * @param array $extras    Extras.
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
        $this->params['hotspots'] = $this->params['hotspots'] ?? [];

        // Filter out hotspots without content
        $this->params['hotspots'] = array_filter(
            $this->params['hotspots'],
            function ($hotspot) {
                $hotspot['content'] = $hotspot['content'] ?? [];
                $hotspot['content'] = array_filter(
                    $hotspot['content'],
                    function ($content) {
                        return isset($content['library']);
                    }
                );
                return count($hotspot['content']) > 0;
            }
        );

        // Sort hotspots by position
        usort(
            $this->params['hotspots'],
            function ($a, $b) {
                // Sanity checks, move data to the back if invalid
                $firstIsValid = $a['position'] && $a['position']['x'] && $a['position']['y'];
                $secondIsValid = $b['position'] && $b['position']['x'] && $b['position']['y'];
                if (!$firstIsValid) {
                    return 1;
                }

                if (!$secondIsValid) {
                    return -1;
                }

                // Order top-to-bottom, left-to-right
                if ($a['position']['y'] !== $b['position']['y']) {
                    return $a['position']['y'] < $b['position']['y'] ? -1 : 1;
                } else {
                    // a and b y position is equal, sort on x
                    return $a['position']['x'] < $b['position']['x'] ? -1 : 1;
                }
            }
        );

        $hotspotsCount = count($this->params['hotspots'] ?? []);
        for ($i = 0; $i < $hotspotsCount; $i++) {
            $hotspot = $this->params['hotspots'][$i];

            $container .= "- " . $hotspot['header'] . "\n";

            foreach ($hotspot['content'] as $content) {
                $subcontentContainer = '';
                $this->main->newRunnable(
                    [
                        'library' => $content['library'],
                        'params' => $content['params'],
                    ],
                    1,
                    $subcontentContainer,
                    false,
                    [
                        'metadata' => $content['metadata'] ?? [],
                    ]
                );

                $subcontentContainer = '  ' . str_replace("\n", "\n  ", $subcontentContainer) . "\n";

                $container .= $subcontentContainer;
            }

            $container .= "\n";
        }

        $container = trim($container);
    }
}

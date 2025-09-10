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
 * Class for generating HTML for H5P.Summary-1.10.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorSummaryMajor1Minor10 extends Generator implements GeneratorInterface
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
        if (isset($this->params['intro'])) {
            $container .= TextUtils::htmlToText($this->params['intro']) . "\n";
        }

        for ($i = 0; $i < count($this->params['summaries']); $i++) {
            $container .= ($i + 1) . '/' . count($this->params['summaries']) . "\n";
            $options = $this->params['summaries'][$i];

            foreach ($options['summary'] ?? [] as $j => $option) {
                $container .= '- ' . TextUtils::htmlToText($option);
            }
        }

        $container = trim($container);
    }
}

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
 * Class for generating HTML for H5P.StructureStrip-1.0.
 *
 * @category Tool
 * @package  H5PExtractor
 * @author   Oliver Tacke <oliver@snordian.de>
 * @license  MIT License
 * @link     https://github.com/otacke/H5PExtractor
 */
class PlainTextGeneratorStructureStripMajor1Minor0 extends Generator implements GeneratorInterface
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
        if (isset($this->params['media']['type'])) {
            $container .= $this->main->renderH5PQuestionMedia(
                $this->params['media']['type']
            );
        }

        if (isset($this->params['taskDescription'])) {
            $container .= TextUtils::htmlToText($this->params['taskDescription']) . "\n";
        }

        for ($i = 0; $i < count($this->params['sections']); $i++) {
            $container .= TextUtils::htmlToText($this->params['sections'][$i]['title']) . "\n";
            if (isset($this->params['sections'][$i]['description'])) {
                $container .= TextUtils::htmlToText($this->params['sections'][$i]['description']) . "\n";
            }

            $container .= "\n" . '________________________________________' . "\n";
            $container .= "\n" . '________________________________________' . "\n";
            $container .= "\n" . '________________________________________' . "\n";
            $container .= "\n";
        }

        $container = trim($container);
    }
}
